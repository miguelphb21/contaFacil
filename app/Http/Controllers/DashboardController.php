<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use App\Services\PeriodoFinanceiroService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request, PeriodoFinanceiroService $periodos, DashboardService $dashboard): Response
    {
        $periodo = $periodos->resolver($request->query('periodo'));

        return Inertia::render('Dashboard', [
            'dashboard' => $dashboard->dados($periodo),
            'periodo' => $periodos->dados($periodo),
        ]);
    }
}
