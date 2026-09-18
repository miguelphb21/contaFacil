<?php

namespace App\Http\Controllers;

use App\Models\Notificacao;
use App\Services\NotificacaoService;
use Illuminate\Http\JsonResponse;

class NotificacaoController extends Controller
{
    public function index(NotificacaoService $notificacoes): JsonResponse
    {
        $novas = $notificacoes->sincronizar();

        return response()->json([
            'novas' => $novas->map(fn (Notificacao $notificacao) => $this->serializar($notificacao))->values(),
            'nao_lidas' => Notificacao::query()->naoLidas()->count(),
            'notificacoes' => $notificacoes->ultimas()
                ->map(fn (Notificacao $notificacao) => $this->serializar($notificacao))
                ->values(),
        ]);
    }

    public function marcarLidas(NotificacaoService $notificacoes): JsonResponse
    {
        $notificacoes->marcarTodasLidas();

        return response()->json(['ok' => true]);
    }

    /**
     * @return array<string, mixed>
     */
    private function serializar(Notificacao $notificacao): array
    {
        $lancamento = $notificacao->lancamento;

        return [
            'id' => $notificacao->getKey(),
            'mensagem' => $notificacao->mensagem,
            'lido' => $notificacao->lido_at !== null,
            'created_at' => $notificacao->created_at?->toISOString(),
            'lancamento' => $lancamento !== null ? [
                'id' => $lancamento->getKey(),
                'tipo' => $lancamento->tipo->value,
                'descricao' => $lancamento->descricao,
                'valor' => number_format((float) $lancamento->valor, 2, ',', '.'),
                'data' => $lancamento->data?->format('Y-m-d'),
            ] : null,
        ];
    }
}
