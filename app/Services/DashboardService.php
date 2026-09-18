<?php

namespace App\Services;

use App\Enums\LancamentoStatus;
use App\Models\Lancamento;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class DashboardService
{
    public function __construct(
        private readonly LancamentoService $lancamentos,
    ) {}

    /**
     * Dados consolidados do Dashboard para o período informado, sempre da
     * empresa ativa na sessão (via escopo global).
     *
     * @return array{
     *     metrica: array{receitas: array{total: string, qtd: int}, despesas: array{total: string, qtd: int}, resultado: array{total: string}},
     *     comparativo: array{anterior_receitas: float, anterior_despesas: float, variacao_receitas: float|null, variacao_despesas: float|null, receitas_pct: float, despesas_pct: float},
     *     categorias: array<int, array{nome: string, valor: string, percentual: float, qtd: int}>,
     *     proximas: EloquentCollection<int, Lancamento>,
     *     vencidas: int,
     *     ultimas: EloquentCollection<int, Lancamento>,
     * }
     */
    public function dados(string $periodo): array
    {
        $mes = CarbonImmutable::createFromFormat('Y-m', $periodo);

        $totais = $this->lancamentos->totais($periodo);
        $anterior = $this->lancamentos->totais($mes->subMonth()->format('Y-m'));

        return [
            'metrica' => [
                'receitas' => [
                    'total' => $this->formatar($totais['total_receitas']),
                    'qtd' => $totais['qtd_receitas'],
                ],
                'despesas' => [
                    'total' => $this->formatar($totais['total_despesas']),
                    'qtd' => $totais['qtd_despesas'],
                ],
                'resultado' => [
                    'total' => $this->formatar($totais['saldo']),
                ],
            ],
            'comparativo' => $this->comparativo($totais, $anterior),
            'categorias' => $this->categoriasDespesas($mes),
            'proximas' => $this->proximas(),
            'vencidas' => $this->vencidas(),
            'ultimas' => $this->ultimas(),
        ];
    }

    /**
     * Comparação do período com o mês anterior e a proporção entre entradas
     * e saídas dentro do próprio período.
     *
     * @param  array{total_receitas: float, total_despesas: float}  $atual
     * @param  array{total_receitas: float, total_despesas: float}  $anterior
     * @return array{anterior_receitas: float, anterior_despesas: float, variacao_receitas: float|null, variacao_despesas: float|null, receitas_pct: float, despesas_pct: float}
     */
    private function comparativo(array $atual, array $anterior): array
    {
        $base = $atual['total_receitas'] + $atual['total_despesas'];

        return [
            'anterior_receitas' => $anterior['total_receitas'],
            'anterior_despesas' => $anterior['total_despesas'],
            'variacao_receitas' => $this->variacao($atual['total_receitas'], $anterior['total_receitas']),
            'variacao_despesas' => $this->variacao($atual['total_despesas'], $anterior['total_despesas']),
            'receitas_pct' => $base > 0 ? round(($atual['total_receitas'] / $base) * 100, 1) : 0.0,
            'despesas_pct' => $base > 0 ? round(($atual['total_despesas'] / $base) * 100, 1) : 0.0,
        ];
    }

    /**
     * Distribuição das despesas por categoria no período, limitada às cinco
     * maiores; categorias sem nome entram em "Sem categoria".
     *
     * @return array<int, array{nome: string, valor: string, percentual: float, qtd: int}>
     */
    private function categoriasDespesas(CarbonImmutable $mes): array
    {
        $lancamentos = Lancamento::query()
            ->despesas()
            ->where('status', '!=', LancamentoStatus::Cancelado)
            ->whereBetween('data', [$mes->startOfMonth(), $mes->endOfMonth()])
            ->with('categoria:id,nome')
            ->get(['categoria_id', 'valor']);

        $total = (float) $lancamentos->sum('valor');

        if ($total <= 0) {
            return [];
        }

        return $lancamentos
            ->mapToGroups(fn (Lancamento $lancamento) => [
                $lancamento->categoria?->nome ?? 'Sem categoria' => $lancamento->valor,
            ])
            ->map(fn ($valores) => [
                'total' => (float) $valores->sum(),
                'qtd' => $valores->count(),
            ])
            ->sortByDesc(fn (array $dados) => $dados['total'])
            ->take(5)
            ->map(fn (array $dados, string $nome) => [
                'nome' => $nome,
                'valor' => $this->formatar($dados['total']),
                'percentual' => round(($dados['total'] / $total) * 100, 1),
                'qtd' => $dados['qtd'],
            ])
            ->values()
            ->all();
    }

    /**
     * @return EloquentCollection<int, Lancamento>
     */
    private function proximas(): EloquentCollection
    {
        return Lancamento::query()
            ->pendentes()
            ->where('data', '>=', now()->startOfDay())
            ->with([
                'categoria:id,nome',
                'contraparte:id,nome',
                'recorrencia:id,data_inicio,data_fim,ativa',
            ])
            ->orderBy('data')
            ->orderByDesc('id')
            ->limit(6)
            ->get();
    }

    /**
     * Despesas pendentes com data anterior a hoje, que exigem atenção.
     */
    private function vencidas(): int
    {
        return Lancamento::query()
            ->despesas()
            ->pendentes()
            ->where('data', '<', now()->startOfDay())
            ->count();
    }

    /**
     * @return EloquentCollection<int, Lancamento>
     */
    private function ultimas(): EloquentCollection
    {
        return Lancamento::query()
            ->where('status', '!=', LancamentoStatus::Cancelado)
            ->with([
                'categoria:id,nome',
                'contraparte:id,nome',
                'recorrencia:id,data_inicio,data_fim,ativa',
            ])
            ->orderByDesc('data')
            ->orderByDesc('id')
            ->limit(5)
            ->get();
    }

    private function variacao(float $atual, float $anterior): ?float
    {
        if ($anterior == 0.0) {
            return null;
        }

        return round((($atual - $anterior) / abs($anterior)) * 100, 1);
    }

    private function formatar(float $valor): string
    {
        return number_format($valor, 2, ',', '.');
    }
}
