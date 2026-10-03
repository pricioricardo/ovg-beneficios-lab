<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Beneficiario extends Model
{
    protected $table = 'beneficiarios';

    protected $fillable = [
        'identificador', 'nome', 'data_nascimento', 'municipio', 'uf',
        'renda_familiar_centavos', 'integrantes_familia', 'vulnerabilidade_social',
        'deficiencia', 'limitacao_mobilidade', 'gestante', 'nascimento_bebe_ocorrido',
        'data_provavel_parto', 'data_nascimento_bebe', 'autonomia_funcional', 'estado',
    ];

    protected function casts(): array
    {
        return [
            'data_nascimento' => 'immutable_date',
            'data_provavel_parto' => 'immutable_date',
            'data_nascimento_bebe' => 'immutable_date',
            'renda_familiar_centavos' => 'integer',
            'integrantes_familia' => 'integer',
            'vulnerabilidade_social' => 'boolean',
            'deficiencia' => 'boolean',
            'limitacao_mobilidade' => 'boolean',
            'gestante' => 'boolean',
            'nascimento_bebe_ocorrido' => 'boolean',
            'autonomia_funcional' => 'boolean',
        ];
    }

    public function comprovacoes(): HasMany
    {
        return $this->hasMany(Comprovacao::class);
    }

    public function avaliacoes(): HasMany
    {
        return $this->hasMany(Avaliacao::class);
    }
}
