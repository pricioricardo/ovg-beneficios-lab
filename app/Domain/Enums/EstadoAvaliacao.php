<?php

namespace App\Domain\Enums;

enum EstadoAvaliacao: string
{
    case INICIADA = 'INICIADA';
    case CONCLUIDA = 'CONCLUIDA';
    case FALHA_TECNICA = 'FALHA_TECNICA';
}
