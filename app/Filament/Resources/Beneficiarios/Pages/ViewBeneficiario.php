<?php

namespace App\Filament\Resources\Beneficiarios\Pages;

use App\Filament\Resources\Beneficiarios\BeneficiarioResource;
use App\Filament\Resources\Avaliacoes\AvaliacaoResource;
use App\Domain\Eligibility\ExecutarAvaliacao;
use DomainException;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewBeneficiario extends ViewRecord
{
    protected static string $resource = BeneficiarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            Action::make('avaliarCadeiraRodas')
                ->label('Avaliar Cadeira de Rodas')
                ->visible(fn (): bool => $this->getRecord()->estado === 'ATIVO')
                ->action(function (): void {
                    try {
                        $avaliacao = app(ExecutarAvaliacao::class)->executar($this->getRecord()->getKey());
                    } catch (DomainException $exception) {
                        Notification::make()->title('Avaliação indisponível')->body($exception->getMessage())->danger()->send();

                        return;
                    }

                    $this->redirect(AvaliacaoResource::getUrl('view', ['record' => $avaliacao]));
                }),
        ];
    }
}
