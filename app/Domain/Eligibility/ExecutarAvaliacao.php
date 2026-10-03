<?php

namespace App\Domain\Eligibility;

use App\Domain\Enums\EstadoAvaliacao;
use App\Models\Avaliacao;
use App\Models\Beneficiario;
use App\Models\Beneficio;
use App\Models\Comprovacao;
use App\Models\VersaoRegra;
use Carbon\CarbonImmutable;
use DomainException;
use Illuminate\Database\MultipleRecordsFoundException;
use Illuminate\Database\RecordsNotFoundException;
use Illuminate\Support\Facades\DB;

class ExecutarAvaliacao
{
    public function __construct(
        private readonly ValidadorRegra $validador,
        private readonly ResolvedorRequisitos $resolvedor,
        private readonly MotorElegibilidade $motor,
    ) {}

    public function executar(int $beneficiarioId, string $codigoBeneficio = 'CADEIRA_RODAS'): Avaliacao
    {
        $instante = CarbonImmutable::now('UTC')->startOfSecond();

        return DB::transaction(function () use ($beneficiarioId, $codigoBeneficio, $instante): Avaliacao {
            $beneficiario = Beneficiario::query()->lockForUpdate()->findOrFail($beneficiarioId);
            $beneficio = Beneficio::query()->where('codigo', $codigoBeneficio)->lockForUpdate()->firstOrFail();
            if ($beneficiario->estado !== 'ATIVO' || $beneficio->estado !== 'ATIVO') {
                throw new DomainException('Beneficiário e Benefício devem estar ativos.');
            }

            try {
                $versao = VersaoRegra::query()
                    ->where('beneficio_id', $beneficio->id)
                    ->where('estado', 'PUBLICADA')
                    ->where('vigencia_inicio', '<=', $instante->format('Y-m-d H:i:s'))
                    ->where(fn ($query) => $query->whereNull('vigencia_fim')->orWhere('vigencia_fim', '>', $instante->format('Y-m-d H:i:s')))
                    ->lockForUpdate()->sole();
            } catch (RecordsNotFoundException|MultipleRecordsFoundException) {
                throw new DomainException('Versão publicada vigente indisponível ou ambígua.');
            }
            [$raiz, $nos] = $this->validador->validar($versao);

            $comprovacoes = Comprovacao::query()->where('beneficiario_id', $beneficiario->id)
                ->lockForUpdate()->get()->keyBy('tipo');
            $valores = [];
            foreach ($nos as $no) {
                if ($no->requisito === null) {
                    continue;
                }
                $requisito = $no->requisito;
                $chave = $requisito->codigo.'/'.$requisito->versao_semantica;
                $valores[$chave] ??= $this->resolvedor->resolver(
                    $requisito,
                    $beneficiario,
                    $comprovacoes->get($requisito->codigo),
                    $instante,
                );
            }

            [$resultadoAutomatico, $resultados] = $this->motor->avaliar($raiz, $nos, $valores);
            $snapshot = [
                'schema_version' => 1,
                'instante_referencia_utc' => $instante->format('Y-m-d\TH:i:s\Z'),
                'beneficiario' => [
                    'identificador' => $beneficiario->identificador,
                    'vulnerabilidade_social' => $beneficiario->vulnerabilidade_social,
                    'limitacao_mobilidade' => $beneficiario->limitacao_mobilidade,
                ],
                'beneficio' => ['codigo' => $beneficio->codigo],
                'versao_regra' => ['id' => $versao->id, 'numero' => $versao->numero],
                'valores_resolvidos' => $valores,
            ];

            $avaliacao = Avaliacao::create([
                'beneficiario_id' => $beneficiario->id,
                'beneficio_id' => $beneficio->id,
                'versao_regra_id' => $versao->id,
                'instante_referencia' => $instante,
                'estado' => EstadoAvaliacao::INICIADA,
            ]);
            foreach ($resultados as $resultado) {
                $avaliacao->resultados()->create($resultado);
            }
            $avaliacao->update([
                'snapshot_schema_version' => 1,
                'snapshot' => $snapshot,
                'resultado_automatico' => $resultadoAutomatico,
                'estado' => EstadoAvaliacao::CONCLUIDA,
                'concluida_em' => CarbonImmutable::now('UTC'),
            ]);

            return $avaliacao->load(['beneficio', 'versaoRegra', 'resultados']);
        });
    }
}
