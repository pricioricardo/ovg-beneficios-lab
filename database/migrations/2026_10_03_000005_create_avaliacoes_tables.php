<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('avaliacoes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('beneficiario_id')->constrained('beneficiarios')->restrictOnDelete();
            $table->foreignId('beneficio_id')->constrained('beneficios')->restrictOnDelete();
            $table->unsignedBigInteger('versao_regra_id');
            $table->foreign(['beneficio_id', 'versao_regra_id'], 'fk_avaliacao_versao_beneficio')
                ->references(['beneficio_id', 'id'])->on('versoes_regra')->restrictOnDelete();
            $table->dateTime('instante_referencia');
            $table->string('estado', 16);
            $table->string('resultado_automatico', 32)->nullable();
            $table->unsignedSmallInteger('snapshot_schema_version')->nullable();
            $table->json('snapshot')->nullable();
            $table->dateTime('concluida_em')->nullable();
            $table->timestamps();
        });
        DB::statement("ALTER TABLE avaliacoes ADD CONSTRAINT chk_avaliacoes_estado CHECK (estado IN ('INICIADA', 'CONCLUIDA', 'FALHA_TECNICA'))");
        DB::statement("ALTER TABLE avaliacoes ADD CONSTRAINT chk_avaliacoes_conclusao CHECK ((estado = 'CONCLUIDA' AND resultado_automatico IN ('ELEGIVEL', 'INELEGIVEL', 'PENDENTE_DOCUMENTACAO', 'REQUER_ANALISE_HUMANA') AND snapshot IS NOT NULL AND snapshot_schema_version IS NOT NULL AND concluida_em IS NOT NULL) OR (estado <> 'CONCLUIDA' AND resultado_automatico IS NULL))");

        Schema::create('resultados_avaliacao', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('avaliacao_id')->constrained('avaliacoes')->restrictOnDelete();
            $table->foreignId('regra_elegibilidade_id')->constrained('regras_elegibilidade')->restrictOnDelete();
            $table->string('chave_no', 40);
            $table->string('caminho', 100);
            $table->unsignedSmallInteger('ordem');
            $table->string('tipo_no', 12);
            $table->string('requisito_codigo', 60)->nullable();
            $table->string('versao_semantica', 16)->nullable();
            $table->string('requisito_rotulo')->nullable();
            $table->string('tipo_requisito', 24)->nullable();
            $table->string('operador', 16)->nullable();
            $table->json('valor_observado')->nullable();
            $table->json('valor_esperado')->nullable();
            $table->string('desfecho', 24);
            $table->string('explicacao');
            $table->timestamps();
            $table->unique(['avaliacao_id', 'regra_elegibilidade_id'], 'uq_resultado_no');
            $table->unique(['avaliacao_id', 'ordem'], 'uq_resultado_ordem');
        });
        DB::statement("ALTER TABLE resultados_avaliacao ADD CONSTRAINT chk_resultados_desfecho CHECK (desfecho IN ('ATENDIDA', 'NAO_ATENDIDA', 'DOCUMENTACAO_PENDENTE', 'INDETERMINADA'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('resultados_avaliacao');
        Schema::dropIfExists('avaliacoes');
    }
};
