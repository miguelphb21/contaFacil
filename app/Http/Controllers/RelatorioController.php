<?php

namespace App\Http\Controllers;

use App\Exports\RelatorioExcelExport;
use App\Services\RelatorioService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RelatorioController extends Controller
{
    public function index(Request $request, RelatorioService $relatorios): InertiaResponse
    {
        $contexto = $relatorios->contexto($request);

        $dados = $relatorios->dados($contexto['inicio'], $contexto['fim']);

        return Inertia::render('Relatorios/Index', [
            'dados' => $dados,
            'periodo' => [
                'tipo' => $contexto['tipo'],
                'label' => $contexto['label'],
                'inicio' => $contexto['inicio']->toDateString(),
                'fim' => $contexto['fim']?->toDateString(),
            ],
        ]);
    }

    public function pdf(Request $request, RelatorioService $relatorios): Response
    {
        $contexto = $relatorios->contexto($request);

        $pdf = Pdf::loadView('relatorios/relatorio', [
            'empresaNome' => $this->empresaAtivaNome(),
            'periodoLabel' => $contexto['label'],
            'dados' => $relatorios->dados($contexto['inicio'], $contexto['fim']),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('relatorio-financeiro.pdf');
    }

    public function excel(Request $request, RelatorioService $relatorios): BinaryFileResponse
    {
        $contexto = $relatorios->contexto($request);

        $exportacao = new RelatorioExcelExport(
            $this->empresaAtivaNome(),
            $contexto['label'],
            $relatorios->dados($contexto['inicio'], $contexto['fim']),
        );

        return Excel::download($exportacao, 'relatorio-financeiro.xlsx');
    }

    private function empresaAtivaNome(): string
    {
        $empresa = Auth::user()?->empresas()
            ->where('empresas.id', session('empresa_ativa_id'))
            ->first();

        return $empresa !== null
            ? ($empresa->nome_fantasia ?? $empresa->razao_social)
            : '—';
    }
}
