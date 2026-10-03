<?php

namespace App\Models;

use App\Domain\Enums\EstadoAvaliacao;
use App\Domain\Enums\ResultadoAutomatico;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Avaliacao extends Model
{
    protected $table = 'avaliacoes';

    protected $fillable = [
        'beneficiario_id', 'beneficio_id', 'versao_regra_id', 'instante_referencia',
        'estado', 'resultado_automatico', 'snapshot_schema_version', 'snapshot', 'concluida_em',
    ];

    protected function casts(): array
    {
        return [
            'instante_referencia' => 'immutable_datetime',
            'concluida_em' => 'immutable_datetime',
            'estado' => EstadoAvaliacao::class,
            'resultado_automatico' => ResultadoAutomatico::class,
            'snapshot' => 'array',
        ];
    }

    public function beneficiario(): BelongsTo
    {
        return $this->belongsTo(Beneficiario::class);
    }

    public function beneficio(): BelongsTo
    {
        return $this->belongsTo(Beneficio::class);
    }

    public function versaoRegra(): BelongsTo
    {
        return $this->belongsTo(VersaoRegra::class);
    }

    public function resultados(): HasMany
    {
        return $this->hasMany(ResultadoAvaliacao::class)->orderBy('ordem');
    }
}
