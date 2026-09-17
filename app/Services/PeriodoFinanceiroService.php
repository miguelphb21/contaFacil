<?php

namespace App\Services;

use Carbon\CarbonImmutable;

class PeriodoFinanceiroService
{
    /** Mês por número no formato aplicável. */
    private const MESES = [
        1 => 'Janeiro',
        2 => 'Fevereiro',
        3 => 'Março',
        4 => 'Abril',
        5 => 'Maio',
        6 => 'Junho',
        7 => 'Julho',
        8 => 'Agosto',
        9 => 'Setembro',
        10 => 'Outubro',
        11 => 'Novembro',
        12 => 'Dezembro',
    ];

    /**
     * Resolve o período "Y-m" vindo da request, persistindo na sessão.
     */
    public function resolver(?string $periodo): string
    {
        if ($periodo !== null && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $periodo) === 1) {
            session(['periodo_ativo' => $periodo]);

            return $periodo;
        }

        if ($periodo === null && session()->has('periodo_ativo')) {
            return session('periodo_ativo');
        }

        $atual = now()->format('Y-m');
        session(['periodo_ativo' => $atual]);

        return $atual;
    }

    /**
     * Metadados do período para exibição e navegação mês a mês.
     *
     * @return array{chave: string, ano: int, mes: int, label: string, anterior: string, proximo: string}
     */
    public function dados(string $periodo): array
    {
        $mes = CarbonImmutable::createFromFormat('Y-m', $periodo);

        return [
            'chave' => $periodo,
            'ano' => (int) $mes->format('Y'),
            'mes' => (int) $mes->format('n'),
            'label' => self::MESES[(int) $mes->format('n')].' de '.$mes->format('Y'),
            'anterior' => $mes->subMonth()->format('Y-m'),
            'proximo' => $mes->addMonth()->format('Y-m'),
        ];
    }
}
