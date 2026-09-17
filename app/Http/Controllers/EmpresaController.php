<?php

namespace App\Http\Controllers;

use App\Http\Requests\Empresa\StoreEmpresaRequest;
use App\Http\Requests\Empresa\UpdateEmpresaRequest;
use App\Models\Empresa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmpresaController extends Controller
{
    public function index(Request $request): Response
    {
        $empresas = $request->user()->empresas()
            ->orderBy('razao_social')
            ->get();

        return Inertia::render('Empresas/Index', [
            'empresas' => $empresas,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Empresas/Create');
    }

    public function store(StoreEmpresaRequest $request): RedirectResponse
    {
        $empresa = Empresa::query()->create($request->validated());

        $request->user()->empresas()->attach($empresa);

        $request->session()->put('empresa_ativa_id', $empresa->getKey());

        return redirect()
            ->route('dashboard')
            ->with('success', 'Empresa criada e selecionada com sucesso.');
    }

    public function selecionar(Request $request): Response
    {
        $empresas = $request->user()->empresas()
            ->orderBy('empresas.razao_social')
            ->get(['empresas.id', 'empresas.razao_social', 'empresas.nome_fantasia']);

        return Inertia::render('Empresas/Selecionar', [
            'empresas' => $empresas,
        ]);
    }

    public function armazenarSelecao(Request $request): RedirectResponse
    {
        $empresaId = (int) $request->input('empresa_id');

        $pertence = $request->user()->empresas()
            ->where('empresas.id', $empresaId)
            ->exists();

        if (! $pertence) {
            return back()->with('error', 'Não foi possível selecionar a empresa.');
        }

        $request->session()->put('empresa_ativa_id', $empresaId);

        return redirect()
            ->route('dashboard')
            ->with('success', 'Empresa selecionada com sucesso.');
    }

    public function update(
        UpdateEmpresaRequest $request,
        Empresa $empresa
    ): RedirectResponse {
        $this->authorize('update', $empresa);

        $empresa->update($request->validated());

        return redirect()
            ->route('empresas.index')
            ->with('success', 'Empresa atualizada com sucesso.');
    }

    public function destroy(Request $request, Empresa $empresa): RedirectResponse
    {
        $this->authorize('delete', $empresa);

        $eraAtiva = (int) $request->session()->get('empresa_ativa_id') === (int) $empresa->getKey();

        $empresa->delete();

        if ($eraAtiva) {
            $request->session()->forget('empresa_ativa_id');

            $proxima = $request->user()->empresas()->orderBy('razao_social')->first();

            if ($proxima !== null) {
                $request->session()->put('empresa_ativa_id', $proxima->getKey());
            }
        }

        return redirect()
            ->route('empresas.index')
            ->with('success', 'Empresa excluída com sucesso.');
    }
}
