<?php

namespace App\Filament\Resources\Beneficiarios\RelationManagers;

use App\Filament\Resources\Avaliacoes\AvaliacaoResource;
use App\Models\Avaliacao;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AvaliacoesRelationManager extends RelationManager
{
    protected static string $relationship = 'avaliacoes';

    protected static ?string $title = 'Avaliações anteriores';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('instante_referencia')->label('Instante UTC')->dateTime('d/m/Y H:i:s'),
                TextColumn::make('beneficio.nome')->label('Benefício'),
                TextColumn::make('resultado_automatico')->label('Resultado automático')->badge(),
            ])
            ->recordActions([
                ViewAction::make()->url(fn (Avaliacao $record): string => AvaliacaoResource::getUrl('view', ['record' => $record])),
            ]);
    }
}
