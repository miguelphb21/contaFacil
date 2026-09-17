<?php

namespace App\Policies;

use App\Models\Categoria;
use App\Models\User;
use App\Policies\Concerns\PertenceAEmpresa;

class CategoriaPolicy
{
    use PertenceAEmpresa;

    public function update(User $user, Categoria $categoria): bool
    {
        return $this->pertenceA($user, $categoria->empresa_id);
    }

    public function delete(User $user, Categoria $categoria): bool
    {
        return $this->pertenceA($user, $categoria->empresa_id);
    }
}
