<?php

namespace App\Models;

use Database\Factories\EmpresaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Empresa extends Model
{
    /** @use HasFactory<EmpresaFactory> */
    use HasFactory;

    protected $fillable = [
        'razao_social',
        'nome_fantasia',
        'cnpj',
    ];

    /**
     * @return BelongsToMany<User, $this>
     */
    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * @return HasMany<Contraparte, $this>
     */
    public function contrapartes(): HasMany
    {
        return $this->hasMany(Contraparte::class);
    }

    /**
     * @return HasMany<Categoria, $this>
     */
    public function categorias(): HasMany
    {
        return $this->hasMany(Categoria::class);
    }

    /**
     * @return HasMany<Lancamento, $this>
     */
    public function lancamentos(): HasMany
    {
        return $this->hasMany(Lancamento::class);
    }

    /**
     * @return HasMany<Recorrencia, $this>
     */
    public function recorrencias(): HasMany
    {
        return $this->hasMany(Recorrencia::class);
    }
}
