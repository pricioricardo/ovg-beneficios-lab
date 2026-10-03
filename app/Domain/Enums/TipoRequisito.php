<?php

namespace App\Domain\Enums;

enum TipoRequisito: string
{
    case BOOLEAN = 'BOOLEAN';
    case INTEGER = 'INTEGER';
    case DECIMAL = 'DECIMAL';
    case DATE = 'DATE';
    case ENUM = 'ENUM';
    case DOCUMENT_PRESENCE = 'DOCUMENT_PRESENCE';
}
