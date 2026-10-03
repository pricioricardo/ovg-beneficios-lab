<?php

namespace App\Domain\Eligibility;

use App\Domain\Enums\TipoRequisito;
use App\Models\Beneficiario;
use App\Models\Comprovacao;
use App\Models\Requisito;
use Carbon\CarbonImmutable;
use DomainException;

class ResolvedorRequisitos
{
    private const CATALOGO = [
        'VULNERABILIDADE_SOCIAL/v1' => [TipoRequisito::BOOLEAN, 'BENEFICIARIO'],
        'LIMITACAO_MOBILIDADE/v1' => [TipoRequisito::BOOLEAN, 'BENEFICIARIO'],
        'RELATORIO_PROFISSIONAL/v1' => [TipoRequisito::DOCUMENT_PRESENCE, 'COMPROVACAO'],
    ];

    public function validar(Requisito $requisito): void
    {
        $chave = $requisito->codigo.'/'.$requisito->versao_semantica;
        $esperado = self::CATALOGO[$chave] ?? null;

        if ($esperado === null || $requisito->tipo !== $esperado[0]
            || $requisito->fonte !== $esperado[1] || $requisito->estado !== 'ATIVO') {
            throw new DomainException('Requisito ou versão semântica sem resolvedor aprovado.');
        }
    }

    public function resolver(
        Requisito $requisito,
        Beneficiario $beneficiario,
        ?Comprovacao $comprovacao,
        CarbonImmutable $instante,
    ): array {
        $this->validar($requisito);

        $base = [
            'codigo' => $requisito->codigo,
            'versao_semantica' => $requisito->versao_semantica,
            'tipo' => $requisito->tipo->value,
        ];

        return match ($requisito->codigo) {
            'VULNERABILIDADE_SOCIAL' => $base + ['valor' => $beneficiario->vulnerabilidade_social],
            'LIMITACAO_MOBILIDADE' => $base + ['valor' => $beneficiario->limitacao_mobilidade],
            'RELATORIO_PROFISSIONAL' => $base + $this->documento($comprovacao, $instante),
        };
    }

    private function documento(?Comprovacao $comprovacao, CarbonImmutable $instante): array
    {
        $dataLocal = $instante->setTimezone('America/Sao_Paulo')->toDateString();
        $apresentacao = $comprovacao?->data_apresentacao?->toDateString();
        $validade = $comprovacao?->validade?->toDateString();
        $estado = $comprovacao?->estado ?? 'AUSENTE';

        $efetivo = match (true) {
            $estado === 'AUSENTE' => 'AUSENTE',
            $estado !== 'APRESENTADA' || $apresentacao === null => 'INDETERMINADA',
            $apresentacao > $dataLocal => 'INDETERMINADA',
            $validade !== null && $validade < $apresentacao => 'INDETERMINADA',
            $validade !== null && $validade < $dataLocal => 'VENCIDA',
            default => 'APRESENTADA',
        };

        return [
            'valor' => match ($efetivo) {
                'APRESENTADA' => true,
                'AUSENTE', 'VENCIDA' => false,
                default => null,
            },
            'estado_cadastral' => $estado,
            'estado_efetivo' => $efetivo,
            'data_apresentacao' => $apresentacao,
            'validade' => $validade,
        ];
    }
}
