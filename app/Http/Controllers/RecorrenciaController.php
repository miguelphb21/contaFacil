<?php

namespace App\Http\Controllers;

use App\Enums\LancamentoTipo;
use App\Http\Requests\Recorrencia\StoreRecorrenciaRequest;
use App\Http\Requests\Recorrencia\UpdateRecorrenciaRequest;
use App\Models\Categoria;
use App\Models\Contraparte;
use App\Models\Recorrencia;
use App\Services\RecorrenciaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RecorrenciaController extends Controller
{
    public function index(Request $request): Response
    {
        $filtro = $request->query('tipo');

        $recorrencias = Recorrencia::query()
            ->with(['categoria:id,nome', 'contraparte:id,nome'])
            ->when(in_array($filtro, [LancamentoTipo::Receita->value, LancamentoTipo::Despesa->value], true), function ($query) use ($filtro) {
                $query->where('tipo', $filtro);
            })
            ->orderBy('ativa', 'desc')
            ->orderBy('descricao')
            ->get();

        $categorias = Categoria::query()
            ->orderBy('tipo')
            ->orderBy('nome')
            ->get(['id', 'nome', 'tipo']);

        $contrapartes = Contraparte::query()
            ->orderBy('tipo')
            ->orderBy('nome')
            ->get(['id', 'nome', 'tipo']);

        return Inertia::render('Recorrencias/Index', [
            'recorrencias' => $recorrencias,
            'categorias' => $categorias,
            'contrapartes' => $contrapartes,
            'filtro' => $filtro,
        ]);
    }

    public function store(
        StoreRecorrenciaRequest $request,
        RecorrenciaService $recorrencias
    ): RedirectResponse {
        $recorrencia = Recorrencia::query()->create($request->validated());

        $recorrencias->gerar(
            $recorrencia,
            now()->addMonths(24)->endOfMonth()
        );

        return redirect()
            ->route('recorrencias.index')
            ->with('success', 'Recorrência criada com sucesso.');
    }

    public function update(
        UpdateRecorrenciaRequest $request,
        Recorrencia $recorrencia,
        RecorrenciaService $recorrencias
    ): RedirectResponse {
        $this->authorize('update', $recorrencia);

        $recorrencia->update($request->validated());

        $recorrencias->regenerarPendentes($recorrencia);

        return redirect()
            ->route('recorrencias.index')
            ->with('success', 'Recorrência atualizada com sucesso.');
    }

    public function destroy(Recorrencia $recorrencia): RedirectResponse
    {
        $this->authorize('delete', $recorrencia);

        $recorrencia->delete();

        return redirect()
            ->route('recorrencias.index')
            ->with('success', 'Recorrência excluída com sucesso.');
    }

    public function regenerar(
        Recorrencia $recorrencia,
        RecorrenciaService $recorrencias
    ): RedirectResponse {
        $this->authorize('update', $recorrencia);

        $gerados = $recorrencias->regenerarPendentes($recorrencia);

        return back()
            ->with('success', "{$gerados} lançamentos regenerados.");
    }
}
