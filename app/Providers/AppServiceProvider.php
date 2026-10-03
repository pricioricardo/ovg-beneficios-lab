<?php

namespace App\Providers;

use App\Models\Avaliacao;
use App\Models\Beneficiario;
use App\Models\Comprovacao;
use App\Models\RegraElegibilidade;
use App\Models\Requisito;
use App\Models\ResultadoAvaliacao;
use App\Models\VersaoRegra;
use App\Observers\AvaliacaoObserver;
use App\Observers\BeneficiarioObserver;
use App\Observers\ComprovacaoObserver;
use App\Observers\RegraElegibilidadeObserver;
use App\Observers\RequisitoObserver;
use App\Observers\ResultadoAvaliacaoObserver;
use App\Observers\VersaoRegraObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Beneficiario::observe(BeneficiarioObserver::class);
        Comprovacao::observe(ComprovacaoObserver::class);
        Requisito::observe(RequisitoObserver::class);
        VersaoRegra::observe(VersaoRegraObserver::class);
        RegraElegibilidade::observe(RegraElegibilidadeObserver::class);
        Avaliacao::observe(AvaliacaoObserver::class);
        ResultadoAvaliacao::observe(ResultadoAvaliacaoObserver::class);
    }
}
