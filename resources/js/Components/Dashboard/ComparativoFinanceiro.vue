<script setup lang="ts">
import type { ComparativoDados } from '@/types'
import { formatarMoeda, periodoLabelCurto } from '@/utils/formatters'

const props = defineProps<{
    comparativo: ComparativoDados
    receitasTotal: string
    despesasTotal: string
    mesAnterior: string
}>()

const labelMesAnterior = periodoLabelCurto(props.mesAnterior)

interface VariacaoInfo {
    variacao: number | null
    boa: boolean | null
    texto: string | null
}

const variacaoEntradas = (): VariacaoInfo => {
    const variacao = props.comparativo.variacao_receitas

    if (variacao === null) {
        return { variacao: null, boa: null, texto: null }
    }

    return {
        variacao,
        boa: variacao >= 0,
        texto: `${variacao >= 0 ? '+' : ''}${variacao.toLocaleString('pt-BR')}%`,
    }
}

const variacaoSaidas = (): VariacaoInfo => {
    const variacao = props.comparativo.variacao_despesas

    if (variacao === null) {
        return { variacao: null, boa: null, texto: null }
    }

    return {
        variacao,
        boa: variacao <= 0,
        texto: `${variacao >= 0 ? '+' : ''}${variacao.toLocaleString('pt-BR')}%`,
    }
}

const classesVariacao = (info: VariacaoInfo) => {
    if (info.texto === null) {
        return 'bg-slate-100 text-slate-500'
    }

    return info.boa ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'
}

const setaVariacao = (info: VariacaoInfo) => {
    if (info.texto === null) {
        return '•'
    }

    return (info.variacao ?? 0) >= 0 ? '▲' : '▼'
}
</script>

<template>
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
        <div class="border-b border-slate-200 px-6 py-5">
            <h2 class="font-semibold text-slate-900">
                Entradas × Saídas
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Proporção do que entrou e saiu no período.
            </p>
        </div>

        <div class="px-6 py-5">
            <div class="flex h-2.5 w-full overflow-hidden rounded-full bg-slate-100">
                <div
                    v-if="comparativo.receitas_pct > 0"
                    class="bg-emerald-500"
                    :style="{ width: `${comparativo.receitas_pct}%` }"
                />

                <div
                    v-if="comparativo.despesas_pct > 0"
                    class="bg-rose-400"
                    :style="{ width: `${comparativo.despesas_pct}%` }"
                />
            </div>

            <div class="mt-5 space-y-1">
                <div class="flex flex-wrap items-center justify-between gap-2 py-2">
                    <div class="flex min-w-0 items-center gap-2.5">
                        <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-emerald-500" />

                        <span class="text-sm font-medium text-slate-700">
                            Entradas
                        </span>

                        <span class="text-xs text-slate-400">
                            {{ comparativo.receitas_pct.toLocaleString('pt-BR') }}%
                        </span>
                    </div>

                    <div class="flex items-center gap-3">
                        <span class="text-sm font-bold text-emerald-700">
                            {{ formatarMoeda(receitasTotal) }}
                        </span>

                        <span
                            class="inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs font-semibold"
                            :class="classesVariacao(variacaoEntradas())"
                        >
                            {{ setaVariacao(variacaoEntradas()) }}
                            {{ variacaoEntradas().texto ?? 'sem base' }}
                        </span>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-2 py-2">
                    <div class="flex min-w-0 items-center gap-2.5">
                        <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-rose-400" />

                        <span class="text-sm font-medium text-slate-700">
                            Saídas
                        </span>

                        <span class="text-xs text-slate-400">
                            {{ comparativo.despesas_pct.toLocaleString('pt-BR') }}%
                        </span>
                    </div>

                    <div class="flex items-center gap-3">
                        <span class="text-sm font-bold text-rose-700">
                            {{ formatarMoeda(despesasTotal) }}
                        </span>

                        <span
                            class="inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs font-semibold"
                            :class="classesVariacao(variacaoSaidas())"
                        >
                            {{ setaVariacao(variacaoSaidas()) }}
                            {{ variacaoSaidas().texto ?? 'sem base' }}
                        </span>
                    </div>
                </div>
            </div>

            <p class="mt-4 border-t border-slate-100 pt-4 text-xs text-slate-400">
                Variação em relação a {{ labelMesAnterior }}.
            </p>
        </div>
    </div>
</template>