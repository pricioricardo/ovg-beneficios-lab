<?php

namespace App\Filament\Resources\Beneficiarios;

use App\Filament\Resources\Beneficiarios\Pages\CreateBeneficiario;
use App\Filament\Resources\Beneficiarios\Pages\EditBeneficiario;
use App\Filament\Resources\Beneficiarios\Pages\ListBeneficiarios;
use App\Filament\Resources\Beneficiarios\Pages\ViewBeneficiario;
use App\Filament\Resources\Beneficiarios\RelationManagers\AvaliacoesRelationManager;
use App\Filament\Resources\Beneficiarios\RelationManagers\ComprovacoesRelationManager;
use App\Filament\Resources\Beneficiarios\Schemas\BeneficiarioForm;
use App\Filament\Resources\Beneficiarios\Schemas\BeneficiarioInfolist;
use App\Filament\Resources\Beneficiarios\Tables\BeneficiariosTable;
use App\Models\Beneficiario;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BeneficiarioResource extends Resource
{
    protected static ?string $model = Beneficiario::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'nome';

    protected static ?string $navigationLabel = 'Beneficiários';

    protected static ?string $modelLabel = 'Beneficiário';

    protected static ?string $pluralModelLabel = 'Beneficiários';

    public static function form(Schema $schema): Schema
    {
        return BeneficiarioForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return BeneficiarioInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BeneficiariosTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ComprovacoesRelationManager::class,
            AvaliacoesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBeneficiarios::route('/'),
            'create' => CreateBeneficiario::route('/create'),
            'view' => ViewBeneficiario::route('/{record}'),
            'edit' => EditBeneficiario::route('/{record}/edit'),
        ];
    }
}
