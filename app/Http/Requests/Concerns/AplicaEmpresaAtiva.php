<?php

namespace App\Http\Requests\Concerns;

trait AplicaEmpresaAtiva
{
    /**
     * Injeta a empresa ativa da sessão nos dados validados, impedindo que o
     * cliente envie o próprio empresa_id.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'empresa_id' => (int) session('empresa_ativa_id'),
        ]);
    }
}
