<script setup lang="ts">
import FlashMessage from '@/Components/FlashMessage.vue'
import ResumoMensal from '@/Components/ResumoMensal.vue'
import PeriodoNavigator from '@/Components/PeriodoNavigator.vue'
import LancamentoForm from '@/Components/Financeiro/LancamentoForm.vue'
import type {
    LancamentoItem,
    LancamentoTipo,
    Opcao,
    Periodo,
    ResumoMensal as Resumo,
} from '@/types'
import { ref } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

interface Flash {
    success?: string | null
    error?: string | null
}

interface PageProps {
    flash: Flash
    [key: string]: unknown
}

const props = defineProps<{
    titulo: string
    descricao: string
    tipo: LancamentoTipo
    prefixoRota: 'receitas' | 'despesas'
    lancamentos: LancamentoItem[]
    categorias: Opcao[]
    contrapartes: Opcao[]
    resumo: Resumo
    periodo: Periodo
}>()

const page = usePage<PageProps>()

const aberto = ref(false)
const editando = ref<LancamentoItem | null>(null)

const rotaIndex = route(`${props.prefixoRota}.index`)

const abrirNovo = () => {
    editando.value = null
    aberto.value = true
}

const abrirEdicao = (lancamento: LancamentoItem) => {
    editando.value = lancamento
    aberto.value = true
}

const fechar = () => {
    aberto.value = false
    editando.value = null
}

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

const etiquetaStatus = (status: LancamentoItem['status']) => {
    if (status === 'pago') {
        return 'Pago'
    }

    if (status === 'cancelado') {
        return 'Cancelado'
    }

    return 'Pendente'
}

const statusOpcoes: LancamentoItem['status'][] = [
    'pendente',
    'pago',
    'cancelado',
]

const classesStatus = (status: LancamentoItem['status']) => {
    if (status === 'pago') {
        return 'bg-emerald-50 text-emerald-700'
    }

    if (status === 'cancelado') {
        return 'bg-gray-100 text-gray-500'
    }

    return 'bg-amber-50 text-amber-700'
}

const alterarStatus = (lancamento: LancamentoItem, evento: Event) => {
    const status = (evento.target as HTMLSelectElement).value

    router.post(
        route(`${props.prefixoRota}.status`, lancamento.id),
        { status },
        { preserveScroll: true },
    )
}

const excluir = (id: number) => {
    if (!confirm('Deseja realmente excluir este lançamento?')) {
        return
    }

    router.delete(
        route(`${props.prefixoRota}.destroy`, id),
        { preserveScroll: true },
    )
}
</script>

<template>
    <div class="mx-auto max-w-6xl">
        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-emerald-700">
                    Financeiro
                </p>

                <h1 class="mt-1 text-2xl font-bold tracking-tight text-gray-900">
                    {{ titulo }}
                </h1>

                <p class="mt-1 text-sm text-gray-500">
                    {{ descricao }}
                </p>
            </div>

            <button
                type="button"
                @click="abrirNovo"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-800"
            >
                <span class="text-lg leading-none">+</span>
                Novo lançamento
            </button>
        </div>

        <FlashMessage
            :success="page.props.flash?.success"
            :error="page.props.flash?.error"
        />

        <div class="space-y-6">
            <PeriodoNavigator
                :periodo="periodo"
                :rota-index="rotaIndex"
            />

            <ResumoMensal :resumo="resumo" />

            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 px-6 py-5">
                    <h2 class="font-semibold text-gray-900">
                        Lançamentos de {{ periodo.label }}
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        {{ lancamentos.length }} lançamento(s) no período.
                    </p>
                </div>

                <div
                    v-if="lancamentos.length"
                    class="overflow-x-auto"
                >
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                            <tr>
                                <th class="px-6 py-4 font-semibold">
                                    Data
                                </th>

                                <th class="px-6 py-4 font-semibold">
                                    Descrição
                                </th>

                                <th class="px-6 py-4 font-semibold">
                                    Valor
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
                                v-for="lancamento in lancamentos"
                                :key="lancamento.id"
                                class="transition hover:bg-emerald-50/40"
                            >
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700">
                                    {{ formatarData(lancamento.data) }}
                                </td>

                                <td class="max-w-md px-6 py-4">
                                    <p class="font-semibold text-gray-900">
                                        {{ lancamento.descricao }}
                                    </p>

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
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold">
                                    <span :class="tipo === 'receita' ? 'text-emerald-700' : 'text-red-600'">
                                        {{ formatarValor(lancamento.valor) }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <select
                                        :value="lancamento.status"
                                        @change="alterarStatus(lancamento, $event)"
                                        class="cursor-pointer rounded-lg border border-gray-200 bg-white px-2 py-1.5 text-xs font-semibold outline-none transition focus:border-emerald-500"
                                        :class="classesStatus(lancamento.status)"
                                    >
                                        <option
                                            v-for="opcao in statusOpcoes"
                                            :key="opcao"
                                            :value="opcao"
                                        >
                                            {{ etiquetaStatus(opcao) }}
                                        </option>
                                    </select>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex justify-end gap-2">
                                        <button
                                            type="button"
                                            @click="abrirEdicao(lancamento)"
                                            class="rounded-lg px-3 py-2 text-sm font-medium text-emerald-600 transition hover:bg-emerald-50 hover:text-emerald-800"
                                        >
                                            Editar
                                        </button>

                                        <button
                                            type="button"
                                            @click="excluir(lancamento.id)"
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
                            <path d="M12 5v14M5 12h14" />
                        </svg>
                    </div>

                    <h3 class="mt-4 font-semibold text-gray-900">
                        Nenhum lançamento neste mês
                    </h3>

                    <p class="mt-1 max-w-sm text-sm text-gray-500">
                        Registre o primeiro lançamento de {{ periodo.label }}.
                    </p>

                    <button
                        type="button"
                        @click="abrirNovo"
                        class="mt-5 rounded-lg bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-800"
                    >
                        Cadastrar lançamento
                    </button>
                </div>
            </div>
        </div>

        <LancamentoForm
            :aberto="aberto"
            :lancamento="editando"
            :categorias="categorias"
            :contrapartes="contrapartes"
            :prefixo-rota="prefixoRota"
            @fechado="fechar"
        />
    </div>
</template>