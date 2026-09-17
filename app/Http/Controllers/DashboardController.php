<?php

namespace App\Http\Controllers;

use App\Models\Lancamento;
use App\Services\LancamentoService;
use App\Services\PeriodoFinanceiroService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request, LancamentoService $lancamentos, PeriodoFinanceiroService $periodos): Response
    {
        $periodo = $periodos->resolver($request->query('periodo'));

        $proximas = Lancamento::query()
            ->pendentes()
            ->where('data', '>=', now()->startOfDay())
            ->with(['categoria:id,nome', 'contraparte:id,nome'])
            ->orderBy('data')
            ->limit(8)
            ->get();

        return Inertia::render('Dashboard', [
            'resumo' => $lancamentos->resumo($periodo),
            'periodo' => $periodos->dados($periodo),
            'proximas' => $proximas,
        ]);
    }
}
