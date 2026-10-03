<?php

namespace App\Filament\Resources\Beneficiarios\Pages;

use App\Filament\Resources\Beneficiarios\BeneficiarioResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBeneficiario extends CreateRecord
{
    protected static string $resource = BeneficiarioResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return [
            'deficiencia' => false,
            'gestante' => false,
            'nascimento_bebe_ocorrido' => false,
            ...$data,
        ];
    }
}
