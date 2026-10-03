<?php

namespace App\Observers;

use App\Domain\Enums\EstadoVersao;
use App\Models\RegraElegibilidade;
use DomainException;

class RegraElegibilidadeObserver
{
    public function saving(RegraElegibilidade $no): void
    {
        if (($no->exists && \App\Models\VersaoRegra::find($no->getRawOriginal('versao_regra_id'))?->estado !== EstadoVersao::RASCUNHO)
            || $no->versaoRegra()->first()?->estado !== EstadoVersao::RASCUNHO) {
            throw new DomainException('Árvore publicada é imutável.');
        }
    }

    public function deleting(RegraElegibilidade $no): void
    {
        if ($no->versaoRegra()->first()?->estado !== EstadoVersao::RASCUNHO) {
            throw new DomainException('Árvore publicada é imutável.');
        }
    }
}
