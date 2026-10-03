<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beneficios', function (Blueprint $table): void {
            $table->id();
            $table->string('codigo', 60)->unique();
            $table->string('nome');
            $table->text('descricao');
            $table->string('estado', 8)->default('ATIVO');
            $table->timestamps();
        });
        DB::statement("ALTER TABLE beneficios ADD CONSTRAINT chk_beneficios_estado CHECK (estado IN ('ATIVO', 'INATIVO'))");

        Schema::create('requisitos', function (Blueprint $table): void {
            $table->id();
            $table->string('codigo', 60);
            $table->string('versao_semantica', 16);
            $table->string('rotulo');
            $table->string('tipo', 24);
            $table->string('fonte', 20);
            $table->string('estado', 8)->default('ATIVO');
            $table->timestamps();
            $table->unique(['codigo', 'versao_semantica']);
        });
        DB::statement("ALTER TABLE requisitos ADD CONSTRAINT chk_requisitos_tipo CHECK (tipo IN ('BOOLEAN', 'INTEGER', 'DECIMAL', 'DATE', 'ENUM', 'DOCUMENT_PRESENCE'))");
        DB::statement("ALTER TABLE requisitos ADD CONSTRAINT chk_requisitos_fonte CHECK (fonte IN ('BENEFICIARIO', 'COMPROVACAO'))");
        DB::statement("ALTER TABLE requisitos ADD CONSTRAINT chk_requisitos_estado CHECK (estado IN ('ATIVO', 'INATIVO'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('requisitos');
        Schema::dropIfExists('beneficios');
    }
};
