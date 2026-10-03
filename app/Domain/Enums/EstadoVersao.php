<?php

namespace App\Domain\Enums;

enum EstadoVersao: string
{
    case RASCUNHO = 'RASCUNHO';
    case PUBLICADA = 'PUBLICADA';
    case SUBSTITUIDA = 'SUBSTITUIDA';
    case INATIVA = 'INATIVA';
}
