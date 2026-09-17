<?php

namespace App\Policies;

use App\Models\Lancamento;
use App\Models\User;
use App\Policies\Concerns\PertenceAEmpresa;

class LancamentoPolicy
{
    use PertenceAEmpresa;

    public function update(User $user, Lancamento $lancamento): bool
    {
        return $this->pertenceA($user, $lancamento->empresa_id);
    }

    public function delete(User $user, Lancamento $lancamento): bool
    {
        return $this->pertenceA($user, $lancamento->empresa_id);
    }
}
