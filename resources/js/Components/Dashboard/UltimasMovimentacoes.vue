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

const etiquetaStatus = (status: LancamentoItem['status']) => {
    if (status === 'pago') {
        return 'Pago'
    }

    if (status === 'cancelado') {
        return 'Cancelado'
    }

    return 'Pendente'
}

const classesStatus = (status: LancamentoItem['status']) => {
    if (status === 'pago') {
        return 'bg-emerald-50 text-emerald-700'
    }

    if (status === 'cancelado') {
        return 'bg-slate-100 text-slate-500'
    }

    return 'bg-amber-50 text-amber-700'
}
</script>

<template>
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
        <div class="border-b border-slate-200 px-6 py-5">
            <h2 class="font-semibold text-slate-900">
                Últimas movimentações
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                A atividade mais recente da empresa.
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
                class="flex items-center gap-4 px-6 py-3.5 transition hover:bg-slate-50/80"
            >
                <span
                    class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-sm font-bold"
                    :class="lancamento.tipo === 'receita'
                        ? 'bg-emerald-50 text-emerald-700'
                        : 'bg-rose-50 text-rose-700'"
                >
                    {{ lancamento.tipo === 'receita' ? '+' : '-' }}
                </span>

                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-slate-900">
                        {{ lancamento.descricao }}
                    </p>

                    <p class="text-xs text-slate-400">
                        {{ formatarData(lancamento.data) }}
                    </p>
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

                    <span
                        class="rounded-md px-2 py-0.5 text-xs font-medium"
                        :class="classesStatus(lancamento.status)"
                    >
                        {{ etiquetaStatus(lancamento.status) }}
                    </span>
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
                    <path d="M12 3v12" />
                    <path d="m7 10 5 5 5-5" />
                    <path d="M5 21h14" />
                </svg>
            </div>

            <p class="mt-3 text-sm font-medium text-slate-600">
                Sem movimentações
            </p>

            <p class="mt-1 text-xs text-slate-400">
                Registre a primeira movimentação da empresa.
            </p>
        </div>
    </div>
</template>