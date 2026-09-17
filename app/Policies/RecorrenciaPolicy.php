<?php

namespace App\Policies;

use App\Models\Recorrencia;
use App\Models\User;
use App\Policies\Concerns\PertenceAEmpresa;

class RecorrenciaPolicy
{
    use PertenceAEmpresa;

    public function update(User $user, Recorrencia $recorrencia): bool
    {
        return $this->pertenceA($user, $recorrencia->empresa_id);
    }

    public function delete(User $user, Recorrencia $recorrencia): bool
    {
        return $this->pertenceA($user, $recorrencia->empresa_id);
    }
}
