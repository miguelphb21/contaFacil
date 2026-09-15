<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fornecedor extends Model
{
    protected $fillable = [
        'empresa_id',
        'nome',
        'cpf_cnpj',
        'telefone',
        'email',
        'endereco',
    ];
    // pertence a uma empresa
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }
    //pertence a varias empresas
    public function contas(): HasMany
    {
        return $this->hasMany(Conta::class);
    }
}
