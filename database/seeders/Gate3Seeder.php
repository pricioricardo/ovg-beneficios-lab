<?php

namespace Database\Seeders;

use App\Domain\Eligibility\ValidadorRegra;
use App\Models\Beneficiario;
use App\Models\Beneficio;
use App\Models\Comprovacao;
use App\Models\Requisito;
use App\Models\VersaoRegra;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class Gate3Seeder extends Seeder
{
    public function run(): void
    {
        $beneficio = Beneficio::firstOrCreate(
            ['codigo' => 'CADEIRA_RODAS'],
            ['nome' => 'Cadeira de Rodas', 'descricao' => 'Benefício fictício para o laboratório de elegibilidade.', 'estado' => 'ATIVO'],
        );

        $requisitos = [];
        foreach ([
            ['VULNERABILIDADE_SOCIAL', 'Vulnerabilidade social', 'BOOLEAN', 'BENEFICIARIO'],
            ['LIMITACAO_MOBILIDADE', 'Limitação de mobilidade', 'BOOLEAN', 'BENEFICIARIO'],
            ['RELATORIO_PROFISSIONAL', 'Relatório profissional', 'DOCUMENT_PRESENCE', 'COMPROVACAO'],
        ] as [$codigo, $rotulo, $tipo, $fonte]) {
            $requisitos[$codigo] = Requisito::firstOrCreate(
                ['codigo' => $codigo, 'versao_semantica' => 'v1'],
                ['rotulo' => $rotulo, 'tipo' => $tipo, 'fonte' => $fonte, 'estado' => 'ATIVO'],
            );
        }

        $versao = VersaoRegra::firstOrCreate(
            ['beneficio_id' => $beneficio->id, 'numero' => 1],
            ['estado' => 'RASCUNHO'],
        );
        if ($versao->estado->value === 'RASCUNHO' && ! $versao->nos()->exists()) {
            $raiz = $versao->nos()->create([
                'chave_no' => 'raiz', 'ordem' => 0, 'tipo_no' => 'GROUP', 'operador_grupo' => 'AND',
            ]);
            foreach ([
                ['vulnerabilidade', 1, 'VULNERABILIDADE_SOCIAL', 'EQ', ['valor' => true]],
                ['mobilidade', 2, 'LIMITACAO_MOBILIDADE', 'EQ', ['valor' => true]],
                ['relatorio', 3, 'RELATORIO_PROFISSIONAL', 'PRESENT', null],
            ] as [$chave, $ordem, $codigo, $operador, $esperado]) {
                $versao->nos()->create([
                    'parent_id' => $raiz->id,
                    'chave_no' => $chave,
                    'ordem' => $ordem,
                    'tipo_no' => 'CONDITION',
                    'requisito_id' => $requisitos[$codigo]->id,
                    'operador' => $operador,
                    'valor_esperado' => $esperado,
                ]);
            }
            app(ValidadorRegra::class)->validar($versao);
            $instante = CarbonImmutable::now('UTC');
            $versao->update([
                'estado' => 'PUBLICADA',
                'publicada_em' => $instante,
                'vigencia_inicio' => $instante,
            ]);
        }

        foreach ([
            ['LAB-000001', 'Pessoa Exemplo A', true, true, 'APRESENTADA'],
            ['LAB-000002', 'Pessoa Exemplo B', true, true, 'AUSENTE'],
            ['LAB-000003', 'Pessoa Exemplo C', false, true, 'AUSENTE'],
        ] as [$identificador, $nome, $vulnerabilidade, $mobilidade, $estadoDocumento]) {
            $beneficiario = Beneficiario::firstOrCreate(['identificador' => $identificador], [
                'nome' => $nome,
                'data_nascimento' => '1990-01-01',
                'municipio' => 'Cidade Laboratório',
                'uf' => 'GO',
                'renda_familiar_centavos' => 100000,
                'integrantes_familia' => 2,
                'vulnerabilidade_social' => $vulnerabilidade,
                'deficiencia' => true,
                'limitacao_mobilidade' => $mobilidade,
                'gestante' => false,
                'nascimento_bebe_ocorrido' => false,
                'estado' => 'ATIVO',
            ]);
            Comprovacao::firstOrCreate(
                ['beneficiario_id' => $beneficiario->id, 'tipo' => 'RELATORIO_PROFISSIONAL'],
                [
                    'estado' => $estadoDocumento,
                    'data_apresentacao' => $estadoDocumento === 'APRESENTADA'
                        ? CarbonImmutable::now('America/Sao_Paulo')->subDays(10)->toDateString() : null,
                ],
            );
        }
    }
}
