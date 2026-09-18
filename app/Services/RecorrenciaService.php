<?php

namespace App\Services;

use App\Enums\LancamentoStatus;
use App\Models\Lancamento;
use App\Models\Recorrencia;
use App\Models\RecorrenciaExclusao;
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

    /**
     * Exclui todas as ocorrências pendentes de uma recorrência e a desativa.
     * Registra cada mês como excluído para impedir regeneração futura.
     * Lançamentos pagos/cancelados são preservados como histórico.
     */
    public function excluirPendentes(Recorrencia $recorrencia): int
    {
        $pendentes = $recorrencia->lancamentos()
            ->where('status', LancamentoStatus::Pendente)
            ->get(['id', 'data']);

        foreach ($pendentes as $lancamento) {
            $this->registrarMesExcluido($recorrencia, CarbonImmutable::parse($lancamento->data));
        }

        $recorrencia->update([
            'ativa' => false,
            'data_fim' => CarbonImmutable::now()->toDateString(),
        ]);

        return $recorrencia->lancamentos()
            ->where('status', LancamentoStatus::Pendente)
            ->delete();
    }

    /**
     * Registra o mês de uma ocorrência excluída para que a série não volte a
     * gerar a movimentação nesse período em nenhuma regeneração futura.
     */
    public function registrarMesExcluido(Recorrencia $recorrencia, CarbonImmutable $data): void
    {
        RecorrenciaExclusao::query()->updateOrCreate([
            'recorrencia_id' => $recorrencia->getKey(),
            'mes' => $data->startOfMonth()->toDateString(),
        ]);
    }

    /**
     * Transforma um lançamento existente em uma série recorrente, vinculando-o
     * como a primeira ocorrência e gerando as demais.
     */
    public function vincular(Lancamento $lancamento, ?string $dataFim): Recorrencia
    {
        $inicio = CarbonImmutable::parse($lancamento->data);

        $recorrencia = Recorrencia::query()->create([
            'empresa_id' => $lancamento->empresa_id,
            'tipo' => $lancamento->tipo,
            'descricao' => $lancamento->descricao,
            'valor' => $lancamento->valor,
            'dia' => (int) $inicio->day,
            'data_inicio' => $inicio->toDateString(),
            'data_fim' => $dataFim,
            'forma_pagamento' => $lancamento->forma_pagamento,
            'ativa' => true,
            'categoria_id' => $lancamento->categoria_id,
            'contraparte_id' => $lancamento->contraparte_id,
        ]);

        $lancamento->update(['recorrencia_id' => $recorrencia->getKey()]);

        $this->gerar($recorrencia, $this->horizonteDaRecorrencia($recorrencia));

        return $recorrencia;
    }

    /**
     * Atualiza a série a partir de um lançamento editado e regenera as
     * ocorrências pendentes, preservando as pagas/canceladas.
     */
    public function sincronizar(
        Recorrencia $recorrencia,
        Lancamento $lancamento,
        ?string $dataFim
    ): void {
        $atributos = [
            'descricao' => $lancamento->descricao,
            'valor' => $lancamento->valor,
            'forma_pagamento' => $lancamento->forma_pagamento,
            'categoria_id' => $lancamento->categoria_id,
            'contraparte_id' => $lancamento->contraparte_id,
            'data_fim' => $dataFim,
            'ativa' => true,
        ];

        $dataLancamento = CarbonImmutable::parse($lancamento->data);

        if ($dataLancamento->isSameMonth(CarbonImmutable::parse($recorrencia->data_inicio))) {
            $atributos['dia'] = (int) $dataLancamento->day;
            $atributos['data_inicio'] = $dataLancamento->toDateString();
        }

        $recorrencia->update($atributos);

        $this->regenerarPendentes($recorrencia);
    }

    /**
     * Encerra a série a partir da data informada, mantendo o histórico gerado.
     * As ocorrências pendentes posteriores à data final são removidas. A data
     * final nunca ultrapassa hoje, garantindo que nenhuma pendência futura
     * sobreviva mesmo quando a ação é disparada numa ocorrência futura.
     */
    public function encerrar(Recorrencia $recorrencia, ?CarbonImmutable $fim = null): void
    {
        $referencia = $fim ?? CarbonImmutable::now();
        $hoje = CarbonImmutable::now();

        $dataFim = ($referencia->lessThan($hoje) ? $referencia : $hoje)->toDateString();

        $recorrencia->update([
            'ativa' => false,
            'data_fim' => $dataFim,
        ]);

        $recorrencia->lancamentos()
            ->where('status', LancamentoStatus::Pendente)
            ->whereDate('data', '>', $dataFim)
            ->delete();
    }

    private function gerarNoIntervalo(
        Recorrencia $recorrencia,
        CarbonImmutable $inicio,
        CarbonImmutable $fim
    ): int {
        $gerados = 0;
        $percurso = $inicio->startOfMonth();

        while ($percurso->lte($fim)) {
            if ($this->dentroDoIntervalo($recorrencia, $percurso)
                && ! $this->mesExcluido($recorrencia, $percurso)
                && ! $this->jaExiste($recorrencia, $percurso)) {
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

    private function mesExcluido(Recorrencia $recorrencia, CarbonImmutable $mes): bool
    {
        return RecorrenciaExclusao::query()
            ->where('recorrencia_id', $recorrencia->getKey())
            ->where('mes', $mes->startOfMonth()->toDateString())
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
