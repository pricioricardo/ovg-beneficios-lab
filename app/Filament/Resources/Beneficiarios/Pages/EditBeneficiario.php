<?php

namespace App\Filament\Resources\Beneficiarios\Pages;

use App\Filament\Resources\Beneficiarios\BeneficiarioResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditBeneficiario extends EditRecord
{
    protected static string $resource = BeneficiarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }
}
