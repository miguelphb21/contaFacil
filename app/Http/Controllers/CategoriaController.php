<?php

namespace App\Http\Controllers;

use App\Enums\LancamentoTipo;
use App\Http\Requests\Categoria\StoreCategoriaRequest;
use App\Http\Requests\Categoria\UpdateCategoriaRequest;
use App\Models\Categoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CategoriaController extends Controller
{
    public function index(Request $request): Response
    {
        $filtro = $request->query('tipo');

        $categorias = Categoria::query()
            ->when(in_array($filtro, [LancamentoTipo::Receita->value, LancamentoTipo::Despesa->value], true), function ($query) use ($filtro) {
                $query->where('tipo', $filtro);
            })
            ->orderBy('tipo')
            ->orderBy('nome')
            ->get();

        return Inertia::render('Categorias/Index', [
            'categorias' => $categorias,
            'filtro' => $filtro,
        ]);
    }

    public function store(StoreCategoriaRequest $request): RedirectResponse
    {
        Categoria::query()->create($request->validated());

        return redirect()
            ->route('categorias.index')
            ->with('success', 'Categoria cadastrada com sucesso.');
    }

    public function update(
        UpdateCategoriaRequest $request,
        Categoria $categoria
    ): RedirectResponse {
        $this->authorize('update', $categoria);

        $categoria->update($request->validated());

        return redirect()
            ->route('categorias.index')
            ->with('success', 'Categoria atualizada com sucesso.');
    }

    public function destroy(Categoria $categoria): RedirectResponse
    {
        $this->authorize('delete', $categoria);

        $categoria->delete();

        return redirect()
            ->route('categorias.index')
            ->with('success', 'Categoria excluída com sucesso.');
    }
}
