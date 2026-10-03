<?php

namespace App\Filament\Resources\Beneficiarios\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BeneficiarioForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Cadastro sintético')->schema([
                    TextInput::make('identificador')->label('Identificador LAB')->required()
                        ->rules(['regex:/^LAB-[A-Z0-9-]+$/'])->unique(ignoreRecord: true),
                    TextInput::make('nome')->required()->maxLength(255)
                        ->helperText('Use somente um nome fictício; nunca insira dados reais.'),
                    DatePicker::make('data_nascimento')->label('Data de nascimento')->required()->maxDate(now('America/Sao_Paulo')),
                    TextInput::make('municipio')->label('Município')->required(),
                    Select::make('uf')->label('UF')->options(array_combine(
                        ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'],
                        ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'],
                    ))->default('GO')->required(),
                    TextInput::make('renda_familiar_centavos')->label('Renda familiar (centavos)')
                        ->integer()->minValue(0)->default(0)->required(),
                    TextInput::make('integrantes_familia')->label('Integrantes da família')
                        ->integer()->minValue(1)->default(1)->required(),
                    Toggle::make('vulnerabilidade_social')->label('Vulnerabilidade social')->default(false),
                    Toggle::make('limitacao_mobilidade')->label('Limitação de mobilidade')->default(false),
                    Select::make('estado')->options(['ATIVO' => 'Ativo', 'INATIVO' => 'Inativo'])->default('ATIVO')->required(),
                ])->columns(2),
            ]);
    }
}
