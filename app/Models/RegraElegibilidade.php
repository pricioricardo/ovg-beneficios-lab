<?php

namespace App\Models;

use App\Domain\Enums\Operador;
use App\Domain\Enums\TipoNo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RegraElegibilidade extends Model
{
    protected $table = 'regras_elegibilidade';

    protected $fillable = [
        'versao_regra_id', 'parent_id', 'chave_no', 'ordem', 'tipo_no',
        'operador_grupo', 'requisito_id', 'operador', 'valor_esperado',
    ];

    protected function casts(): array
    {
        return [
            'tipo_no' => TipoNo::class,
            'operador' => Operador::class,
            'valor_esperado' => 'array',
        ];
    }

    public function versaoRegra(): BelongsTo
    {
        return $this->belongsTo(VersaoRegra::class);
    }

    public function pai(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function filhos(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('ordem');
    }

    public function requisito(): BelongsTo
    {
        return $this->belongsTo(Requisito::class);
    }
}
