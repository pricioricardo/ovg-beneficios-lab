<?php

namespace App\Domain\Enums;

enum Operador: string
{
    case EQ = 'EQ';
    case NEQ = 'NEQ';
    case PRESENT = 'PRESENT';
    case NOT_PRESENT = 'NOT_PRESENT';
}
