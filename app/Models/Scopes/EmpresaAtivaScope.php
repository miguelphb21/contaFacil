<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * @implements Scope<Model>
 */
class EmpresaAtivaScope implements Scope
{
    /**
     * Filtra os registros pela empresa ativa na sessão.
     *
     * Apenas aplica o filtro quando há uma empresa ativa definida, para que
     * comandos, filas e testes sem sessão continuem enxergando todos os dados.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $empresaId = session('empresa_ativa_id');

        if ($empresaId !== null) {
            $builder->where($model->getTable().'.empresa_id', $empresaId);
        }
    }
}
