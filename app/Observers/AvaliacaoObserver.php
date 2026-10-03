<?php

namespace App\Observers;

use App\Domain\Enums\EstadoAvaliacao;
use App\Models\Avaliacao;
use DomainException;

class AvaliacaoObserver
{
    public function creating(Avaliacao $avaliacao): void
    {
        if ($avaliacao->estado !== EstadoAvaliacao::INICIADA) {
            throw new DomainException('Avaliação deve iniciar antes de ser concluída.');
        }
    }

    public function updating(Avaliacao $avaliacao): void
    {
        if ($avaliacao->getRawOriginal('estado') === EstadoAvaliacao::CONCLUIDA->value) {
            throw new DomainException('Avaliação concluída é imutável.');
        }

        if ($avaliacao->estado === EstadoAvaliacao::CONCLUIDA
            && ($avaliacao->snapshot_schema_version !== 1
                || ! is_array($avaliacao->snapshot)
                || $avaliacao->resultado_automatico === null
                || $avaliacao->concluida_em === null
                || $avaliacao->resultados()->count() !== $avaliacao->versaoRegra()->firstOrFail()->nos()->count())) {
            throw new DomainException('Avaliação concluída exige snapshot e resultado de todos os nós.');
        }
    }

    public function deleting(Avaliacao $avaliacao): void
    {
        if ($avaliacao->estado === EstadoAvaliacao::CONCLUIDA) {
            throw new DomainException('Avaliação concluída é imutável.');
        }
    }
}
