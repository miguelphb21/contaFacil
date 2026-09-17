<?php

namespace App\Policies;

use App\Models\Empresa;
use App\Models\User;
use App\Policies\Concerns\PertenceAEmpresa;

class EmpresaPolicy
{
    use PertenceAEmpresa;

    public function view(User $user, Empresa $empresa): bool
    {
        return $this->pertenceA($user, $empresa->getKey());
    }

    public function update(User $user, Empresa $empresa): bool
    {
        return $this->pertenceA($user, $empresa->getKey());
    }

    public function delete(User $user, Empresa $empresa): bool
    {
        return $this->pertenceA($user, $empresa->getKey());
    }
}
