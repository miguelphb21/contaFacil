<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import type { MetricaItem } from '@/types'
import { converterParaNumero, formatarMoeda } from '@/utils/formatters'

const props = defineProps<{
    receitas: MetricaItem
    despesas: MetricaItem
    resultado: MetricaItem
    periodo: string
}>()

const resultadoPositivo = computed(() =>
    converterParaNumero(props.resultado.total) >= 0,
)

const plural = (qtd: number) => (qtd === 1 ? '1 lançamento' : `${qtd} lançamentos`)
</script>

<template>
    <div
        class="grid grid-cols-1 divide-y divide-slate-100 overflow-hidden rounded-xl border border-slate-200 bg-white sm:grid-cols-3 sm:divide-x sm:divide-y-0"
    >
        <Link
            :href="route('receitas.index', { periodo })"
            class="group flex flex-col gap-1 p-6 transition hover:bg-slate-50/80"
        >
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                    Entradas
                </p>

                <div
                    class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700"
                >
                    <svg
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="M22 7 9 20l-7-7" />
                        <path d="m22 7-8.5 1.8" />
                        <path d="M22 7 20.2 15.5" />
                    </svg>
                </div>
            </div>

            <p class="mt-3 text-2xl font-bold tracking-tight text-emerald-700">
                {{ formatarMoeda(receitas.total) }}
            </p>

            <p class="mt-1 text-xs text-slate-400">
                {{ plural(receitas.qtd ?? 0) }}
            </p>
        </Link>

        <Link
            :href="route('despesas.index', { periodo })"
            class="group flex flex-col gap-1 p-6 transition hover:bg-slate-50/80"
        >
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                    Saídas
                </p>

                <div
                    class="flex h-9 w-9 items-center justify-center rounded-lg bg-rose-50 text-rose-700"
                >
                    <svg
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="M2 7 15 20l7-7L22 7z" />
                        <path d="m2 7 8.5 1.8L22 7" />
                        <path d="M2 7l1.8 8.5" />
                    </svg>
                </div>
            </div>

            <p class="mt-3 text-2xl font-bold tracking-tight text-rose-700">
                {{ formatarMoeda(despesas.total) }}
            </p>

            <p class="mt-1 text-xs text-slate-400">
                {{ plural(despesas.qtd ?? 0) }}
            </p>
        </Link>

        <Link
            :href="route('relatorios.index')"
            class="group flex flex-col gap-1 p-6 transition hover:bg-slate-50/80"
        >
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                    Resultado
                </p>

                <div
                    class="flex h-9 w-9 items-center justify-center rounded-lg"
                    :class="resultadoPositivo
                        ? 'bg-emerald-50 text-emerald-700'
                        : 'bg-rose-50 text-rose-700'"
                >
                    <svg
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <rect x="3" y="5" width="18" height="14" rx="2" />
                        <path d="M3 10h18M7 15h4" />
                    </svg>
                </div>
            </div>

            <p
                class="mt-3 text-2xl font-bold tracking-tight"
                :class="resultadoPositivo ? 'text-emerald-700' : 'text-rose-700'"
            >
                {{ formatarMoeda(resultado.total) }}
            </p>

            <p class="mt-1 text-xs text-slate-400">
                Receitas − Despesas
            </p>
        </Link>
    </div>
</template>