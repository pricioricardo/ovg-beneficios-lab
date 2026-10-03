<?php

namespace App\Filament\Resources\Beneficiarios\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BeneficiarioInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Beneficiário sintético')->schema([
                    TextEntry::make('identificador')->label('Identificador LAB'),
                    TextEntry::make('nome'),
                    TextEntry::make('municipio')->label('Município'),
                    TextEntry::make('uf')->label('UF'),
                    TextEntry::make('vulnerabilidade_social')->label('Vulnerabilidade social')->formatStateUsing(fn ($state) => $state ? 'Sim' : 'Não'),
                    TextEntry::make('limitacao_mobilidade')->label('Limitação de mobilidade')->formatStateUsing(fn ($state) => $state ? 'Sim' : 'Não'),
                    TextEntry::make('estado'),
                ])->columns(2),
                TextEntry::make('aviso')->label('Aviso')->state('Resultado técnico do laboratório; não representa decisão administrativa.'),
            ]);
    }
}
