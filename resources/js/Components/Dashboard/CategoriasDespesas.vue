<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import type { CategoriaDespesa } from '@/types'
import { formatarMoeda } from '@/utils/formatters'

const props = defineProps<{
    categorias: CategoriaDespesa[]
    periodo: string
}>()

const cores = [
    'bg-emerald-500',
    'bg-sky-500',
    'bg-violet-500',
    'bg-amber-500',
    'bg-slate-400',
]

const cor = (indice: number) => cores[indice % cores.length] ?? 'bg-slate-400'
</script>

<template>
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
        <div class="border-b border-slate-200 px-6 py-5">
            <h2 class="font-semibold text-slate-900">
                Despesas por categoria
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Para onde o dinheiro está indo.
            </p>
        </div>

        <div
            v-if="categorias.length"
            class="divide-y divide-slate-100"
        >
            <Link
                v-for="(categoria, indice) in categorias"
                :key="categoria.nome"
                :href="route('despesas.index', { periodo })"
                class="block px-6 py-4 transition hover:bg-slate-50/80"
            >
                <div class="flex items-center justify-between gap-4">
                    <div class="flex min-w-0 items-center gap-2.5">
                        <span
                            class="h-2.5 w-2.5 shrink-0 rounded-full"
                            :class="cor(indice)"
                        />

                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-slate-800">
                                {{ categoria.nome }}
                            </p>

                            <p class="text-xs text-slate-400">
                                {{ categoria.qtd }} lançamento(s)
                            </p>
                        </div>
                    </div>

                    <div class="text-right">
                        <p class="text-sm font-bold text-slate-900">
                            {{ formatarMoeda(categoria.valor) }}
                        </p>

                        <p class="text-xs text-slate-400">
                            {{ categoria.percentual.toLocaleString('pt-BR') }}%
                        </p>
                    </div>
                </div>

                <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-slate-100">
                    <div
                        class="h-full rounded-full"
                        :class="cor(indice)"
                        :style="{ width: `${Math.min(categoria.percentual, 100)}%` }"
                    />
                </div>
            </Link>
        </div>

        <div
            v-else
            class="flex flex-col items-center justify-center px-6 py-12 text-center"
        >
            <div
                class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400"
            >
                <svg
                    class="h-6 w-6"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path d="M3 3v18h18" />
                    <path d="m7 15 4-4 3 3 5-6" />
                </svg>
            </div>

            <p class="mt-3 text-sm font-medium text-slate-600">
                Sem despesas no período
            </p>

            <p class="mt-1 text-xs text-slate-400">
                As categorias surgem aqui quando houver saídas.
            </p>
        </div>
    </div>
</template>