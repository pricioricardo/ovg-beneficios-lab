<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comprovacoes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('beneficiario_id')->constrained('beneficiarios')->restrictOnDelete();
            $table->string('tipo', 40);
            $table->string('estado', 12);
            $table->date('data_apresentacao')->nullable();
            $table->date('validade')->nullable();
            $table->timestamps();
            $table->unique(['beneficiario_id', 'tipo']);
        });

        DB::statement("ALTER TABLE comprovacoes ADD CONSTRAINT chk_comprovacoes_tipo CHECK (tipo IN ('DOCUMENTO_IDENTIFICACAO', 'COMPROVANTE_ENDERECO', 'COMPROVANTE_RENDA', 'LAUDO_MEDICO', 'RELATORIO_PROFISSIONAL', 'CARTAO_GESTANTE', 'ULTRASSONOGRAFIA'))");
        DB::statement("ALTER TABLE comprovacoes ADD CONSTRAINT chk_comprovacoes_estado CHECK ((estado = 'AUSENTE' AND data_apresentacao IS NULL AND validade IS NULL) OR (estado = 'APRESENTADA' AND data_apresentacao IS NOT NULL AND (validade IS NULL OR validade >= data_apresentacao)))");
    }

    public function down(): void
    {
        Schema::dropIfExists('comprovacoes');
    }
};
