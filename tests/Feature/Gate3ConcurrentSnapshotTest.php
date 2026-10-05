<?php

namespace Tests\Feature;

use App\Domain\Eligibility\ExecutarAvaliacao;
use App\Domain\Eligibility\MotorElegibilidade;
use App\Domain\Eligibility\ResolvedorRequisitos;
use App\Domain\Eligibility\ValidadorRegra;
use App\Domain\Enums\ResultadoAutomatico;
use App\Models\Beneficiario;
use App\Models\Comprovacao;
use App\Models\VersaoRegra;
use App\Support\TestDatabaseGuard;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class Gate3ConcurrentSnapshotTest extends TestCase
{
    public function test_later_document_edit_affects_only_the_next_evaluation(): void
    {
        // Fixture confirmada antes da segunda conexão; RefreshDatabase manteria
        // o seed em uma transação invisível para ela.
        TestDatabaseGuard::assertSafe();
        $this->assertSame(0, Artisan::call('migrate:fresh', ['--seed' => true, '--force' => true]));

        $beneficiario = Beneficiario::where('identificador', 'LAB-000002')->sole();
        $documento = $beneficiario->comprovacoes()->sole();
        $this->assertSame('AUSENTE', $documento->estado);

        config(['database.connections.gate3_second' => config('database.connections.mysql')]);
        $validador = new class(app(ResolvedorRequisitos::class)) extends ValidadorRegra {
            public int $documentoId;

            public function validar(VersaoRegra $versao): array
            {
                $arvore = parent::validar($versao);
                Comprovacao::on('gate3_second')->findOrFail($this->documentoId)->update([
                    'estado' => 'APRESENTADA',
                    'data_apresentacao' => now('America/Sao_Paulo')->toDateString(),
                    'validade' => null,
                ]);

                return $arvore;
            }
        };
        $validador->documentoId = $documento->id;

        try {
            $primeira = (new ExecutarAvaliacao($validador, app(ResolvedorRequisitos::class), app(MotorElegibilidade::class)))
                ->executar($beneficiario->id);

            $this->assertSame(ResultadoAutomatico::PENDENTE_DOCUMENTACAO, $primeira->resultado_automatico);
            $this->assertSame('AUSENTE', $primeira->snapshot['valores_resolvidos']['RELATORIO_PROFISSIONAL/v1']['estado_efetivo']);
            $this->assertSame('APRESENTADA', $documento->fresh()->estado);

            $segunda = app(ExecutarAvaliacao::class)->executar($beneficiario->id);
            $this->assertSame(ResultadoAutomatico::ELEGIVEL, $segunda->resultado_automatico);
            $this->assertSame('APRESENTADA', $segunda->snapshot['valores_resolvidos']['RELATORIO_PROFISSIONAL/v1']['estado_efetivo']);
        } finally {
            DB::connection('gate3_second')->disconnect();
        }
    }
}
