<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'
import PeriodoNavigator from '@/Components/PeriodoNavigator.vue'
import ResumoMensal from '@/Components/ResumoMensal.vue'
import { Link } from '@inertiajs/vue3'
import type { LancamentoItem, Periodo, ResumoMensal as Resumo } from '@/types'

const props = defineProps<{
    resumo: Resumo
    periodo: Periodo
    proximas: LancamentoItem[]
}>()

const formatarValor = (valor: string) => {
    const numero = Number(valor)

    return numero.toLocaleString('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    })
}

const formatarData = (data: string) => {
    const instante = new Date(data)
    if (Number.isNaN(instante.getTime())) {
        return data
    }

    return instante.toLocaleDateString('pt-BR')
}

const rotaVencimento = (lancamento: LancamentoItem) =>
    lancamento.tipo === 'receita'
        ? route('receitas.index', { periodo: lancamento.data.slice(0, 7) })
        : route('despesas.index', { periodo: lancamento.data.slice(0, 7) })
</script>

<template>
    <AppLayout>
        <div class="min-h-screen bg-gray-50 p-6 lg:p-8">
            <div class="mx-auto max-w-6xl">
                <div class="mb-8">
                    <p class="text-sm font-medium text-emerald-700">
                        Painel
                    </p>

                    <h1
                        class="mt-1 text-2xl font-bold tracking-tight text-gray-900"
                    >
                        Dashboard
                    </h1>

                    <p class="mt-1 text-sm text-gray-500">
                        Visão geral do seu financeiro.
                    </p>
                </div>

                <div class="space-y-6">
                    <PeriodoNavigator
                        :periodo="props.periodo"
                        :rota-index="route('dashboard')"
                    />

                    <ResumoMensal :resumo="props.resumo" />

                    <div
                        class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm"
                    >
                        <div class="border-b border-gray-200 px-6 py-5">
                            <h2 class="font-semibold text-gray-900">
                                Próximos vencimentos
                            </h2>

                            <p class="mt-1 text-sm text-gray-500">
                                Pendências com data igual ou posterior a hoje.
                            </p>
                        </div>

                        <div
                            v-if="proximas.length"
                            class="divide-y divide-gray-100"
                        >
                            <div
                                v-for="lancamento in proximas"
                                :key="lancamento.id"
                                class="flex items-center gap-4 px-6 py-4 transition hover:bg-emerald-50/40"
                            >
                                <div
                                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg font-semibold"
                                    :class="lancamento.tipo === 'receita'
                                        ? 'bg-emerald-50 text-emerald-700'
                                        : 'bg-red-50 text-red-600'"
                                >
                                    {{ lancamento.tipo === 'receita' ? '+' : '-' }}
                                </div>

                                <div class="min-w-0 flex-1">
                                    <Link
                                        :href="rotaVencimento(lancamento)"
                                        class="font-semibold text-gray-900 hover:text-emerald-700"
                                    >
                                        {{ lancamento.descricao }}
                                    </Link>

                                    <div
                                        v-if="lancamento.categoria || lancamento.contraparte"
                                        class="mt-1 flex flex-wrap items-center gap-1.5"
                                    >
                                        <span
                                            v-if="lancamento.categoria"
                                            class="rounded-md bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700"
                                        >
                                            {{ lancamento.categoria.nome }}
                                        </span>

                                        <span
                                            v-if="lancamento.contraparte"
                                            class="rounded-md bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600"
                                        >
                                            {{ lancamento.contraparte.nome }}
                                        </span>
                                    </div>
                                </div>

                                <div class="text-right">
                                    <p class="text-sm font-bold text-gray-900">
                                        {{ formatarValor(lancamento.valor) }}
                                    </p>

                                    <p class="text-xs text-gray-400">
                                        {{ formatarData(lancamento.data) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div
                            v-else
                            class="flex flex-col items-center justify-center px-6 py-16 text-center"
                        >
                            <div
                                class="flex h-16 w-16 items-center justify-center rounded-full bg-emerald-50 text-emerald-700"
                            >
                                <svg
                                    class="h-8 w-8"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path d="m5 12 4 4L19 6" />
                                </svg>
                            </div>

                            <h3 class="mt-4 font-semibold text-gray-900">
                                Nenhum vencimento pendente
                            </h3>

                            <p class="mt-1 max-w-sm text-sm text-gray-500">
                                Todas as pendências estão em dia por aqui.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>