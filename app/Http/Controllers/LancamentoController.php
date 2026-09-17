<?php

namespace App\Http\Controllers;

use App\Enums\ContraparteTipo;
use App\Enums\LancamentoStatus;
use App\Enums\LancamentoTipo;
use App\Http\Requests\Lancamento\MarcarStatusLancamentoRequest;
use App\Http\Requests\Lancamento\StoreLancamentoRequest;
use App\Http\Requests\Lancamento\UpdateLancamentoRequest;
use App\Models\Categoria;
use App\Models\Contraparte;
use App\Models\Lancamento;
use App\Services\LancamentoService;
use App\Services\PeriodoFinanceiroService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

abstract class LancamentoController extends Controller
{
    abstract protected function tipo(): LancamentoTipo;

    abstract protected function contraparteTipo(): ContraparteTipo;

    abstract protected function pagina(): string;

    abstract protected function rotaIndex(): string;

    public function index(
        Request $request,
        PeriodoFinanceiroService $periodos,
        LancamentoService $lancamentos
    ): Response {
        $periodo = $periodos->resolver($request->query('periodo'));

        $items = Lancamento::query()
            ->with(['categoria:id,nome', 'contraparte:id,nome'])
            ->where('tipo', $this->tipo())
            ->doPeriodo($periodo)
            ->orderBy('data')
            ->orderByDesc('id')
            ->get();

        $categorias = Categoria::query()
            ->where('tipo', $this->tipo())
            ->orderBy('nome')
            ->get(['id', 'nome']);

        $contrapartes = Contraparte::query()
            ->where('tipo', $this->contraparteTipo())
            ->orderBy('nome')
            ->get(['id', 'nome']);

        return Inertia::render($this->pagina(), [
            'lancamentos' => $items,
            'categorias' => $categorias,
            'contrapartes' => $contrapartes,
            'resumo' => $lancamentos->resumo($periodo),
            'periodo' => $periodos->dados($periodo),
            'novo' => $request->query('novo') === '1',
        ]);
    }

    public function store(StoreLancamentoRequest $request): RedirectResponse
    {
        Lancamento::query()->create($request->validated());

        return redirect()
            ->route($this->rotaIndex())
            ->with('success', $this->tipo()->label().' registrada com sucesso.');
    }

    public function update(
        UpdateLancamentoRequest $request,
        Lancamento $lancamento
    ): RedirectResponse {
        $this->authorize('update', $lancamento);

        abort_unless($lancamento->tipo === $this->tipo(), 404);

        $lancamento->update($request->validated());

        return redirect()
            ->route($this->rotaIndex())
            ->with('success', 'Lançamento atualizado com sucesso.');
    }

    public function destroy(Lancamento $lancamento): RedirectResponse
    {
        $this->authorize('delete', $lancamento);

        abort_unless($lancamento->tipo === $this->tipo(), 404);

        $lancamento->delete();

        return redirect()
            ->route($this->rotaIndex())
            ->with('success', 'Lançamento excluído com sucesso.');
    }

    public function status(
        Lancamento $lancamento,
        MarcarStatusLancamentoRequest $request,
        LancamentoService $lancamentos
    ): RedirectResponse {
        $this->authorize('update', $lancamento);

        abort_unless($lancamento->tipo === $this->tipo(), 404);

        $status = LancamentoStatus::from($request->validated('status'));

        if ($status === LancamentoStatus::Pago) {
            $lancamentos->marcarPago($lancamento, $request->validated('forma_pagamento'));
        } else {
            $lancamentos->marcarStatus($lancamento, $status);
        }

        return back()->with('success', 'Status do lançamento atualizado.');
    }
}
