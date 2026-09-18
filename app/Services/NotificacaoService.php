<?php

namespace App\Services;

use App\Enums\LancamentoStatus;
use App\Models\Lancamento;
use App\Models\Notificacao;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;

class NotificacaoService
{
    public const int JANELA_DIAS = 7;

    /**
     * Garante uma notificação por lançamento pendente que vence em até `$dias`
     * dias (ou que já está vencido), sem duplicar. Notificações cujo lançamento
     * deixou de ser pendente são removidas.
     *
     * @return Collection<int, Notificacao>
     */
    public function sincronizar(int $dias = self::JANELA_DIAS): Collection
    {
        $empresaId = (int) session('empresa_ativa_id');

        if ($empresaId === 0) {
            return new Collection;
        }

        $ate = CarbonImmutable::now()->addDays($dias)->endOfDay();

        $lancamentos = Lancamento::query()
            ->where('status', LancamentoStatus::Pendente)
            ->whereDate('data', '<=', $ate->toDateString())
            ->get(['id', 'tipo', 'descricao', 'valor', 'data']);

        $idsValidos = $lancamentos->pluck('id');

        Notificacao::query()
            ->when(
                $idsValidos->isEmpty(),
                fn ($query) => $query->whereNotNull('lancamento_id'),
                fn ($query) => $query->whereNotIn('lancamento_id', $idsValidos),
            )
            ->delete();

        $existentes = Notificacao::query()
            ->pluck('id', 'lancamento_id')
            ->filter(fn ($id, $lancamentoId) => $lancamentoId !== null && $lancamentoId !== '');

        $novas = $lancamentos
            ->reject(fn (Lancamento $lancamento) => $existentes->has($lancamento->id))
            ->map(fn (Lancamento $lancamento) => Notificacao::query()->create([
                'empresa_id' => $empresaId,
                'lancamento_id' => $lancamento->id,
                'mensagem' => $this->mensagem($lancamento),
            ]))
            ->values();

        return new Collection($novas);
    }

    /**
     * Marca todas as notificações não lidas da empresa ativa como lidas.
     */
    public function marcarTodasLidas(): int
    {
        return Notificacao::query()
            ->naoLidas()
            ->update(['lido_at' => CarbonImmutable::now()]);
    }

    /**
     * Retorna as notificações mais recentes, com as não lidas primeiro.
     *
     * @return Collection<int, Notificacao>
     */
    public function ultimas(int $limite = 20): Collection
    {
        return Notificacao::query()
            ->with('lancamento')
            ->orderByRaw('lido_at IS NULL DESC')
            ->orderByDesc('created_at')
            ->limit($limite)
            ->get();
    }

    private function mensagem(Lancamento $lancamento): string
    {
        $hoje = CarbonImmutable::now()->startOfDay();
        $data = CarbonImmutable::parse($lancamento->data)->startOfDay();
        $dias = (int) $hoje->diffInDays($data, false);
        $valor = number_format((float) $lancamento->valor, 2, ',', '.');

        $prazo = match (true) {
            $dias < 0 => 'venceu há '.abs($dias).($dias === -1 ? ' dia' : ' dias'),
            $dias === 0 => 'vence hoje',
            default => 'vence em '.$dias.' dia'.($dias === 1 ? '' : 's'),
        };

        return "{$lancamento->tipo->label()} {$prazo}: {$lancamento->descricao} ({$valor})";
    }
}
