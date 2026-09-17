<?php

namespace App\Models;

use App\Enums\LancamentoTipo;
use App\Models\Scopes\EmpresaAtivaScope;
use Database\Factories\RecorrenciaFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Recorrencia extends Model
{
    /** @use HasFactory<RecorrenciaFactory> */
    use HasFactory;

    protected $fillable = [
        'empresa_id',
        'tipo',
        'descricao',
        'valor',
        'dia',
        'data_inicio',
        'data_fim',
        'forma_pagamento',
        'ativa',
        'categoria_id',
        'contraparte_id',
    ];

    protected $casts = [
        'tipo' => LancamentoTipo::class,
        'valor' => 'decimal:2',
        'dia' => 'integer',
        'data_inicio' => 'date',
        'data_fim' => 'date',
        'ativa' => 'boolean',
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
     * @return BelongsTo<Categoria, $this>
     */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    /**
     * @return BelongsTo<Contraparte, $this>
     */
    public function contraparte(): BelongsTo
    {
        return $this->belongsTo(Contraparte::class);
    }

    /**
     * @return HasMany<Lancamento, $this>
     */
    public function lancamentos(): HasMany
    {
        return $this->hasMany(Lancamento::class);
    }

    /**
     * @param  Builder<Recorrencia>  $query
     * @return Builder<Recorrencia>
     */
    public function scopeReceitas(Builder $query): Builder
    {
        return $query->where('tipo', LancamentoTipo::Receita);
    }

    /**
     * @param  Builder<Recorrencia>  $query
     * @return Builder<Recorrencia>
     */
    public function scopeDespesas(Builder $query): Builder
    {
        return $query->where('tipo', LancamentoTipo::Despesa);
    }

    /**
     * @param  Builder<Recorrencia>  $query
     * @return Builder<Recorrencia>
     */
    public function scopeAtivas(Builder $query): Builder
    {
        return $query->where('ativa', true);
    }
}
