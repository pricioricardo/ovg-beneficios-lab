<?php

namespace App\Filament\Resources\Avaliacoes;

use App\Filament\Resources\Beneficiarios\BeneficiarioResource;
use App\Filament\Resources\Avaliacoes\Pages\ViewAvaliacao;
use App\Filament\Resources\Avaliacoes\Schemas\AvaliacaoInfolist;
use App\Models\Avaliacao;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;

class AvaliacaoResource extends Resource
{
    protected static ?string $model = Avaliacao::class;

    protected static ?string $recordTitleAttribute = 'id';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'avaliacoes';

    public static function infolist(Schema $schema): Schema
    {
        return AvaliacaoInfolist::configure($schema);
    }

    public static function getPages(): array
    {
        return [
            'view' => ViewAvaliacao::route('/{record}'),
        ];
    }

    public static function getIndexUrl(array $parameters = [], bool $isAbsolute = true, ?string $panel = null, ?Model $tenant = null, bool $shouldGuessMissingParameters = false): string
    {
        return BeneficiarioResource::getUrl('index', $parameters, $isAbsolute, $panel, $tenant, $shouldGuessMissingParameters);
    }
}
