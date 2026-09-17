<?php

namespace App\Models;

use App\Enums\ContraparteTipo;
use App\Models\Scopes\EmpresaAtivaScope;
use Database\Factories\ContraparteFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contraparte extends Model
{
    /** @use HasFactory<ContraparteFactory> */
    use HasFactory;

    protected $fillable = [
        'empresa_id',
        'tipo',
        'nome',
        'cpf_cnpj',
        'telefone',
        'email',
        'endereco',
    ];

    protected $casts = [
        'tipo' => ContraparteTipo::class,
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
     * @return HasMany<Lancamento, $this>
     */
    public function lancamentos(): HasMany
    {
        return $this->hasMany(Lancamento::class);
    }

    /**
     * @param  Builder<Contraparte>  $query
     * @return Builder<Contraparte>
     */
    public function scopeFornecedores(Builder $query): Builder
    {
        return $query->where('tipo', ContraparteTipo::Fornecedor);
    }

    /**
     * @param  Builder<Contraparte>  $query
     * @return Builder<Contraparte>
     */
    public function scopeClientes(Builder $query): Builder
    {
        return $query->where('tipo', ContraparteTipo::Cliente);
    }
}
