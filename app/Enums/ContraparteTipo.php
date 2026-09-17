<?php

namespace App\Enums;

enum ContraparteTipo: string
{
    case Fornecedor = 'fornecedor';
    case Cliente = 'cliente';

    public function label(): string
    {
        return match ($this) {
            self::Fornecedor => 'Fornecedor',
            self::Cliente => 'Cliente',
        };
    }
}
