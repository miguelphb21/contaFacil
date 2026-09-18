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
import { onMounted, ref } from 'vue'
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
    filtroData: string | null
    novo?: boolean
}>()

const page = usePage<PageProps>()

const aberto = ref(false)
const editando = ref<LancamentoItem | null>(null)

onMounted(() => {
    if (props.novo) {
        abrirNovo()
    }
})

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
        return 'bg-slate-100 text-slate-500'
    }

    return 'bg-amber-50 text-amber-700'
}

const alterarStatus = (lancamento: LancamentoItem, evento: Event) => {
    const status = (evento.target as HTMLSelectElement).value

    router.post(
        route(`${props.prefixoRota}.status`, lancamento.id),
        { status, periodo: props.periodo.chave },
        { preserveScroll: true },
    )
}

const excluir = (id: number) => {
    if (!confirm('Deseja realmente excluir este lançamento?')) {
        return
    }

    router.delete(
        route(`${props.prefixoRota}.destroy`, {
            lancamento: id,
            periodo: props.periodo.chave,
        }),
        { preserveScroll: true },
    )
}

const excluirTodos = (id: number) => {
    if (!confirm('Deseja excluir todas as ocorrências pendentes desta recorrência? As pagas e canceladas serão preservadas.')) {
        return
    }

    router.delete(
        route(`${props.prefixoRota}.excluir-recorrencia`, {
            lancamento: id,
            periodo: props.periodo.chave,
        }),
        { preserveScroll: true },
    )
}

const encerrarRecorrencia = (id: number) => {
    if (!confirm('Deseja encerrar esta recorrência? As ocorrências futuras deixarão de ser geradas.')) {
        return
    }

    router.post(
        route(`${props.prefixoRota}.encerrar-recorrencia`, id),
        { periodo: props.periodo.chave },
        { preserveScroll: true },
    )
}

const descricaoPeriodo = (lancamento: LancamentoItem) => {
    const fim = lancamento.recorrencia?.data_fim

    if (!fim) {
        return 'Recorrente · contínua'
    }

    return `Recorrente · até ${formatarData(fim)}`
}
</script>

<template>
    <div class="mx-auto max-w-6xl">
        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">
                    Financeiro
                </p>

                <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">
                    {{ titulo }}
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    {{ descricao }}
                </p>
            </div>

            <button
                type="button"
                @click="abrirNovo"
                class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700 sm:w-auto"
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
                :filtro-data="filtroData"
            />

            <ResumoMensal :resumo="resumo" />

            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-5">
                    <h2 class="font-semibold text-slate-900">
                        Lançamentos de
                        {{ filtroData ? formatarData(filtroData) : periodo.label }}
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        {{ lancamentos.length }} lançamento(s) no período.
                    </p>
                </div>

                <div
                    v-if="lancamentos.length"
                    class="overflow-x-auto"
                >
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
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

                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="lancamento in lancamentos"
                                :key="lancamento.id"
                                class="transition hover:bg-slate-100/60"
                            >
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-700">
                                    {{ formatarData(lancamento.data) }}
                                </td>

                                <td class="max-w-md px-6 py-4">
                                    <p class="font-semibold text-slate-900">
                                        {{ lancamento.descricao }}
                                    </p>

                                    <div
                                        v-if="lancamento.categoria || lancamento.contraparte || lancamento.recorrencia"
                                        class="mt-1 flex flex-wrap items-center gap-1.5"
                                    >
                                        <span
                                            v-if="lancamento.categoria"
                                            class="rounded-md bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600"
                                        >
                                            {{ lancamento.categoria.nome }}
                                        </span>

                                        <span
                                            v-if="lancamento.contraparte"
                                            class="rounded-md bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600"
                                        >
                                            {{ lancamento.contraparte.nome }}
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

                                            {{ descricaoPeriodo(lancamento) }}
                                        </span>
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold">
                                    <span :class="tipo === 'receita' ? 'text-emerald-700' : 'text-rose-700'">
                                        {{ formatarValor(lancamento.valor) }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <select
                                        :value="lancamento.status"
                                        @change="alterarStatus(lancamento, $event)"
                                        class="cursor-pointer rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-xs font-semibold outline-none transition focus:border-slate-900"
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
                                            v-if="lancamento.recorrencia?.ativa"
                                            type="button"
                                            @click="encerrarRecorrencia(lancamento.id)"
                                            class="rounded-lg px-3 py-2 text-sm font-medium text-amber-700 transition hover:bg-amber-50 hover:text-amber-800"
                                        >
                                            Encerrar recorrência
                                        </button>

                                        <button
                                            type="button"
                                            @click="abrirEdicao(lancamento)"
                                            class="rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-900"
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

                                        <button
                                            v-if="lancamento.recorrencia"
                                            type="button"
                                            @click="excluirTodos(lancamento.id)"
                                            class="rounded-lg px-3 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-50 hover:text-red-800"
                                        >
                                            Excluir todos
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
                        class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-slate-400"
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

                    <h3 class="mt-4 font-semibold text-slate-900">
                        Nenhum lançamento neste mês
                    </h3>

                    <p class="mt-1 max-w-sm text-sm text-slate-500">
                        Registre o primeiro lançamento de
                        {{ filtroData ? formatarData(filtroData) : periodo.label }}.
                    </p>

                    <button
                        type="button"
                        @click="abrirNovo"
                        class="mt-5 rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700"
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
            :periodo="periodo.chave"
            @fechado="fechar"
        />
    </div>
</template>