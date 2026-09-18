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
use App\Services\RecorrenciaService;
use Carbon\CarbonImmutable;
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
        $filtroData = $periodos->resolverData($request->query('data'));

        $periodo = $filtroData !== null
            ? substr($filtroData, 0, 7)
            : $periodos->resolver($request->query('periodo'));

        $items = Lancamento::query()
            ->with([
                'categoria:id,nome',
                'contraparte:id,nome',
                'recorrencia:id,data_inicio,data_fim,ativa',
            ])
            ->where('tipo', $this->tipo())
            ->when(
                $filtroData !== null,
                fn ($query) => $query->whereDate('data', $filtroData),
                fn ($query) => $query->doPeriodo($periodo),
            )
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
            'filtroData' => $filtroData,
            'novo' => $request->query('novo') === '1',
        ]);
    }

    public function store(
        StoreLancamentoRequest $request,
        RecorrenciaService $recorrencias
    ): RedirectResponse {
        $dados = $request->validated();
        $recorrente = (bool) ($dados['recorrente'] ?? false);
        $dataFim = $dados['data_fim'] ?? null;

        unset($dados['recorrente'], $dados['data_fim']);

        $lancamento = Lancamento::query()->create($dados);

        if ($recorrente) {
            $recorrencias->vincular($lancamento, $dataFim);
        }

        return $this->redirecionarIndex($request, $this->tipo()->label().' registrada com sucesso.');
    }

    public function update(
        UpdateLancamentoRequest $request,
        Lancamento $lancamento,
        RecorrenciaService $recorrencias
    ): RedirectResponse {
        $this->authorize('update', $lancamento);

        abort_unless($lancamento->tipo === $this->tipo(), 404);

        $dados = $request->validated();
        $recorrente = (bool) ($dados['recorrente'] ?? false);
        $dataFim = $dados['data_fim'] ?? null;

        unset($dados['recorrente'], $dados['data_fim']);

        $lancamento->update($dados);

        if ($recorrente) {
            if ($lancamento->recorrencia !== null) {
                $recorrencias->sincronizar($lancamento->recorrencia, $lancamento, $dataFim);
            } else {
                $recorrencias->vincular($lancamento, $dataFim);
            }
        } elseif ($lancamento->recorrencia !== null) {
            $recorrencias->encerrar(
                $lancamento->recorrencia,
                CarbonImmutable::parse($lancamento->data)
            );
        }

        return $this->redirecionarIndex($request, 'Lançamento atualizado com sucesso.');
    }

    public function encerrarRecorrencia(
        Lancamento $lancamento,
        RecorrenciaService $recorrencias
    ): RedirectResponse {
        $this->authorize('update', $lancamento);

        abort_unless($lancamento->tipo === $this->tipo(), 404);

        $recorrencia = $lancamento->recorrencia;

        if ($recorrencia === null) {
            return back()->with('error', 'Este lançamento não é recorrente.');
        }

        $recorrencias->encerrar($recorrencia, CarbonImmutable::parse($lancamento->data));

        return back()->with('success', 'Recorrência encerrada com sucesso.');
    }

    public function destroy(
        Request $request,
        Lancamento $lancamento,
        RecorrenciaService $recorrencias
    ): RedirectResponse {
        $this->authorize('delete', $lancamento);

        abort_unless($lancamento->tipo === $this->tipo(), 404);

        $recorrencia = $lancamento->recorrencia;

        $lancamento->delete();

        if ($recorrencia !== null) {
            $recorrencias->registrarMesExcluido(
                $recorrencia,
                CarbonImmutable::parse($lancamento->data)
            );
        }

        return $this->redirecionarIndex($request, 'Lançamento excluído com sucesso.');
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

    /**
     * Redireciona para a listagem preservando o período visualizado.
     */
    private function redirecionarIndex(Request $request, string $mensagem): RedirectResponse
    {
        $periodo = $request->input('periodo');

        $parametros = is_string($periodo)
            && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $periodo) === 1
                ? ['periodo' => $periodo]
                : [];

        return redirect()
            ->route($this->rotaIndex(), $parametros)
            ->with('success', $mensagem);
    }
}
