<?php

namespace App\Policies;

use App\Models\Contraparte;
use App\Models\User;
use App\Policies\Concerns\PertenceAEmpresa;

class ContrapartePolicy
{
    use PertenceAEmpresa;

    public function update(User $user, Contraparte $contraparte): bool
    {
        return $this->pertenceA($user, $contraparte->empresa_id);
    }

    public function delete(User $user, Contraparte $contraparte): bool
    {
        return $this->pertenceA($user, $contraparte->empresa_id);
    }
}
