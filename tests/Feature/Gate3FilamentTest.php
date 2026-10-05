<?php

namespace Tests\Feature;

use App\Domain\Enums\ResultadoAutomatico;
use App\Filament\Resources\Avaliacoes\AvaliacaoResource;
use App\Filament\Resources\Beneficiarios\BeneficiarioResource;
use App\Filament\Resources\Beneficiarios\Pages\CreateBeneficiario;
use App\Filament\Resources\Beneficiarios\Pages\EditBeneficiario;
use App\Filament\Resources\Beneficiarios\Pages\ViewBeneficiario;
use App\Filament\Resources\Beneficiarios\RelationManagers\ComprovacoesRelationManager;
use App\Models\Avaliacao;
use App\Models\Beneficiario;
use Database\Seeders\Gate3Seeder;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Support\SafeRefreshDatabase;

class Gate3FilamentTest extends TestCase
{
    use SafeRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(Gate3Seeder::class);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_admin_and_beneficiary_list_are_accessible_without_login(): void
    {
        $this->get('/admin')->assertOk();
        $this->get(BeneficiarioResource::getUrl('index'))->assertOk()->assertSee('Pessoa Exemplo A');
        $this->get(BeneficiarioResource::getUrl('create'))->assertOk();
        $this->assertFalse(BeneficiarioResource::hasPage('login'));
    }

    public function test_beneficiary_can_be_created_and_edited_in_filament(): void
    {
        Livewire::test(CreateBeneficiario::class)
            ->set('data', [
                'identificador' => 'LAB-000004',
                'nome' => 'Pessoa Exemplo D',
                'data_nascimento' => '1988-01-01',
                'municipio' => 'Cidade Laboratório',
                'uf' => 'GO',
                'renda_familiar_centavos' => 0,
                'integrantes_familia' => 1,
                'vulnerabilidade_social' => true,
                'limitacao_mobilidade' => true,
                'estado' => 'ATIVO',
            ])
            ->assertFormSet(['identificador' => 'LAB-000004', 'nome' => 'Pessoa Exemplo D'])
            ->call('create')
            ->assertHasNoFormErrors();

        $beneficiario = Beneficiario::where('identificador', 'LAB-000004')->firstOrFail();
        $this->assertSame('Pessoa Exemplo D', $beneficiario->nome);

        Livewire::test(EditBeneficiario::class, ['record' => $beneficiario->id])
            ->set('data.nome', 'Pessoa Exemplo D Atualizada')
            ->call('save')
            ->assertHasNoFormErrors();
        $this->assertSame('Pessoa Exemplo D Atualizada', $beneficiario->fresh()->nome);
    }

    public function test_evaluate_action_creates_historical_result_and_explanation_page(): void
    {
        $beneficiario = Beneficiario::where('identificador', 'LAB-000002')->firstOrFail();
        Livewire::test(ViewBeneficiario::class, ['record' => $beneficiario->id])
            ->callAction('avaliarCadeiraRodas');

        $avaliacao = Avaliacao::firstOrFail();
        $this->assertSame(ResultadoAutomatico::PENDENTE_DOCUMENTACAO, $avaliacao->resultado_automatico);
        $this->get(AvaliacaoResource::getUrl('view', ['record' => $avaliacao]))
            ->assertOk()
            ->assertSee('Relatório profissional')
            ->assertSee('PENDENTE DE DOCUMENTAÇÃO')
            ->assertSee('não representa decisão administrativa');
    }

    public function test_report_state_can_be_registered_in_filament(): void
    {
        $beneficiario = Beneficiario::where('identificador', 'LAB-000002')->firstOrFail();
        $documento = $beneficiario->comprovacoes()->firstOrFail();

        Livewire::test(ComprovacoesRelationManager::class, [
            'ownerRecord' => $beneficiario,
            'pageClass' => ViewBeneficiario::class,
        ])->mountTableAction('edit', $documento)
            ->set('mountedActions.0.data', [
            'tipo' => 'RELATORIO_PROFISSIONAL',
            'estado' => 'APRESENTADA',
            'data_apresentacao' => now('America/Sao_Paulo')->toDateString(),
            'validade' => null,
            ])
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $this->assertSame('APRESENTADA', $documento->fresh()->estado);
    }

    public function test_report_can_be_created_for_beneficiary_without_one(): void
    {
        $beneficiario = Beneficiario::where('identificador', 'LAB-000001')->firstOrFail();
        $beneficiario->comprovacoes()->firstOrFail()->delete();

        Livewire::test(ComprovacoesRelationManager::class, [
            'ownerRecord' => $beneficiario,
            'pageClass' => ViewBeneficiario::class,
        ])->mountTableAction('create')
            ->set('mountedActions.0.data', [
                'tipo' => 'RELATORIO_PROFISSIONAL',
                'estado' => 'APRESENTADA',
                'data_apresentacao' => now('America/Sao_Paulo')->toDateString(),
                'validade' => null,
            ])
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $this->assertSame('APRESENTADA', $beneficiario->comprovacoes()->firstOrFail()->estado);
    }

    public function test_rule_editor_route_does_not_exist(): void
    {
        $this->get('/admin/versoes-regra')->assertNotFound();
        $this->get('/admin/regras-elegibilidade')->assertNotFound();
    }
}
