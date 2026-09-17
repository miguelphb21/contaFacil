<?php

namespace App\Policies\Concerns;

use App\Models\User;

trait PertenceAEmpresa
{
    private function pertenceA(User $user, ?int $empresaId): bool
    {
        return $empresaId !== null
            && $user->empresas()->where('empresas.id', $empresaId)->exists();
    }
}
