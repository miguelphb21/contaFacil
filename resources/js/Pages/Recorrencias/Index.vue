<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'
import FlashMessage from '@/Components/FlashMessage.vue'
import RecorrenciaForm from '@/Components/Financeiro/RecorrenciaForm.vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import type {
    Flash,
    LancamentoTipo,
    OpcaoTipoContraparte,
    OpcaoTipoLancamento,
    RecorrenciaItem,
} from '@/types'
import { computed, ref } from 'vue'

interface PageProps {
    flash: Flash
    [key: string]: unknown
}

const props = defineProps<{
    recorrencias: RecorrenciaItem[]
    categorias: OpcaoTipoLancamento[]
    contrapartes: OpcaoTipoContraparte[]
    filtro: string | null
}>()

const page = usePage<PageProps>()

const aberto = ref(false)
const editando = ref<RecorrenciaItem | null>(null)

const abrirNovo = () => {
    editando.value = null
    aberto.value = true
}

const abrirEdicao = (recorrencia: RecorrenciaItem) => {
    editando.value = recorrencia
    aberto.value = true
}

const fechar = () => {
    aberto.value = false
    editando.value = null
}

const ativas = computed(
    () => props.recorrencias.filter((recorrencia) => recorrencia.ativa).length,
)

const formatarData = (data: string) => {
    const instante = new Date(data)
    if (Number.isNaN(instante.getTime())) {
        return data
    }

    return instante.toLocaleDateString('pt-BR')
}

const formatarValor = (valor: string) => {
    const numero = Number(valor)
    if (Number.isNaN(numero)) {
        return valor
    }

    return numero.toLocaleString('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    })
}

const filtros: Array<{ valor: LancamentoTipo | null; label: string }> = [
    { valor: null, label: 'Todas' },
    { valor: 'receita', label: 'Receitas' },
    { valor: 'despesa', label: 'Despesas' },
]

const filtroAtivo = computed<LancamentoTipo | null>(() => {
    if (props.filtro === 'receita' || props.filtro === 'despesa') {
        return props.filtro
    }

    return null
})

const rotaFiltro = (tipo: LancamentoTipo | null) =>
    route('recorrencias.index', tipo ? { tipo } : {})

const regenerar = (id: number) => {
    if (!confirm('Regenerar os lançamentos pendentes desta recorrência?')) {
        return
    }

    router.post(route('recorrencias.regenerar', id), {}, {
        preserveScroll: true,
    })
}

const excluir = (id: number) => {
    if (!confirm('Deseja realmente excluir esta recorrência?')) {
        return
    }

    router.delete(route('recorrencias.destroy', id), {
        preserveScroll: true,
    })
}
</script>

<template>
    <AppLayout>
        <div class="min-h-screen bg-gray-50 p-6 lg:p-8">
            <div class="mx-auto max-w-6xl">
                <div
                    class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <p class="text-sm font-medium text-emerald-700">
                            Financeiro
                        </p>

                        <h1
                            class="mt-1 text-2xl font-bold tracking-tight text-gray-900"
                        >
                            Recorrências
                        </h1>

                        <p class="mt-1 text-sm text-gray-500">
                            Despesas e receitas que se repetem todo mês.
                        </p>
                    </div>

                    <button
                        type="button"
                        @click="abrirNovo"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-800"
                    >
                        <span class="text-lg leading-none">+</span>
                        Nova recorrência
                    </button>
                </div>

                <FlashMessage
                    :success="page.props.flash?.success"
                    :error="page.props.flash?.error"
                />

                <div class="mb-6 flex flex-col gap-3">
                    <div
                        class="w-full rounded-xl border border-gray-200 bg-white p-5 shadow-sm sm:max-w-xs"
                    >
                        <p class="text-sm font-medium text-gray-500">
                            Recorrências ativas
                        </p>

                        <p class="mt-2 text-3xl font-bold text-gray-900">
                            {{ ativas }}
                            <span class="text-base font-medium text-gray-400">/ {{ recorrencias.length }}</span>
                        </p>
                    </div>

                    <div
                        class="inline-flex w-fit rounded-xl border border-gray-200 bg-white p-1 shadow-sm"
                    >
                        <Link
                            v-for="opcao in filtros"
                            :key="opcao.label"
                            :href="rotaFiltro(opcao.valor)"
                            class="rounded-lg px-4 py-2 text-sm font-medium transition"
                            :class="filtroAtivo === opcao.valor
                                ? 'bg-emerald-700 text-white'
                                : 'text-gray-500 hover:bg-emerald-50 hover:text-emerald-700'"
                        >
                            {{ opcao.label }}
                        </Link>
                    </div>
                </div>

                <div
                    class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm"
                >
                    <div
                        class="border-b border-gray-200 px-6 py-5"
                    >
                        <h2 class="font-semibold text-gray-900">
                            Recorrências cadastradas
                        </h2>

                        <p class="mt-1 text-sm text-gray-500">
                            {{ recorrencias.length }} recorrência(s).
                        </p>
                    </div>

                    <div
                        v-if="recorrencias.length"
                        class="overflow-x-auto"
                    >
                        <table class="w-full text-left">
                            <thead
                                class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500"
                            >
                                <tr>
                                    <th class="px-6 py-4 font-semibold">
                                        Recorrência
                                    </th>

                                    <th class="px-6 py-4 font-semibold">
                                        Vencimento
                                    </th>

                                    <th class="px-6 py-4 font-semibold">
                                        Valor
                                    </th>

                                    <th class="px-6 py-4 font-semibold">
                                        Início
                                    </th>

                                    <th class="px-6 py-4 font-semibold">
                                        Status
                                    </th>

                                    <th class="px-6 py-4 text-right font-semibold">
                                        Ações
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-100">
                                <tr
                                    v-for="recorrencia in recorrencias"
                                    :key="recorrencia.id"
                                    class="transition hover:bg-emerald-50/40"
                                >
                                    <td class="max-w-md px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg font-semibold"
                                                :class="recorrencia.tipo === 'receita'
                                                    ? 'bg-emerald-50 text-emerald-700'
                                                    : 'bg-red-50 text-red-600'"
                                            >
                                                {{ recorrencia.tipo === 'receita' ? '+' : '-' }}
                                            </div>

                                            <div class="min-w-0">
                                                <p class="truncate font-semibold text-gray-900">
                                                    {{ recorrencia.descricao }}
                                                </p>

                                                <div
                                                    v-if="recorrencia.categoria || recorrencia.contraparte"
                                                    class="mt-1 flex flex-wrap items-center gap-1.5"
                                                >
                                                    <span
                                                        v-if="recorrencia.categoria"
                                                        class="rounded-md bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700"
                                                    >
                                                        {{ recorrencia.categoria.nome }}
                                                    </span>

                                                    <span
                                                        v-if="recorrencia.contraparte"
                                                        class="rounded-md bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600"
                                                    >
                                                        {{ recorrencia.contraparte.nome }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700">
                                        Dia {{ recorrencia.dia }}
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                        {{ formatarValor(recorrencia.valor) }}
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ formatarData(recorrencia.data_inicio) }}
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            v-if="recorrencia.ativa"
                                            class="rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700"
                                        >
                                            Ativa
                                        </span>

                                        <span
                                            v-else
                                            class="rounded-md bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-500"
                                        >
                                            Inativa
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex justify-end gap-2">
                                            <button
                                                type="button"
                                                @click="regenerar(recorrencia.id)"
                                                class="rounded-lg px-3 py-2 text-sm font-medium text-amber-600 transition hover:bg-amber-50 hover:text-amber-800"
                                            >
                                                Regenerar
                                            </button>

                                            <button
                                                type="button"
                                                @click="abrirEdicao(recorrencia)"
                                                class="rounded-lg px-3 py-2 text-sm font-medium text-emerald-600 transition hover:bg-emerald-50 hover:text-emerald-800"
                                            >
                                                Editar
                                            </button>

                                            <button
                                                type="button"
                                                @click="excluir(recorrencia.id)"
                                                class="rounded-lg px-3 py-2 text-sm font-medium text-red-500 transition hover:bg-red-50 hover:text-red-700"
                                            >
                                                Excluir
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
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
                                <path d="M19 3 5 21" />
                                <path d="M22 17 5 17 5 7 22 7" />
                                <path d="M5 17 2 12 5 7" />
                            </svg>
                        </div>

                        <h3 class="mt-4 font-semibold text-gray-900">
                            Nenhuma recorrência cadastrada
                        </h3>

                        <p class="mt-1 max-w-sm text-sm text-gray-500">
                            Cadastre despesas e receitas recorrentes para
                            automatizar seus lançamentos.
                        </p>

                        <button
                            type="button"
                            @click="abrirNovo"
                            class="mt-5 rounded-lg bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-800"
                        >
                            Cadastrar recorrência
                        </button>
                    </div>
                </div>

                <RecorrenciaForm
                    :aberto="aberto"
                    :recorrencia="editando"
                    :categorias="categorias"
                    :contrapartes="contrapartes"
                    @fechado="fechar"
                />
            </div>
        </div>
    </AppLayout>
</template>