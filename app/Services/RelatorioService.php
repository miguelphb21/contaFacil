<?php

namespace App\Services;

use App\Enums\LancamentoStatus;
use App\Enums\LancamentoTipo;
use App\Models\Lancamento;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class RelatorioService
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
     * Resolve o contexto do relatório a partir da request.
     *
     * Suporta: `periodo` (mês Y-m), `inicio`/`fim` (período customizado),
     * `ano` (Y) ou nada (todo o período disponível).
     *
     * @return array{tipo: string, inicio: CarbonImmutable, fim: CarbonImmutable|null, label: string}
     */
    public function contexto(Request $request): array
    {
        $periodo = $request->query('periodo');
        $ano = $request->query('ano');
        $inicio = $request->query('inicio');
        $fim = $request->query('fim');

        if (is_string($periodo) && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $periodo) === 1) {
            $mes = CarbonImmutable::createFromFormat('Y-m', $periodo);

            return [
                'tipo' => 'mes',
                'inicio' => $mes->startOfMonth(),
                'fim' => $mes->endOfMonth(),
                'label' => self::MESES[(int) $mes->format('n')].' de '.$mes->format('Y'),
            ];
        }

        if (is_string($ano) && preg_match('/^\d{4}$/', $ano) === 1) {
            $anoInicio = CarbonImmutable::createFromFormat('Y-m-d', "{$ano}-01-01");

            return [
                'tipo' => 'ano',
                'inicio' => $anoInicio->startOfYear(),
                'fim' => $anoInicio->endOfYear(),
                'label' => "Ano de {$ano}",
            ];
        }

        if (is_string($inicio) || is_string($fim)) {
            $inicioData = is_string($inicio) && $this->dataValida($inicio)
                ? CarbonImmutable::parse($inicio)
                : CarbonImmutable::now()->startOfYear();

            $fimData = is_string($fim) && $this->dataValida($fim)
                ? CarbonImmutable::parse($fim)
                : CarbonImmutable::now();

            if ($fimData->lt($inicioData)) {
                [$inicioData, $fimData] = [$fimData, $inicioData];
            }

            return [
                'tipo' => 'periodo',
                'inicio' => $inicioData->startOfDay(),
                'fim' => $fimData->endOfDay(),
                'label' => $inicioData->translatedFormat('d/m/Y').' até '.$fimData->translatedFormat('d/m/Y'),
            ];
        }

        return [
            'tipo' => 'tudo',
            'inicio' => CarbonImmutable::createFromFormat('Y-m-d', '1970-01-01'),
            'fim' => null,
            'label' => 'Todo o período',
        ];
    }

    /**
     * Dados completos do relatório para o intervalo informado.
     *
     * @return array{
     *     receitas: array<int, array<string, mixed>>,
     *     despesas: array<int, array<string, mixed>>,
     *     totais: array{total_receitas: string, total_despesas: string, resultado: string, qtd_receitas: int, qtd_despesas: int}
     * }
     */
    public function dados(CarbonImmutable $inicio, ?CarbonImmutable $fim): array
    {
        $receitas = $this->movimentacoes(LancamentoTipo::Receita, $inicio, $fim);
        $despesas = $this->movimentacoes(LancamentoTipo::Despesa, $inicio, $fim);

        $totalReceitas = $this->somarValores($receitas);
        $totalDespesas = $this->somarValores($despesas);

        return [
            'receitas' => $receitas,
            'despesas' => $despesas,
            'totais' => [
                'total_receitas' => $this->formatar($totalReceitas),
                'total_despesas' => $this->formatar($totalDespesas),
                'resultado' => $this->formatar($totalReceitas - $totalDespesas),
                'qtd_receitas' => count($receitas),
                'qtd_despesas' => count($despesas),
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function movimentacoes(
        LancamentoTipo $tipo,
        CarbonImmutable $inicio,
        ?CarbonImmutable $fim
    ): array {
        $query = Lancamento::query()
            ->with(['categoria:id,nome', 'contraparte:id,nome'])
            ->where('tipo', $tipo)
            ->where('data', '>=', $inicio)
            ->orderBy('data')
            ->orderByDesc('id');

        if ($fim !== null) {
            $query->where('data', '<=', $fim);
        }

        return $query->get()->map(function (Lancamento $lancamento): array {
            $cancelado = $lancamento->status === LancamentoStatus::Cancelado;

            return [
                'id' => $lancamento->getKey(),
                'data' => $lancamento->data->toDateString(),
                'descricao' => $lancamento->descricao,
                'valor' => $lancamento->valor,
                'valor_formatado' => $this->formatar((float) $lancamento->valor),
                'status' => $lancamento->status->value,
                'categoria' => $lancamento->categoria?->nome,
                'contraparte' => $lancamento->contraparte?->nome,
                'cancelado' => $cancelado,
            ];
        })->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $movimentacoes
     */
    private function somarValores(array $movimentacoes): float
    {
        return array_sum(array_map(
            fn (array $movimentacao): float => $movimentacao['cancelado']
                ? 0.0
                : (float) $movimentacao['valor'],
            $movimentacoes
        ));
    }

    private function dataValida(string $data): bool
    {
        return CarbonImmutable::hasFormat($data, 'Y-m-d');
    }

    private function formatar(float $valor): string
    {
        return number_format($valor, 2, ',', '.');
    }
}
