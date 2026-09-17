<?php

namespace App\Services;

use App\Enums\LancamentoStatus;
use App\Enums\LancamentoTipo;
use App\Models\Lancamento;
use Carbon\CarbonImmutable;

class LancamentoService
{
    /**
     * Totais de receitas, despesas e saldo de um período "Y-m".
     *
     * @return array{total_receitas: string, total_despesas: string, saldo: string, pagas: string, pendentes: string}
     */
    public function resumo(string $periodo): array
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
            'total_receitas' => $this->formatar($totalReceitas),
            'total_despesas' => $this->formatar($totalDespesas),
            'saldo' => $this->formatar($totalReceitas - $totalDespesas),
            'pagas' => $this->formatar($pagas),
            'pendentes' => $this->formatar($pendentes),
        ];
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
