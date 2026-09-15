<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pagamento extends Model
{
    protected $fillable = [
        'parcela_id',
        'data_pagamento',
        'valor_pago',
        'forma_pagamento',
    ];

    protected $casts = [
        'data_pagamento' => 'date',
        'valor_pago' => 'decimal:2',
    ];

    public function parcela(): BelongsTo
    {
        return $this->belongsTo(Parcela::class);
    }
}
