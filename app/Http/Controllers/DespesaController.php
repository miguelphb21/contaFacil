<?php

namespace App\Http\Controllers;

use App\Enums\ContraparteTipo;
use App\Enums\LancamentoTipo;

class DespesaController extends LancamentoController
{
    protected function tipo(): LancamentoTipo
    {
        return LancamentoTipo::Despesa;
    }

    protected function contraparteTipo(): ContraparteTipo
    {
        return ContraparteTipo::Fornecedor;
    }

    protected function pagina(): string
    {
        return 'Financeiro/Despesas';
    }

    protected function rotaIndex(): string
    {
        return 'despesas.index';
    }
}
