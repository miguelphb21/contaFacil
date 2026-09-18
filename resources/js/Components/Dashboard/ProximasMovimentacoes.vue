<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import type { LancamentoItem } from '@/types'
import { formatarData, formatarMoeda } from '@/utils/formatters'

const props = defineProps<{
    lancamentos: LancamentoItem[]
}>()

const rotaDoLancamento = (lancamento: LancamentoItem) =>
    lancamento.tipo === 'receita'
        ? route('receitas.index', { periodo: lancamento.data.slice(0, 7) })
        : route('despesas.index', { periodo: lancamento.data.slice(0, 7) })

const dia = (data: string) => new Date(data).getDate()
const mes = (data: string) =>
    new Date(data).toLocaleDateString('pt-BR', { month: 'short' }).replace('.', '')
</script>

<template>
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
        <div class="border-b border-slate-200 px-6 py-5">
            <h2 class="font-semibold text-slate-900">
                Próximas movimentações
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Entradas e saídas pendentes, da mais próxima para a mais distante.
            </p>
        </div>

        <div
            v-if="lancamentos.length"
            class="divide-y divide-slate-100"
        >
            <Link
                v-for="lancamento in lancamentos"
                :key="lancamento.id"
                :href="rotaDoLancamento(lancamento)"
                class="flex items-center gap-4 px-6 py-4 transition hover:bg-slate-50/80"
            >
                <div
                    class="flex h-12 w-12 shrink-0 flex-col items-center justify-center rounded-lg border"
                    :class="lancamento.tipo === 'receita'
                        ? 'border-emerald-100 bg-emerald-50 text-emerald-700'
                        : 'border-rose-100 bg-rose-50 text-rose-700'"
                >
                    <span class="text-base font-bold leading-none">{{ dia(lancamento.data) }}</span>
                    <span class="mt-0.5 text-[10px] font-medium uppercase leading-none opacity-70">
                        {{ mes(lancamento.data) }}
                    </span>
                </div>

                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-slate-900">
                        {{ lancamento.descricao }}
                    </p>

                    <div
                        v-if="lancamento.contraparte || lancamento.categoria || lancamento.recorrencia"
                        class="mt-1 flex flex-wrap items-center gap-1.5"
                    >
                        <span
                            v-if="lancamento.contraparte"
                            class="rounded-md bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600"
                        >
                            {{ lancamento.contraparte.nome }}
                        </span>

                        <span
                            v-if="lancamento.categoria"
                            class="rounded-md bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600"
                        >
                            {{ lancamento.categoria.nome }}
                        </span>

                        <span
                            v-if="lancamento.recorrencia"
                            class="inline-flex items-center gap-1 rounded-md bg-slate-900 px-2 py-0.5 text-xs font-medium text-white"
                        >
                            <svg
                                class="h-3 w-3"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path d="M17 2l4 4-4 4" />
                                <path d="M3 11V9a4 4 0 0 1 4-4h14" />
                                <path d="M7 22l-4-4 4-4" />
                                <path d="M21 13v2a4 4 0 0 1-4 4H3" />
                            </svg>

                            Recorrente
                        </span>
                    </div>
                </div>

                <div class="text-right">
                    <p
                        class="text-sm font-bold"
                        :class="lancamento.tipo === 'receita'
                            ? 'text-emerald-700'
                            : 'text-rose-700'"
                    >
                        {{ formatarMoeda(lancamento.valor) }}
                    </p>

                    <p class="text-xs text-slate-400">
                        {{ formatarData(lancamento.data) }}
                    </p>
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
                    <path d="m5 12 4 4L19 6" />
                </svg>
            </div>

            <p class="mt-3 text-sm font-medium text-slate-600">
                Nenhuma pendência futura
            </p>

            <p class="mt-1 text-xs text-slate-400">
                Tudo certo por aqui, sem movimentações aguardando.
            </p>
        </div>
    </div>
</template>