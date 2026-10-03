<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comprovacao extends Model
{
    protected $table = 'comprovacoes';

    protected $fillable = ['beneficiario_id', 'tipo', 'estado', 'data_apresentacao', 'validade'];

    protected function casts(): array
    {
        return ['data_apresentacao' => 'immutable_date', 'validade' => 'immutable_date'];
    }

    public function beneficiario(): BelongsTo
    {
        return $this->belongsTo(Beneficiario::class);
    }
}
