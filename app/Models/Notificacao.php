<?php

namespace App\Models;

use App\Models\Scopes\EmpresaAtivaScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notificacao extends Model
{
    protected $table = 'notificacoes';

    protected $fillable = [
        'empresa_id',
        'lancamento_id',
        'mensagem',
        'lido_at',
    ];

    protected $casts = [
        'lido_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new EmpresaAtivaScope);
    }

    /**
     * @return BelongsTo<Empresa, $this>
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    /**
     * @return BelongsTo<Lancamento, $this>
     */
    public function lancamento(): BelongsTo
    {
        return $this->belongsTo(Lancamento::class);
    }

    public function scopeNaoLidas(Builder $query): Builder
    {
        return $query->whereNull('lido_at');
    }
}
