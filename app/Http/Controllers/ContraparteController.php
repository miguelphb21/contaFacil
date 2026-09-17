<?php

namespace App\Http\Controllers;

use App\Enums\ContraparteTipo;
use App\Http\Requests\Contraparte\StoreContraparteRequest;
use App\Http\Requests\Contraparte\UpdateContraparteRequest;
use App\Models\Contraparte;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContraparteController extends Controller
{
    public function index(Request $request): Response
    {
        $filtro = $request->query('tipo');

        $contrapartes = Contraparte::query()
            ->when(in_array($filtro, [ContraparteTipo::Fornecedor->value, ContraparteTipo::Cliente->value], true), function ($query) use ($filtro) {
                $query->where('tipo', $filtro);
            })
            ->orderBy('tipo')
            ->orderBy('nome')
            ->get();

        return Inertia::render('Contrapartes/Index', [
            'contrapartes' => $contrapartes,
            'filtro' => $filtro,
        ]);
    }

    public function store(StoreContraparteRequest $request): RedirectResponse
    {
        Contraparte::query()->create($request->validated());

        return redirect()
            ->route('contrapartes.index')
            ->with('success', 'Contraparte cadastrada com sucesso.');
    }

    public function update(
        UpdateContraparteRequest $request,
        Contraparte $contraparte
    ): RedirectResponse {
        $this->authorize('update', $contraparte);

        $contraparte->update($request->validated());

        return redirect()
            ->route('contrapartes.index')
            ->with('success', 'Contraparte atualizada com sucesso.');
    }

    public function destroy(Contraparte $contraparte): RedirectResponse
    {
        $this->authorize('delete', $contraparte);

        $contraparte->delete();

        return redirect()
            ->route('contrapartes.index')
            ->with('success', 'Contraparte excluída com sucesso.');
    }
}
