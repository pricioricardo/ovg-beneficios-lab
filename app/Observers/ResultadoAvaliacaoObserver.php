<?php

namespace App\Observers;

use App\Domain\Enums\EstadoAvaliacao;
use App\Models\ResultadoAvaliacao;
use DomainException;

class ResultadoAvaliacaoObserver
{
    public function creating(ResultadoAvaliacao $resultado): void
    {
        $avaliacao = $resultado->avaliacao()->firstOrFail();
        if ($avaliacao->estado !== EstadoAvaliacao::INICIADA
            || $resultado->regraElegibilidade()->firstOrFail()->versao_regra_id !== $avaliacao->versao_regra_id) {
            throw new DomainException('Resultado deve pertencer à Versão de Regra da Avaliação iniciada.');
        }
    }

    public function updating(ResultadoAvaliacao $resultado): void
    {
        $this->proteger($resultado);
    }

    public function deleting(ResultadoAvaliacao $resultado): void
    {
        $this->proteger($resultado);
    }

    private function proteger(ResultadoAvaliacao $resultado): void
    {
        if ($resultado->avaliacao()->first()?->estado === EstadoAvaliacao::CONCLUIDA) {
            throw new DomainException('Resultado de Avaliação concluída é imutável.');
        }
    }
}
