<?php

namespace App\Filament\Resources\Avaliacoes\Schemas;

use App\Domain\Enums\Desfecho;
use App\Domain\Enums\ResultadoAutomatico;
use App\Domain\Enums\TipoNo;
use App\Models\Avaliacao;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AvaliacaoInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Avaliação técnica do laboratório')->schema([
                    TextEntry::make('beneficio.nome')->label('Benefício'),
                    TextEntry::make('versaoRegra.numero')->label('Regra v'),
                    TextEntry::make('instante_referencia')->label('Instante UTC')->dateTime('d/m/Y H:i:s'),
                    TextEntry::make('resultado_exibicao')->label('Resultado automático')
                        ->state(fn (Avaliacao $record): string => match ($record->resultado_automatico) {
                            ResultadoAutomatico::ELEGIVEL => 'ELEGÍVEL',
                            ResultadoAutomatico::INELEGIVEL => 'INELEGÍVEL',
                            ResultadoAutomatico::PENDENTE_DOCUMENTACAO => 'PENDENTE DE DOCUMENTAÇÃO',
                            ResultadoAutomatico::REQUER_ANALISE_HUMANA => 'REQUER ANÁLISE HUMANA',
                            default => 'Sem resultado',
                        })->badge(),
                ])->columns(2),
                Section::make('Explicação dos critérios')->schema([
                    TextEntry::make('criterios')->hiddenLabel()->markdown()
                        ->state(fn (Avaliacao $record): string => $record->resultados
                            ->filter(fn ($resultado): bool => $resultado->tipo_no === TipoNo::CONDITION)
                            ->map(fn ($resultado): string => match ($resultado->desfecho) {
                                Desfecho::ATENDIDA => '✓ ',
                                Desfecho::NAO_ATENDIDA => '✕ ',
                                Desfecho::DOCUMENTACAO_PENDENTE => '⚠ ',
                                Desfecho::INDETERMINADA => '? ',
                            }.$resultado->explicacao)
                            ->implode("  \n")),
                    TextEntry::make('consolidacao')->label('Consolidação')
                        ->state(fn (Avaliacao $record): string => $record->resultados
                            ->firstWhere('caminho', '1')?->explicacao ?? ''),
                ]),
                TextEntry::make('aviso')->label('Aviso')
                    ->state('Resultado técnico do laboratório; não representa decisão administrativa.'),
            ]);
    }
}
