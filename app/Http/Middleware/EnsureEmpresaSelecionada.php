<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmpresaSelecionada
{
    /**
     * Garante que há uma empresa ativa válida, redirecionando para a tela de
     * seleção quando o usuário ainda não escolheu (ou não pertence a) nenhuma.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        $empresaId = $request->session()->get('empresa_ativa_id');

        if ($empresaId !== null && $user->empresas()->where('empresas.id', $empresaId)->exists()) {
            return $next($request);
        }

        if ($user->empresas()->count() === 1) {
            $request->session()->put('empresa_ativa_id', $user->empresas()->value('empresas.id'));

            return $next($request);
        }

        return to_route('empresas.selecionar');
    }
}
