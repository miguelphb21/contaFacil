<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class RelatorioExcelExport implements FromView, ShouldAutoSize
{
    /**
     * @param  array<string, mixed>  $dados
     */
    public function __construct(
        private readonly string $empresaNome,
        private readonly string $periodoLabel,
        private readonly array $dados,
    ) {}

    public function view(): View
    {
        return view('relatorios/relatorio', [
            'empresaNome' => $this->empresaNome,
            'periodoLabel' => $this->periodoLabel,
            'dados' => $this->dados,
        ]);
    }
}
