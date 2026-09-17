<?php

namespace App\Models;

use App\Enums\LancamentoStatus;
use App\Enums\LancamentoTipo;
use App\Models\Scopes\EmpresaAtivaScope;
use Carbon\CarbonImmutable;
use Database\Factories\LancamentoFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lancamento extends Model
{
    /** @use HasFactory<LancamentoFactory> */
    use HasFactory;

    protected $fillable = [
        'empresa_id',
        'tipo',
        'descricao',
        'valor',
        'data',
        'status',
        'forma_pagamento',
        'categoria_id',
        'contraparte_id',
        'recorrencia_id',
    ];

    protected $casts = [
        'tipo' => LancamentoTipo::class,
        'status' => LancamentoStatus::class,
        'valor' => 'decimal:2',
        'data' => 'date',
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
     * @return BelongsTo<Recorrencia, $this>
     */
    public function recorrencia(): BelongsTo
    {
        return $this->belongsTo(Recorrencia::class);
    }

    /**
     * @param  Builder<Lancamento>  $query
     * @return Builder<Lancamento>
     */
    public function scopeReceitas(Builder $query): Builder
    {
        return $query->where('tipo', LancamentoTipo::Receita);
    }

    /**
     * @param  Builder<Lancamento>  $query
     * @return Builder<Lancamento>
     */
    public function scopeDespesas(Builder $query): Builder
    {
        return $query->where('tipo', LancamentoTipo::Despesa);
    }

    /**
     * @param  Builder<Lancamento>  $query
     * @return Builder<Lancamento>
     */
    public function scopeDoPeriodo(Builder $query, string $periodo): Builder
    {
        $mes = CarbonImmutable::createFromFormat('Y-m', $periodo);

        return $query->whereBetween('data', [$mes->startOfMonth(), $mes->endOfMonth()]);
    }

    /**
     * @param  Builder<Lancamento>  $query
     * @return Builder<Lancamento>
     */
    public function scopePendentes(Builder $query): Builder
    {
        return $query->where('status', LancamentoStatus::Pendente);
    }
}
