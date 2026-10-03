<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Beneficio extends Model
{
    protected $table = 'beneficios';

    protected $fillable = ['codigo', 'nome', 'descricao', 'estado'];

    public function versoesRegra(): HasMany
    {
        return $this->hasMany(VersaoRegra::class);
    }

    public function avaliacoes(): HasMany
    {
        return $this->hasMany(Avaliacao::class);
    }
}
