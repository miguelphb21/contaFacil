<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecorrenciaExclusao extends Model
{
    protected $table = 'recorrencia_exclusoes';

    protected $fillable = [
        'recorrencia_id',
        'mes',
    ];

    /**
     * @return BelongsTo<Recorrencia, $this>
     */
    public function recorrencia(): BelongsTo
    {
        return $this->belongsTo(Recorrencia::class);
    }
}
