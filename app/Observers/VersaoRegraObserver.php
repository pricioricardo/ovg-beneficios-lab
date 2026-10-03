<?php

namespace App\Observers;

use App\Domain\Enums\EstadoVersao;
use App\Domain\Eligibility\ValidadorRegra;
use App\Models\VersaoRegra;
use DomainException;

class VersaoRegraObserver
{
    public function updating(VersaoRegra $versao): void
    {
        $anterior = EstadoVersao::from($versao->getRawOriginal('estado'));

        if ($anterior === EstadoVersao::RASCUNHO) {
            if ($versao->estado === EstadoVersao::PUBLICADA) {
                if ($versao->publicada_em === null || $versao->vigencia_inicio === null
                    || $versao->vigencia_fim !== null) {
                    throw new DomainException('Publicação exige vigência imediata aberta.');
                }
                app(ValidadorRegra::class)->validar($versao);
                if (VersaoRegra::query()->where('beneficio_id', $versao->beneficio_id)
                    ->where('id', '!=', $versao->id)->where('estado', 'PUBLICADA')->exists()) {
                    throw new DomainException('Há outra Versão de Regra publicada para este Benefício.');
                }
            }

            return;
        }

        if ($anterior !== EstadoVersao::PUBLICADA
            || ! in_array($versao->estado, [EstadoVersao::SUBSTITUIDA, EstadoVersao::INATIVA], true)
            || $versao->vigencia_fim === null
            || $versao->vigencia_fim < $versao->vigencia_inicio
            || array_diff(array_keys($versao->getDirty()), ['estado', 'vigencia_fim', 'updated_at']) !== []) {
            throw new DomainException('Conteúdo publicado de Versão de Regra é imutável.');
        }
    }

    public function deleting(VersaoRegra $versao): void
    {
        if ($versao->estado !== EstadoVersao::RASCUNHO || $versao->avaliacoes()->exists()) {
            throw new DomainException('Versão de Regra publicada ou usada não pode ser excluída.');
        }
    }
}
