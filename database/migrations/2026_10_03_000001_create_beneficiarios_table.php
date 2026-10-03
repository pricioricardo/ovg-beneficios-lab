<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beneficiarios', function (Blueprint $table): void {
            $table->id();
            $table->string('identificador', 32)->unique();
            $table->string('nome');
            $table->date('data_nascimento');
            $table->string('municipio', 120);
            $table->char('uf', 2);
            $table->unsignedBigInteger('renda_familiar_centavos');
            $table->unsignedSmallInteger('integrantes_familia');
            $table->boolean('vulnerabilidade_social');
            $table->boolean('deficiencia');
            $table->boolean('limitacao_mobilidade');
            $table->boolean('gestante');
            $table->boolean('nascimento_bebe_ocorrido')->nullable();
            $table->date('data_provavel_parto')->nullable();
            $table->date('data_nascimento_bebe')->nullable();
            $table->boolean('autonomia_funcional')->nullable();
            $table->string('estado', 8)->default('ATIVO');
            $table->timestamps();
        });

        DB::statement("ALTER TABLE beneficiarios ADD CONSTRAINT chk_beneficiarios_identificador CHECK (identificador REGEXP '^LAB-[A-Z0-9-]+$')");
        DB::statement('ALTER TABLE beneficiarios ADD CONSTRAINT chk_beneficiarios_integrantes CHECK (integrantes_familia > 0)');
        DB::statement("ALTER TABLE beneficiarios ADD CONSTRAINT chk_beneficiarios_estado CHECK (estado IN ('ATIVO', 'INATIVO'))");
        DB::statement("ALTER TABLE beneficiarios ADD CONSTRAINT chk_beneficiarios_episodio CHECK ((gestante = 1 AND nascimento_bebe_ocorrido = 0 AND data_provavel_parto IS NOT NULL AND data_nascimento_bebe IS NULL) OR (gestante = 0 AND data_provavel_parto IS NULL AND ((nascimento_bebe_ocorrido = 1 AND data_nascimento_bebe IS NOT NULL) OR (nascimento_bebe_ocorrido = 0 AND data_nascimento_bebe IS NULL) OR (nascimento_bebe_ocorrido IS NULL AND data_nascimento_bebe IS NULL))))");
    }

    public function down(): void
    {
        Schema::dropIfExists('beneficiarios');
    }
};
