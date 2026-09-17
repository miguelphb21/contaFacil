<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetEmpresaAtiva
{
    /**
     * Define a empresa ativa na sessão a partir de ?empresa_id, da sessão
     * existente ou da única empresa do usuário (fallback).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        $queryEmpresaId = $request->query('empresa_id');

        if ($request->isMethod('GET') && $queryEmpresaId !== null) {
            if ($user->empresas()->where('empresas.id', $queryEmpresaId)->exists()) {
                $request->session()->put('empresa_ativa_id', (int) $queryEmpresaId);
            }

            return $next($request);
        }

        $empresaId = $request->session()->get('empresa_ativa_id');

        if ($empresaId !== null) {
            if ($user->empresas()->where('empresas.id', $empresaId)->exists()) {
                return $next($request);
            }

            $request->session()->forget('empresa_ativa_id');
        }

        if ($user->empresas()->count() === 1) {
            $request->session()->put('empresa_ativa_id', $user->empresas()->value('empresas.id'));
        }

        return $next($request);
    }
}
