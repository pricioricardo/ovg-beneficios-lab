<?php

namespace App\Models;

use App\Domain\Enums\TipoRequisito;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Requisito extends Model
{
    protected $table = 'requisitos';

    protected $fillable = ['codigo', 'versao_semantica', 'rotulo', 'tipo', 'fonte', 'estado'];

    protected function casts(): array
    {
        return ['tipo' => TipoRequisito::class];
    }

    public function regras(): HasMany
    {
        return $this->hasMany(RegraElegibilidade::class);
    }
}
