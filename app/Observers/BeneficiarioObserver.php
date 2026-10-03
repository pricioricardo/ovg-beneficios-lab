<?php

namespace App\Observers;

use App\Models\Beneficiario;
use DomainException;

class BeneficiarioObserver
{
    public function saving(Beneficiario $beneficiario): void
    {
        $hoje = now('America/Sao_Paulo')->toDateString();
        $ufs = ['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'];

        if (! preg_match('/^LAB-[A-Z0-9-]+$/', (string) $beneficiario->identificador)
            || ! in_array($beneficiario->uf, $ufs, true)
            || $beneficiario->data_nascimento?->toDateString() > $hoje
            || $beneficiario->renda_familiar_centavos < 0
            || $beneficiario->integrantes_familia < 1) {
            throw new DomainException('Dados cadastrais sintéticos inválidos.');
        }

        if ($beneficiario->gestante) {
            if ($beneficiario->nascimento_bebe_ocorrido !== false
                || $beneficiario->data_provavel_parto === null
                || $beneficiario->data_nascimento_bebe !== null) {
                throw new DomainException('Episódio gestacional inconsistente.');
            }
        } elseif ($beneficiario->data_provavel_parto !== null
            || ($beneficiario->nascimento_bebe_ocorrido === true
                ? ($beneficiario->data_nascimento_bebe === null || $beneficiario->data_nascimento_bebe->toDateString() > $hoje)
                : $beneficiario->data_nascimento_bebe !== null)) {
            throw new DomainException('Episódio gestacional inconsistente.');
        }
    }

    public function deleting(Beneficiario $beneficiario): void
    {
        if ($beneficiario->avaliacoes()->exists()) {
            throw new DomainException('Beneficiário com histórico deve ser inativado.');
        }
    }
}
