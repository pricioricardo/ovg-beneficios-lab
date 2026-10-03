<?php

namespace App\Domain\Enums;

enum Desfecho: string
{
    case ATENDIDA = 'ATENDIDA';
    case NAO_ATENDIDA = 'NAO_ATENDIDA';
    case DOCUMENTACAO_PENDENTE = 'DOCUMENTACAO_PENDENTE';
    case INDETERMINADA = 'INDETERMINADA';
}
