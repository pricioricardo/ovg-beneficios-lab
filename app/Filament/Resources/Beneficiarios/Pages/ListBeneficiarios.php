<?php

namespace App\Filament\Resources\Beneficiarios\Pages;

use App\Filament\Resources\Beneficiarios\BeneficiarioResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBeneficiarios extends ListRecords
{
    protected static string $resource = BeneficiarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
