<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'
import CategoriasDespesas from '@/Components/Dashboard/CategoriasDespesas.vue'
import ComparativoFinanceiro from '@/Components/Dashboard/ComparativoFinanceiro.vue'
import MetricasFinanceiras from '@/Components/Dashboard/MetricasFinanceiras.vue'
import ProximasMovimentacoes from '@/Components/Dashboard/ProximasMovimentacoes.vue'
import UltimasMovimentacoes from '@/Components/Dashboard/UltimasMovimentacoes.vue'
import type { DashboardDados, Periodo } from '@/types'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

interface EmpresaResumida {
    id: number
    razao_social: string
    nome_fantasia: string | null
}

interface PageProps {
    empresa_atual?: EmpresaResumida | null
    [key: string]: unknown
}

const props = defineProps<{
    dashboard: DashboardDados
    periodo: Periodo
}>()

const page = usePage<PageProps>()

const nomeEmpresa = computed(() => {
    const empresa = page.props.empresa_atual

    return empresa !== null && empresa !== undefined
        ? (empresa.nome_fantasia ?? empresa.razao_social)
        : 'Minha empresa'
})

const navegarMes = (periodo: string) => {
    router.get(route('dashboard'), { periodo }, { preserveScroll: true })
}
</script>

<template>
    <Head :title="'Dashboard'" />

    <AppLayout>
        <div class="min-h-screen bg-[#f6f6f2] p-4 sm:p-6 lg:p-8">
            <div class="mx-auto max-w-6xl">
                <div
                    class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between"
                >
                    <div>
                        <p class="text-sm font-medium text-slate-500">
                            Painel · Dashboard
                        </p>

                        <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">
                            Visão geral
                        </h1>

                        <p class="mt-1 text-sm text-slate-500">
                            Situação financeira de
                            <span class="font-semibold text-slate-700">
                                {{ nomeEmpresa }}
                            </span>
                        </p>

                        <div class="mt-3 inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2 py-1">
                            <button
                                type="button"
                                aria-label="Mês anterior"
                                @click="navegarMes(periodo.anterior)"
                                class="flex h-6 w-6 items-center justify-center rounded-md text-slate-500 transition hover:bg-slate-100 hover:text-slate-900"
                            >
                                <svg
                                    class="h-4 w-4"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path d="m15 18-6-6 6-6" />
                                </svg>
                            </button>

                            <span class="px-1 text-sm font-medium text-slate-700">
                                {{ periodo.label }}
                            </span>

                            <button
                                type="button"
                                aria-label="Próximo mês"
                                @click="navegarMes(periodo.proximo)"
                                class="flex h-6 w-6 items-center justify-center rounded-md text-slate-500 transition hover:bg-slate-100 hover:text-slate-900"
                            >
                                <svg
                                    class="h-4 w-4"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path d="m9 18 6-6-6-6" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="flex w-full gap-2 sm:w-auto">
                        <Link
                            :href="route('receitas.index', { novo: 1 })"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700 sm:w-auto"
                        >
                            <svg
                                class="h-4 w-4"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2.5"
                            >
                                <path d="M12 5v14M5 12h14" />
                            </svg>

                            Nova receita
                        </Link>

                        <Link
                            :href="route('despesas.index', { novo: 1 })"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-slate-400 hover:bg-slate-50 sm:w-auto"
                        >
                            <svg
                                class="h-4 w-4"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2.5"
                            >
                                <path d="M12 5v14M5 12h14" />
                            </svg>

                            Nova despesa
                        </Link>
                    </div>
                </div>

                <div class="space-y-6">
                    <MetricasFinanceiras
                        :receitas="dashboard.metrica.receitas"
                        :despesas="dashboard.metrica.despesas"
                        :resultado="dashboard.metrica.resultado"
                        :periodo="periodo.chave"
                    />

                    <div
                        v-if="dashboard.vencidas > 0"
                        class="flex flex-col gap-3 rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-700"
                            >
                                <svg
                                    class="h-5 w-5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z" />
                                    <path d="M12 9v4M12 17h.01" />
                                </svg>
                            </div>

                            <div>
                                <p class="text-sm font-semibold text-amber-800">
                                    Atenção necessária
                                </p>

                                <p class="text-sm text-amber-700">
                                    {{ dashboard.vencidas }} despesa(s) vencida(s)
                                    ainda aguardando pagamento.
                                </p>
                            </div>
                        </div>

                        <Link
                            :href="route('despesas.index', { periodo: periodo.chave })"
                            class="shrink-0 rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-amber-700"
                        >
                            Ver despesas
                        </Link>
                    </div>

                    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
                        <ProximasMovimentacoes :lancamentos="dashboard.proximas" />

                        <ComparativoFinanceiro
                            :comparativo="dashboard.comparativo"
                            :receitas-total="dashboard.metrica.receitas.total"
                            :despesas-total="dashboard.metrica.despesas.total"
                            :mes-anterior="periodo.anterior"
                        />
                    </div>

                    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
                        <CategoriasDespesas
                            :categorias="dashboard.categorias"
                            :periodo="periodo.chave"
                        />

                        <UltimasMovimentacoes :lancamentos="dashboard.ultimas" />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>