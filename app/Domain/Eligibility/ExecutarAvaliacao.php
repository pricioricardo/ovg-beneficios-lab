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
use Illuminate\Support\Facades\Log;
use Throwable;

class ExecutarAvaliacao
{
    public function __construct(
        private readonly ValidadorRegra $validador,
        private readonly ResolvedorRequisitos $resolvedor,
        private readonly MotorElegibilidade $motor,
    ) {}

    public function executar(int $beneficiarioId, string $codigoBeneficio = 'CADEIRA_RODAS'): Avaliacao
    {
        // A primeira leitura fixa a visão REPEATABLE READ e obtém o instante UTC
        // na mesma instrução. Não misturar com lockForUpdate.
        [$avaliacao, $raiz, $nos, $valores, $snapshot] = DB::transaction(function () use ($beneficiarioId, $codigoBeneficio): array {
            if (DB::selectOne('SELECT @@transaction_isolation AS nivel')->nivel !== 'REPEATABLE-READ') {
                throw new DomainException('Avaliação exige visão consistente MySQL REPEATABLE READ.');
            }

            $inicio = DB::selectOne('SELECT UTC_TIMESTAMP(6) AS instante_utc FROM beneficiarios WHERE id = ?', [$beneficiarioId]);
            if ($inicio === null) {
                throw new DomainException('Beneficiário inexistente.');
            }
            $instante = CarbonImmutable::parse($inicio->instante_utc, 'UTC')->startOfSecond();
            $beneficiario = Beneficiario::query()->findOrFail($beneficiarioId);
            $beneficio = Beneficio::query()->where('codigo', $codigoBeneficio)->firstOrFail();
            if ($beneficiario->estado !== 'ATIVO' || $beneficio->estado !== 'ATIVO') {
                throw new DomainException('Beneficiário e Benefício devem estar ativos.');
            }

            try {
                $versao = VersaoRegra::query()
                    ->where('beneficio_id', $beneficio->id)
                    ->where('estado', 'PUBLICADA')
                    ->where('vigencia_inicio', '<=', $instante->format('Y-m-d H:i:s'))
                    ->where(fn ($query) => $query->whereNull('vigencia_fim')->orWhere('vigencia_fim', '>', $instante->format('Y-m-d H:i:s')))
                    ->sole();
            } catch (RecordsNotFoundException|MultipleRecordsFoundException) {
                throw new DomainException('Versão publicada vigente indisponível ou ambígua.');
            }
            [$raiz, $nos] = $this->validador->validar($versao);

            $comprovacoes = Comprovacao::query()->where('beneficiario_id', $beneficiario->id)
                ->get()->keyBy('tipo');
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
            return [$avaliacao, $raiz, $nos, $valores, $snapshot];
        });

        try {
            return DB::transaction(function () use ($avaliacao, $raiz, $nos, $valores, $snapshot): Avaliacao {
                [$resultadoAutomatico, $resultados] = $this->motor->avaliar($raiz, $nos, $valores);
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
        } catch (Throwable $exception) {
            // A tentativa foi confirmada antes do processamento. O rollback acima
            // elimina resultados parciais; somente o estado de falha é confirmado.
            $avaliacao->refresh()->update(['estado' => EstadoAvaliacao::FALHA_TECNICA]);
            Log::error('Falha técnica na avaliação do laboratório.', [
                'avaliacao_id' => $avaliacao->id,
                'tipo' => $exception::class,
            ]);

            throw $exception;
        }
    }
}
