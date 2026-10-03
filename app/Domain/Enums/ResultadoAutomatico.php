<?php

namespace App\Domain\Enums;

enum ResultadoAutomatico: string
{
    case ELEGIVEL = 'ELEGIVEL';
    case INELEGIVEL = 'INELEGIVEL';
    case PENDENTE_DOCUMENTACAO = 'PENDENTE_DOCUMENTACAO';
    case REQUER_ANALISE_HUMANA = 'REQUER_ANALISE_HUMANA';
}
