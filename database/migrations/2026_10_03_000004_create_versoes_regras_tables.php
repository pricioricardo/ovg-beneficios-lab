<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('versoes_regra', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('beneficio_id')->constrained('beneficios')->restrictOnDelete();
            $table->unsignedInteger('numero');
            $table->string('estado', 12)->default('RASCUNHO');
            $table->dateTime('publicada_em')->nullable();
            $table->dateTime('vigencia_inicio')->nullable();
            $table->dateTime('vigencia_fim')->nullable();
            $table->timestamps();
            $table->unique(['beneficio_id', 'numero']);
            $table->unique(['beneficio_id', 'id']);
            $table->index(['beneficio_id', 'estado', 'vigencia_inicio', 'vigencia_fim'], 'idx_versao_vigencia');
        });
        DB::statement("ALTER TABLE versoes_regra ADD CONSTRAINT chk_versoes_estado CHECK (estado IN ('RASCUNHO', 'PUBLICADA', 'SUBSTITUIDA', 'INATIVA'))");
        DB::statement('ALTER TABLE versoes_regra ADD CONSTRAINT chk_versoes_periodo CHECK (vigencia_fim IS NULL OR vigencia_fim >= vigencia_inicio)');
        DB::statement("ALTER TABLE versoes_regra ADD CONSTRAINT chk_versoes_publicacao CHECK ((estado = 'RASCUNHO' AND publicada_em IS NULL AND vigencia_inicio IS NULL) OR (estado <> 'RASCUNHO' AND publicada_em IS NOT NULL AND vigencia_inicio IS NOT NULL))");

        Schema::create('regras_elegibilidade', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('versao_regra_id')->constrained('versoes_regra')->restrictOnDelete();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('chave_no', 40);
            $table->unsignedSmallInteger('ordem');
            $table->string('tipo_no', 12);
            $table->string('operador_grupo', 3)->nullable();
            $table->foreignId('requisito_id')->nullable()->constrained('requisitos')->restrictOnDelete();
            $table->string('operador', 16)->nullable();
            $table->json('valor_esperado')->nullable();
            $table->timestamps();
            $table->unique(['versao_regra_id', 'chave_no']);
            $table->unique(['versao_regra_id', 'id']);
            $table->unique(['versao_regra_id', 'parent_id', 'ordem'], 'uq_regra_irmaos_ordem');
            $table->foreign(['versao_regra_id', 'parent_id'], 'fk_regra_pai_mesma_versao')
                ->references(['versao_regra_id', 'id'])->on('regras_elegibilidade')->restrictOnDelete();
        });
        DB::statement("ALTER TABLE regras_elegibilidade ADD CONSTRAINT chk_regras_forma CHECK ((tipo_no = 'GROUP' AND operador_grupo IN ('AND', 'OR') AND requisito_id IS NULL AND operador IS NULL AND valor_esperado IS NULL) OR (tipo_no = 'CONDITION' AND operador_grupo IS NULL AND requisito_id IS NOT NULL AND operador IN ('EQ', 'NEQ', 'PRESENT', 'NOT_PRESENT')))");
    }

    public function down(): void
    {
        Schema::dropIfExists('regras_elegibilidade');
        Schema::dropIfExists('versoes_regra');
    }
};
