<?php

namespace App\Filament\Resources\Beneficiarios\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ComprovacoesRelationManager extends RelationManager
{
    protected static string $relationship = 'comprovacoes';

    protected static ?string $title = 'Comprovações';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('tipo')->label('Tipo')
                ->options(['RELATORIO_PROFISSIONAL' => 'Relatório profissional'])->required(),
            Select::make('estado')->options(['AUSENTE' => 'Ausente', 'APRESENTADA' => 'Apresentada'])
                ->default('AUSENTE')->required()->live(),
            DatePicker::make('data_apresentacao')->label('Data de apresentação')
                ->required(fn (Get $get): bool => $get('estado') === 'APRESENTADA')
                ->maxDate(now('America/Sao_Paulo'))
                ->visible(fn (Get $get): bool => $get('estado') === 'APRESENTADA'),
            DatePicker::make('validade')->label('Validade (inclusiva)')
                ->minDate(fn (Get $get) => $get('data_apresentacao'))
                ->visible(fn (Get $get): bool => $get('estado') === 'APRESENTADA'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('tipo')
            ->columns([
                TextColumn::make('tipo')->label('Tipo'),
                TextColumn::make('estado')->badge(),
                TextColumn::make('data_apresentacao')->label('Apresentação')->date('d/m/Y'),
                TextColumn::make('validade')->date('d/m/Y'),
            ])
            ->headerActions([
                CreateAction::make()->label('Registrar relatório')
                    ->visible(fn (): bool => ! $this->getOwnerRecord()->comprovacoes()
                        ->where('tipo', 'RELATORIO_PROFISSIONAL')->exists())
                    ->mutateDataUsing(fn (array $data): array => $data['estado'] === 'AUSENTE'
                        ? array_merge($data, ['data_apresentacao' => null, 'validade' => null]) : $data),
            ])
            ->recordActions([
                EditAction::make()->mutateDataUsing(fn (array $data): array => $data['estado'] === 'AUSENTE'
                    ? array_merge($data, ['data_apresentacao' => null, 'validade' => null]) : $data),
            ]);
    }
}
