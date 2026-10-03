<?php

namespace App\Models;

use App\Domain\Enums\Desfecho;
use App\Domain\Enums\TipoNo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResultadoAvaliacao extends Model
{
    protected $table = 'resultados_avaliacao';

    protected $fillable = [
        'avaliacao_id', 'regra_elegibilidade_id', 'chave_no', 'caminho', 'ordem',
        'tipo_no', 'requisito_codigo', 'versao_semantica', 'requisito_rotulo',
        'tipo_requisito', 'operador', 'valor_observado', 'valor_esperado',
        'desfecho', 'explicacao',
    ];

    protected function casts(): array
    {
        return [
            'tipo_no' => TipoNo::class,
            'desfecho' => Desfecho::class,
            'valor_observado' => 'array',
            'valor_esperado' => 'array',
        ];
    }

    public function avaliacao(): BelongsTo
    {
        return $this->belongsTo(Avaliacao::class);
    }

    public function regraElegibilidade(): BelongsTo
    {
        return $this->belongsTo(RegraElegibilidade::class);
    }
}
