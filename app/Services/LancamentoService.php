<?php

namespace App\Services;

use App\Enums\LancamentoStatus;
use App\Enums\LancamentoTipo;
use App\Models\Lancamento;
use Carbon\CarbonImmutable;

class LancamentoService
{
    /**
     * Totais brutos de receitas, despesas e saldo de um período "Y-m",
     * incluindo a quantidade de lançamentos por tipo.
     *
     * @return array{total_receitas: float, total_despesas: float, saldo: float, pagas: float, pendentes: float, qtd_receitas: int, qtd_despesas: int}
     */
    public function totais(string $periodo): array
    {
        $mes = CarbonImmutable::createFromFormat('Y-m', $periodo);

        $lancamentos = Lancamento::query()
            ->whereBetween('data', [$mes->startOfMonth(), $mes->endOfMonth()])
            ->where('status', '!=', LancamentoStatus::Cancelado)
            ->get(['tipo', 'valor', 'status']);

        $totalReceitas = $lancamentos
            ->where('tipo', LancamentoTipo::Receita)
            ->sum('valor');

        $totalDespesas = $lancamentos
            ->where('tipo', LancamentoTipo::Despesa)
            ->sum('valor');

        $pagas = $lancamentos
            ->where('status', LancamentoStatus::Pago)
            ->sum('valor');

        $pendentes = $lancamentos
            ->where('status', LancamentoStatus::Pendente)
            ->sum('valor');

        return [
            'total_receitas' => (float) $totalReceitas,
            'total_despesas' => (float) $totalDespesas,
            'saldo' => (float) $totalReceitas - $totalDespesas,
            'pagas' => (float) $pagas,
            'pendentes' => (float) $pendentes,
            'qtd_receitas' => $lancamentos->where('tipo', LancamentoTipo::Receita)->count(),
            'qtd_despesas' => $lancamentos->where('tipo', LancamentoTipo::Despesa)->count(),
        ];
    }

    /**
     * Totais formatados de receitas, despesas e saldo de um período "Y-m".
     *
     * @return array{total_receitas: string, total_despesas: string, saldo: string, pagas: string, pendentes: string, qtd_receitas: int, qtd_despesas: int}
     */
    public function resumo(string $periodo): array
    {
        $totais = $this->totais($periodo);

        foreach (['total_receitas', 'total_despesas', 'saldo', 'pagas', 'pendentes'] as $campo) {
            $totais[$campo] = $this->formatar($totais[$campo]);
        }

        return $totais;
    }

    public function marcarPago(Lancamento $lancamento, ?string $formaPagamento = null): void
    {
        $lancamento->update([
            'status' => LancamentoStatus::Pago,
            'forma_pagamento' => $formaPagamento,
        ]);
    }

    public function marcarStatus(Lancamento $lancamento, LancamentoStatus $status): void
    {
        $lancamento->update(['status' => $status]);
    }

    private function formatar(float $valor): string
    {
        return number_format($valor, 2, ',', '.');
    }
}
