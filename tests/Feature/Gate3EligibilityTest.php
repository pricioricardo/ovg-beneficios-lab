<?php

namespace Tests\Feature;

use App\Domain\Eligibility\ExecutarAvaliacao;
use App\Domain\Eligibility\MotorElegibilidade;
use App\Domain\Eligibility\ValidadorRegra;
use App\Domain\Enums\ResultadoAutomatico;
use App\Models\Avaliacao;
use App\Models\Beneficiario;
use App\Models\Beneficio;
use App\Models\Comprovacao;
use App\Models\RegraElegibilidade;
use App\Models\Requisito;
use App\Models\VersaoRegra;
use Carbon\CarbonImmutable;
use Database\Seeders\Gate3Seeder;
use DomainException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class Gate3EligibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(Gate3Seeder::class);
    }

    public function test_three_synthetic_scenarios_and_all_node_results(): void
    {
        $this->assertSame('mysql', DB::connection()->getDriverName());

        foreach ([
            'LAB-000001' => ResultadoAutomatico::ELEGIVEL,
            'LAB-000002' => ResultadoAutomatico::PENDENTE_DOCUMENTACAO,
            'LAB-000003' => ResultadoAutomatico::INELEGIVEL,
        ] as $identificador => $esperado) {
            $avaliacao = $this->avaliar($identificador);
            $this->assertSame($esperado, $avaliacao->resultado_automatico);
            $this->assertSame(['1', '1.1', '1.2', '1.3'], $avaliacao->resultados->pluck('caminho')->all());
            $this->assertSame('CONCLUIDA', $avaliacao->estado->value);
            $this->assertSame(1, $avaliacao->snapshot_schema_version);
            $this->assertSame(1, $avaliacao->snapshot['schema_version']);
        }
    }

    public function test_document_is_present_through_its_last_valid_day(): void
    {
        $doc = $this->documento('LAB-000002');
        $doc->update([
            'estado' => 'APRESENTADA',
            'data_apresentacao' => CarbonImmutable::now('America/Sao_Paulo')->subDays(10)->toDateString(),
            'validade' => CarbonImmutable::now('America/Sao_Paulo')->toDateString(),
        ]);

        $avaliacao = $this->avaliar('LAB-000002');
        $this->assertSame(ResultadoAutomatico::ELEGIVEL, $avaliacao->resultado_automatico);
        $this->assertSame('APRESENTADA', $avaliacao->snapshot['valores_resolvidos']['RELATORIO_PROFISSIONAL/v1']['estado_efetivo']);
    }

    public function test_expired_document_is_pending_not_persisted_as_expired(): void
    {
        $doc = $this->documento('LAB-000002');
        $doc->update([
            'estado' => 'APRESENTADA',
            'data_apresentacao' => CarbonImmutable::now('America/Sao_Paulo')->subDays(10)->toDateString(),
            'validade' => CarbonImmutable::now('America/Sao_Paulo')->subDay()->toDateString(),
        ]);

        $avaliacao = $this->avaliar('LAB-000002');
        $this->assertSame('APRESENTADA', $doc->fresh()->estado);
        $this->assertSame(ResultadoAutomatico::PENDENTE_DOCUMENTACAO, $avaliacao->resultado_automatico);
        $this->assertSame('VENCIDA', $avaliacao->snapshot['valores_resolvidos']['RELATORIO_PROFISSIONAL/v1']['estado_efetivo']);
    }

    public function test_document_registration_rejects_future_presentation_and_inverted_validity(): void
    {
        $doc = $this->documento('LAB-000002');
        foreach ([
            ['data_apresentacao' => CarbonImmutable::now('America/Sao_Paulo')->addDay()->toDateString(), 'validade' => null],
            ['data_apresentacao' => CarbonImmutable::now('America/Sao_Paulo')->subDay()->toDateString(), 'validade' => CarbonImmutable::now('America/Sao_Paulo')->subDays(2)->toDateString()],
        ] as $datas) {
            try {
                $doc->update(['estado' => 'APRESENTADA', ...$datas]);
                $this->fail('Comprovação com datas inválidas deveria ser recusada.');
            } catch (DomainException) {
                $doc->refresh();
                $this->assertSame('AUSENTE', $doc->estado);
            }
        }
    }

    public function test_published_version_content_cannot_be_edited(): void
    {
        $versao = VersaoRegra::firstOrFail();
        $this->expectException(DomainException::class);
        $versao->update(['numero' => 2]);
    }

    public function test_published_tree_cannot_be_edited(): void
    {
        $no = RegraElegibilidade::where('chave_no', 'vulnerabilidade')->firstOrFail();
        $this->expectException(DomainException::class);
        $no->update(['valor_esperado' => ['valor' => false]]);
    }

    public function test_published_node_cannot_be_moved_to_a_draft(): void
    {
        $versao = VersaoRegra::create(['beneficio_id' => Beneficio::firstOrFail()->id, 'numero' => 2, 'estado' => 'RASCUNHO']);
        $no = RegraElegibilidade::where('chave_no', 'vulnerabilidade')->firstOrFail();
        $this->expectException(DomainException::class);
        $no->update(['versao_regra_id' => $versao->id, 'parent_id' => null]);
    }

    public function test_invalid_draft_cannot_be_published(): void
    {
        $versao = VersaoRegra::create(['beneficio_id' => Beneficio::firstOrFail()->id, 'numero' => 2, 'estado' => 'RASCUNHO']);
        $instante = CarbonImmutable::now('UTC');
        $this->expectException(DomainException::class);
        $versao->update(['estado' => 'PUBLICADA', 'publicada_em' => $instante, 'vigencia_inicio' => $instante]);
    }

    public function test_requirement_semantics_cannot_be_rewritten(): void
    {
        $requisito = Requisito::where('codigo', 'VULNERABILIDADE_SOCIAL')->firstOrFail();
        $this->expectException(DomainException::class);
        $requisito->update(['versao_semantica' => 'v2']);
    }

    public function test_nonpublished_version_is_not_used(): void
    {
        $versao = VersaoRegra::firstOrFail();
        $versao->update(['estado' => 'INATIVA', 'vigencia_fim' => CarbonImmutable::now('UTC')]);

        $this->expectException(DomainException::class);
        $this->avaliar('LAB-000001');
    }

    public function test_inactive_benefit_rejects_evaluation(): void
    {
        Beneficio::firstOrFail()->update(['estado' => 'INATIVO']);
        $this->expectException(DomainException::class);
        $this->avaliar('LAB-000001');
    }

    public function test_snapshot_and_result_do_not_follow_later_beneficiary_edits(): void
    {
        $anterior = $this->avaliar('LAB-000001');
        $beneficiario = Beneficiario::where('identificador', 'LAB-000001')->firstOrFail();
        $beneficiario->update(['vulnerabilidade_social' => false]);
        $nova = $this->avaliar('LAB-000001');

        $this->assertTrue($anterior->fresh()->snapshot['beneficiario']['vulnerabilidade_social']);
        $this->assertSame(ResultadoAutomatico::ELEGIVEL, $anterior->fresh()->resultado_automatico);
        $this->assertSame(ResultadoAutomatico::INELEGIVEL, $nova->resultado_automatico);
        $this->assertSame('v1', $anterior->snapshot['valores_resolvidos']['VULNERABILIDADE_SOCIAL/v1']['versao_semantica']);
        $this->assertSame($anterior->instante_referencia->format('Y-m-d\TH:i:s\Z'), $anterior->snapshot['instante_referencia_utc']);
    }

    public function test_completed_evaluation_and_results_are_immutable(): void
    {
        $avaliacao = $this->avaliar('LAB-000001');
        try {
            $avaliacao->update(['resultado_automatico' => ResultadoAutomatico::INELEGIVEL]);
            $this->fail('Avaliação concluída deveria ser imutável.');
        } catch (DomainException) {
            $this->assertSame(ResultadoAutomatico::ELEGIVEL, $avaliacao->fresh()->resultado_automatico);
        }

        $this->expectException(DomainException::class);
        $avaliacao->resultados->first()->delete();
    }

    public function test_completed_evaluation_rejects_new_results(): void
    {
        $avaliacao = $this->avaliar('LAB-000001');
        $resultado = $avaliacao->resultados->first()->replicate();
        $resultado->avaliacao_id = $avaliacao->id;
        $this->expectException(DomainException::class);
        $resultado->save();
    }

    public function test_incomplete_evaluation_cannot_be_marked_concluded(): void
    {
        $avaliacao = Avaliacao::create([
            'beneficiario_id' => Beneficiario::firstOrFail()->id,
            'beneficio_id' => Beneficio::firstOrFail()->id,
            'versao_regra_id' => VersaoRegra::firstOrFail()->id,
            'instante_referencia' => CarbonImmutable::now('UTC'),
            'estado' => 'INICIADA',
        ]);
        $this->expectException(DomainException::class);
        $avaliacao->update([
            'estado' => 'CONCLUIDA',
            'resultado_automatico' => 'ELEGIVEL',
            'snapshot_schema_version' => 1,
            'snapshot' => ['schema_version' => 1],
            'concluida_em' => CarbonImmutable::now('UTC'),
        ]);
    }

    public function test_invalid_operator_type_combination_is_rejected_and_rolls_back(): void
    {
        DB::table('regras_elegibilidade')->where('chave_no', 'vulnerabilidade')->update(['operador' => 'PRESENT']);
        try {
            $this->avaliar('LAB-000001');
            $this->fail('Combinação inválida deveria ser recusada.');
        } catch (DomainException) {
            $this->assertSame(0, Avaliacao::count());
        }
    }

    public function test_arbitrary_payload_cannot_be_interpreted_as_php_or_sql(): void
    {
        foreach (['php' => 'system("id")', 'sql' => 'SELECT * FROM users', 'class' => 'Runtime\\Executor'] as $campo => $codigo) {
            DB::table('regras_elegibilidade')->where('chave_no', 'vulnerabilidade')
                ->update(['valor_esperado' => json_encode(['valor' => true, $campo => $codigo])]);
            try {
                $this->avaliar('LAB-000001');
                $this->fail('Payload arbitrário deveria ser recusado.');
            } catch (DomainException) {
                $this->assertSame(0, Avaliacao::count());
            }
        }
    }

    public function test_unregistered_resolver_cannot_select_arbitrary_path_or_method(): void
    {
        $requisito = Requisito::create([
            'codigo' => 'RUNTIME_EXECUTOR', 'versao_semantica' => 'v1', 'rotulo' => 'Não permitido',
            'tipo' => 'BOOLEAN', 'fonte' => 'BENEFICIARIO', 'estado' => 'ATIVO',
        ]);
        DB::table('regras_elegibilidade')->where('chave_no', 'vulnerabilidade')->update(['requisito_id' => $requisito->id]);

        $this->expectException(DomainException::class);
        $this->avaliar('LAB-000001');
    }

    public function test_or_precedence_evaluates_every_child(): void
    {
        $beneficio = Beneficio::firstOrFail();
        $versao = VersaoRegra::create(['beneficio_id' => $beneficio->id, 'numero' => 2, 'estado' => 'RASCUNHO']);
        $raiz = $versao->nos()->create(['chave_no' => 'raiz-or', 'ordem' => 0, 'tipo_no' => 'GROUP', 'operador_grupo' => 'OR']);
        foreach (['VULNERABILIDADE_SOCIAL', 'LIMITACAO_MOBILIDADE'] as $indice => $codigo) {
            $versao->nos()->create([
                'parent_id' => $raiz->id, 'chave_no' => 'filho-'.$indice, 'ordem' => $indice + 1,
                'tipo_no' => 'CONDITION', 'requisito_id' => Requisito::where('codigo', $codigo)->firstOrFail()->id,
                'operador' => 'EQ', 'valor_esperado' => ['valor' => true],
            ]);
        }
        [$raiz, $nos] = app(ValidadorRegra::class)->validar($versao);
        [$resultado, $nosAvaliados] = app(MotorElegibilidade::class)->avaliar($raiz, $nos, [
            'VULNERABILIDADE_SOCIAL/v1' => ['valor' => null],
            'LIMITACAO_MOBILIDADE/v1' => ['valor' => true],
        ]);
        $this->assertSame(ResultadoAutomatico::ELEGIVEL, $resultado);
        $this->assertCount(3, $nosAvaliados);
    }

    public function test_and_prioritizes_indeterminate_over_pending_documentation(): void
    {
        [$raiz, $nos] = app(ValidadorRegra::class)->validar(VersaoRegra::firstOrFail());
        [$resultado, $resultados] = app(MotorElegibilidade::class)->avaliar($raiz, $nos, [
            'VULNERABILIDADE_SOCIAL/v1' => ['valor' => null],
            'LIMITACAO_MOBILIDADE/v1' => ['valor' => true],
            'RELATORIO_PROFISSIONAL/v1' => ['valor' => false, 'estado_efetivo' => 'AUSENTE'],
        ]);
        $this->assertSame(ResultadoAutomatico::REQUER_ANALISE_HUMANA, $resultado);
        $this->assertCount(4, $resultados);
    }

    public function test_database_rejects_evaluation_with_rule_from_another_benefit(): void
    {
        $outro = Beneficio::create(['codigo' => 'TESTE_ESTRUTURAL', 'nome' => 'Catálogo estrutural sintético', 'descricao' => 'Somente teste.', 'estado' => 'ATIVO']);
        $this->expectException(QueryException::class);
        DB::table('avaliacoes')->insert([
            'beneficiario_id' => Beneficiario::firstOrFail()->id,
            'beneficio_id' => $outro->id,
            'versao_regra_id' => VersaoRegra::firstOrFail()->id,
            'instante_referencia' => CarbonImmutable::now('UTC'),
            'estado' => 'INICIADA',
            'created_at' => CarbonImmutable::now('UTC'),
            'updated_at' => CarbonImmutable::now('UTC'),
        ]);
    }

    public function test_database_enforces_simple_constraints_and_unique_current_document(): void
    {
        $beneficiario = Beneficiario::firstOrFail();
        $falhas = 0;
        foreach ([['integrantes_familia' => 0], ['identificador' => '12345678901'], ['renda_familiar_centavos' => -1]] as $dados) {
            try {
                DB::table('beneficiarios')->where('id', $beneficiario->id)->update($dados);
                $this->fail('O banco deveria rejeitar o valor inválido.');
            } catch (QueryException) {
                $falhas++;
            }
        }
        $this->assertSame(3, $falhas);
        $this->assertSame(2, $beneficiario->fresh()->integrantes_familia);
        $this->assertSame('LAB-000001', $beneficiario->fresh()->identificador);
        $this->expectException(QueryException::class);
        Comprovacao::create(['beneficiario_id' => $beneficiario->id, 'tipo' => 'RELATORIO_PROFISSIONAL', 'estado' => 'AUSENTE']);
    }

    public function test_seed_is_safe_to_run_again(): void
    {
        $avaliacao = $this->avaliar('LAB-000001');
        $this->seed(Gate3Seeder::class);
        $this->assertSame(1, Beneficio::count());
        $this->assertSame(3, Requisito::count());
        $this->assertSame(1, VersaoRegra::count());
        $this->assertSame(4, RegraElegibilidade::count());
        $this->assertSame(3, Beneficiario::count());
        $this->assertSame(3, Comprovacao::count());
        $this->assertSame(ResultadoAutomatico::ELEGIVEL, $avaliacao->fresh()->resultado_automatico);
    }

    private function avaliar(string $identificador): Avaliacao
    {
        return app(ExecutarAvaliacao::class)->executar(
            Beneficiario::where('identificador', $identificador)->firstOrFail()->id,
        );
    }

    private function documento(string $identificador): Comprovacao
    {
        return $this->avaliadoBeneficiario($identificador)->comprovacoes()->firstOrFail();
    }

    private function avaliadoBeneficiario(string $identificador): Beneficiario
    {
        return Beneficiario::where('identificador', $identificador)->firstOrFail();
    }
}
