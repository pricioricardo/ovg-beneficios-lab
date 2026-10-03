<?php

namespace App\Filament\Resources\Beneficiarios\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BeneficiariosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('identificador')->label('Identificador LAB')->searchable(),
                TextColumn::make('nome')->searchable(),
                TextColumn::make('vulnerabilidade_social')->label('Vulnerabilidade')
                    ->formatStateUsing(fn ($state): string => $state ? 'Sim' : 'Não'),
                TextColumn::make('limitacao_mobilidade')->label('Mobilidade limitada')
                    ->formatStateUsing(fn ($state): string => $state ? 'Sim' : 'Não'),
                TextColumn::make('estado')->badge(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ]);
    }
}
