<?php

namespace App\Observers;

use App\Models\Requisito;
use DomainException;

class RequisitoObserver
{
    public function updating(Requisito $requisito): void
    {
        foreach (['codigo', 'versao_semantica', 'rotulo', 'tipo', 'fonte'] as $campo) {
            if ($requisito->isDirty($campo)) {
                throw new DomainException('Nova semântica exige outro Requisito versionado.');
            }
        }
    }

    public function deleting(Requisito $requisito): void
    {
        if ($requisito->regras()->exists()) {
            throw new DomainException('Requisito usado deve ser inativado.');
        }
    }
}
