<?php

namespace App\Observers;

use App\Models\Comprovacao;
use DomainException;

class ComprovacaoObserver
{
    public function saving(Comprovacao $comprovacao): void
    {
        $hoje = now('America/Sao_Paulo')->toDateString();

        if ($comprovacao->estado === 'AUSENTE') {
            if ($comprovacao->data_apresentacao !== null || $comprovacao->validade !== null) {
                throw new DomainException('Comprovação ausente não possui datas.');
            }

            return;
        }

        if ($comprovacao->estado !== 'APRESENTADA'
            || $comprovacao->data_apresentacao === null
            || $comprovacao->data_apresentacao->toDateString() > $hoje
            || ($comprovacao->validade !== null && $comprovacao->validade < $comprovacao->data_apresentacao)) {
            throw new DomainException('Datas ou estado da comprovação inválidos.');
        }
    }
}
