<?php

namespace App\Http\Controllers;

use App\Enums\ContraparteTipo;
use App\Enums\LancamentoTipo;

class ReceitaController extends LancamentoController
{
    protected function tipo(): LancamentoTipo
    {
        return LancamentoTipo::Receita;
    }

    protected function contraparteTipo(): ContraparteTipo
    {
        return ContraparteTipo::Cliente;
    }

    protected function pagina(): string
    {
        return 'Financeiro/Receitas';
    }

    protected function rotaIndex(): string
    {
        return 'receitas.index';
    }
}
