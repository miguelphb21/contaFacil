<?php

namespace App\Services;

use App\Enums\LancamentoStatus;
use App\Models\Lancamento;
use App\Models\Recorrencia;
use Carbon\CarbonImmutable;

class RecorrenciaService
{
    /**
     * Gera os lançamentos pendentes de uma recorrência entre a data de início
     * e a data final informada, evitando duplicidade por mês.
     */
    public function gerar(Recorrencia $recorrencia, CarbonImmutable $fim): int
    {
        $inicio = CarbonImmutable::parse($recorrencia->data_inicio)->startOfMonth();

        return $this->gerarNoIntervalo($recorrencia, $inicio, $fim);
    }

    /**
     * Mantém o horizonte rolante: gera lançamentos pendentes até `$meses`
     * meses no futuro, avançando sempre a partir do último lançamento pendente
     * já existente (para não recriar meses excluídos manualmente).
     */
    public function manterHorizonte(int $meses = 24): int
    {
        $fim = CarbonImmutable::now()->addMonths($meses)->endOfMonth();
        $gerados = 0;

        Recorrencia::query()
            ->ativas()
            ->each(function (Recorrencia $recorrencia) use ($fim, &$gerados) {
                $ultimaGeracao = $recorrencia->lancamentos()
                    ->where('status', LancamentoStatus::Pendente)
                    ->max('data');

                $inicio = $ultimaGeracao !== null
                    ? CarbonImmutable::parse($ultimaGeracao)->addMonth()->startOfMonth()
                    : CarbonImmutable::now()->startOfMonth();

                $gerados += $this->gerarNoIntervalo($recorrencia, $inicio, $fim);
            });

        return $gerados;
    }

    /**
     * Apaga os lançamentos pendentes de uma recorrência e os regenera a partir
     * da data de início. Lançamentos pagos/cancelados são preservados.
     */
    public function regenerarPendentes(Recorrencia $recorrencia): int
    {
        $recorrencia->lancamentos()->pendentes()->delete();

        $fim = $this->horizonteDaRecorrencia($recorrencia);

        return $this->gerar($recorrencia, $fim);
    }

    private function gerarNoIntervalo(
        Recorrencia $recorrencia,
        CarbonImmutable $inicio,
        CarbonImmutable $fim
    ): int {
        $gerados = 0;
        $percurso = $inicio->startOfMonth();

        while ($percurso->lte($fim)) {
            if ($this->dentroDoIntervalo($recorrencia, $percurso) && ! $this->jaExiste($recorrencia, $percurso)) {
                Lancamento::query()->create([
                    'empresa_id' => $recorrencia->empresa_id,
                    'tipo' => $recorrencia->tipo,
                    'descricao' => $recorrencia->descricao,
                    'valor' => $recorrencia->valor,
                    'data' => $this->dataDoDia($recorrencia, $percurso)->format('Y-m-d'),
                    'status' => LancamentoStatus::Pendente,
                    'forma_pagamento' => $recorrencia->forma_pagamento,
                    'categoria_id' => $recorrencia->categoria_id,
                    'contraparte_id' => $recorrencia->contraparte_id,
                    'recorrencia_id' => $recorrencia->getKey(),
                ]);
                $gerados++;
            }

            $percurso = $percurso->addMonth()->startOfMonth();
        }

        return $gerados;
    }

    private function dentroDoIntervalo(Recorrencia $recorrencia, CarbonImmutable $mes): bool
    {
        if ($mes->lt(CarbonImmutable::parse($recorrencia->data_inicio)->startOfMonth())) {
            return false;
        }

        if ($recorrencia->data_fim !== null && $mes->gt(CarbonImmutable::parse($recorrencia->data_fim)->endOfMonth())) {
            return false;
        }

        return true;
    }

    private function jaExiste(Recorrencia $recorrencia, CarbonImmutable $mes): bool
    {
        return Lancamento::query()
            ->where('recorrencia_id', $recorrencia->getKey())
            ->whereBetween('data', [$mes->startOfMonth(), $mes->endOfMonth()])
            ->exists();
    }

    private function dataDoDia(Recorrencia $recorrencia, CarbonImmutable $mes): CarbonImmutable
    {
        return $mes->setDay(min($recorrencia->dia, $mes->daysInMonth));
    }

    private function horizonteDaRecorrencia(Recorrencia $recorrencia): CarbonImmutable
    {
        $fim = CarbonImmutable::now()->addMonths(24)->endOfMonth();

        return $recorrencia->data_fim !== null
            ? $fim->min(CarbonImmutable::parse($recorrencia->data_fim)->endOfMonth())
            : $fim;
    }
}
