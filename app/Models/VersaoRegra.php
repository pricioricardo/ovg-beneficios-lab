<?php

namespace App\Models;

use App\Domain\Enums\EstadoVersao;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VersaoRegra extends Model
{
    protected $table = 'versoes_regra';

    protected $fillable = ['beneficio_id', 'numero', 'estado', 'publicada_em', 'vigencia_inicio', 'vigencia_fim'];

    protected function casts(): array
    {
        return [
            'estado' => EstadoVersao::class,
            'publicada_em' => 'immutable_datetime',
            'vigencia_inicio' => 'immutable_datetime',
            'vigencia_fim' => 'immutable_datetime',
        ];
    }

    public function beneficio(): BelongsTo
    {
        return $this->belongsTo(Beneficio::class);
    }

    public function nos(): HasMany
    {
        return $this->hasMany(RegraElegibilidade::class);
    }

    public function avaliacoes(): HasMany
    {
        return $this->hasMany(Avaliacao::class);
    }
}
