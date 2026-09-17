<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'
import FlashMessage from '@/Components/FlashMessage.vue'
import type {
    Flash,
    PeriodoRelatorio,
    PeriodoRelatorioTipo,
    RelatorioDados,
    RelatorioMovimentacao,
} from '@/types'
import { router, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

interface PageProps {
    flash: Flash
    [key: string]: unknown
}

const props = defineProps<{
    dados: RelatorioDados
    periodo: PeriodoRelatorio
}>()

const page = usePage<PageProps>()

const tipo = ref<PeriodoRelatorioTipo>(props.periodo.tipo)
const periodoMes = ref(
    props.periodo.tipo === 'mes' && props.periodo.inicio
        ? props.periodo.inicio.slice(0, 7)
        : '',
)
const inicio = ref(
    props.periodo.tipo === 'periodo' ? props.periodo.inicio : '',
)
const fim = ref(
    props.periodo.tipo === 'periodo' && props.periodo.fim
        ? props.periodo.fim.slice(0, 10)
        : '',
)
const ano = ref(
    props.periodo.tipo === 'ano' && props.periodo.inicio
        ? props.periodo.inicio.slice(0, 4)
        : '',
)

const opcoes: Array<{ valor: PeriodoRelatorioTipo; label: string }> = [
    { valor: 'mes', label: 'Mês' },
    { valor: 'periodo', label: 'Período' },
    { valor: 'ano', label: 'Ano' },
    { valor: 'tudo', label: 'Tudo' },
]

const hoje = new Date().toISOString().slice(0, 10)

const parametrosFiltro = computed(() => {
    if (tipo.value === 'mes') {
        return { periodo: periodoMes.value }
    }

    if (tipo.value === 'periodo') {
        return { inicio: inicio.value, fim: fim.value }
    }

    if (tipo.value === 'ano') {
        return { ano: ano.value }
    }

    return {}
})

const rotaExportacao = (sufixo: 'pdf' | 'excel') =>
    route(`relatorios.${sufixo}`, parametrosFiltro.value)

const consultar = () => {
    router.get(route('relatorios.index'), parametrosFiltro.value, {
        preserveScroll: true,
    })
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

const numeroDe = (valor: string) => {
    const numero = Number(String(valor).replace(/\./g, '').replace(',', '.'))

    return Number.isNaN(numero) ? 0 : numero
}

const resultadoPositivo = computed(
    () => numeroDe(props.dados.totais.resultado) >= 0,
)

const formatarData = (data: string) => {
    const instante = new Date(data)
    if (Number.isNaN(instante.getTime())) {
        return data
    }

    return instante.toLocaleDateString('pt-BR')
}

const etiquetaStatus = (status: RelatorioMovimentacao['status']) => {
    if (status === 'pago') {
        return 'Pago'
    }

    if (status === 'cancelado') {
        return 'Cancelado'
    }

    return 'Pendente'
}

const classesStatus = (status: RelatorioMovimentacao['status']) => {
    if (status === 'pago') {
        return 'bg-emerald-50 text-emerald-700'
    }

    if (status === 'cancelado') {
        return 'bg-gray-100 text-gray-500'
    }

    return 'bg-amber-50 text-amber-700'
}
</script>

<template>
    <AppLayout>
        <div class="min-h-screen bg-gray-50 p-6 lg:p-8">
            <div class="mx-auto max-w-6xl space-y-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-medium text-emerald-700">
                            Financeiro
                        </p>

                        <h1 class="mt-1 text-2xl font-bold tracking-tight text-gray-900">
                            Relatórios
                        </h1>

                        <p class="mt-1 text-sm text-gray-500">
                            Relatório financeiro da empresa no período selecionado.
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <a
                            :href="rotaExportacao('pdf')"
                            target="_blank"
                            class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                        >
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 3v12" />
                                <path d="m7 10 5 5 5-5" />
                                <path d="M5 21h14" />
                            </svg>
                            Exportar PDF
                        </a>

                        <a
                            :href="rotaExportacao('excel')"
                            target="_blank"
                            class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                        >
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 3v12" />
                                <path d="m7 10 5 5 5-5" />
                                <path d="M5 21h14" />
                            </svg>
                            Exportar Excel
                        </a>
                    </div>
                </div>

                <FlashMessage
                    :success="page.props.flash?.success"
                    :error="page.props.flash?.error"
                />

                <!-- Seletor de período -->
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="mb-4 inline-flex rounded-lg border border-gray-200 bg-gray-50 p-1">
                        <button
                            v-for="opcao in opcoes"
                            :key="opcao.valor"
                            type="button"
                            @click="tipo = opcao.valor"
                            class="rounded-md px-4 py-1.5 text-sm font-semibold transition"
                            :class="tipo === opcao.valor
                                ? 'bg-white text-emerald-700 shadow-sm'
                                : 'text-gray-500 hover:text-gray-800'"
                        >
                            {{ opcao.label }}
                        </button>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                        <div v-if="tipo === 'mes'" class="sm:w-56">
                            <label for="relatorio_mes" class="mb-1.5 block text-xs font-medium text-gray-500">
                                Mês
                            </label>
                            <input
                                id="relatorio_mes"
                                v-model="periodoMes"
                                type="month"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                            />
                        </div>

                        <template v-if="tipo === 'periodo'">
                            <div class="sm:w-52">
                                <label for="relatorio_inicio" class="mb-1.5 block text-xs font-medium text-gray-500">
                                    De
                                </label>
                                <input
                                    id="relatorio_inicio"
                                    v-model="inicio"
                                    type="date"
                                    :max="fim || hoje"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                />
                            </div>

                            <div class="sm:w-52">
                                <label for="relatorio_fim" class="mb-1.5 block text-xs font-medium text-gray-500">
                                    Até
                                </label>
                                <input
                                    id="relatorio_fim"
                                    v-model="fim"
                                    type="date"
                                    :min="inicio"
                                    :max="hoje"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                />
                            </div>
                        </template>

                        <div v-if="tipo === 'ano'" class="sm:w-40">
                            <label for="relatorio_ano" class="mb-1.5 block text-xs font-medium text-gray-500">
                                Ano
                            </label>
                            <input
                                id="relatorio_ano"
                                v-model="ano"
                                type="number"
                                min="2000"
                                max="2100"
                                placeholder="2026"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                            />
                        </div>

                        <button
                            type="button"
                            @click="consultar"
                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-700 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-800"
                        >
                            Consultar
                        </button>

                        <p class="flex-1 text-right text-sm font-medium text-gray-500">
                            {{ periodo.label }}
                        </p>
                    </div>
                </div>

                <!-- Totais -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="rounded-xl border border-emerald-100 bg-white p-5 shadow-sm">
                        <p class="text-sm font-medium text-gray-500">
                            Total de receitas
                        </p>

                        <p class="mt-2 text-2xl font-bold tracking-tight text-emerald-700">
                            {{ formatarValor(dados.totais.total_receitas) }}
                        </p>

                        <p class="mt-1 text-xs text-gray-400">
                            {{ dados.totais.qtd_receitas }} receita(s)
                        </p>
                    </div>

                    <div class="rounded-xl border border-red-100 bg-white p-5 shadow-sm">
                        <p class="text-sm font-medium text-gray-500">
                            Total de despesas
                        </p>

                        <p class="mt-2 text-2xl font-bold tracking-tight text-red-600">
                            {{ formatarValor(dados.totais.total_despesas) }}
                        </p>

                        <p class="mt-1 text-xs text-gray-400">
                            {{ dados.totais.qtd_despesas }} despesa(s)
                        </p>
                    </div>

                    <div
                        class="rounded-xl border bg-white p-5 shadow-sm"
                        :class="resultadoPositivo ? 'border-emerald-100' : 'border-red-100'"
                    >
                        <p class="text-sm font-medium text-gray-500">
                            Resultado financeiro
                        </p>

                        <p
                            class="mt-2 text-2xl font-bold tracking-tight"
                            :class="resultadoPositivo ? 'text-emerald-700' : 'text-red-600'"
                        >
                            {{ formatarValor(dados.totais.resultado) }}
                        </p>

                        <p class="mt-1 text-xs text-gray-400">
                            Receitas - Despesas
                        </p>
                    </div>
                </div>

                <!-- Tabela de receitas -->
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-200 px-6 py-4">
                        <h2 class="font-semibold text-gray-900">
                            Receitas
                        </h2>

                        <p class="mt-1 text-sm text-gray-500">
                            {{ dados.totais.qtd_receitas }} lançamento(s).
                        </p>
                    </div>

                    <div v-if="dados.receitas.length" class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                                <tr>
                                    <th class="px-6 py-3 font-semibold">
                                        Data
                                    </th>

                                    <th class="px-6 py-3 font-semibold">
                                        Descrição
                                    </th>

                                    <th class="px-6 py-3 font-semibold">
                                        Cliente
                                    </th>

                                    <th class="px-6 py-3 font-semibold">
                                        Categoria
                                    </th>

                                    <th class="px-6 py-3 font-semibold">
                                        Valor
                                    </th>

                                    <th class="px-6 py-3 font-semibold">
                                        Status
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-100">
                                <tr
                                    v-for="receita in dados.receitas"
                                    :key="receita.id"
                                    class="transition hover:bg-emerald-50/30"
                                >
                                    <td class="px-6 py-3 whitespace-nowrap text-sm font-medium text-gray-700">
                                        {{ formatarData(receita.data) }}
                                    </td>

                                    <td class="px-6 py-3">
                                        <p class="font-semibold text-gray-900">
                                            {{ receita.descricao }}
                                        </p>
                                    </td>

                                    <td class="px-6 py-3 text-sm text-gray-600">
                                        {{ receita.contraparte || '—' }}
                                    </td>

                                    <td class="px-6 py-3 text-sm text-gray-600">
                                        {{ receita.categoria || '—' }}
                                    </td>

                                    <td class="px-6 py-3 whitespace-nowrap text-sm font-bold text-emerald-700">
                                        {{ formatarValor(receita.valor_formatado) }}
                                    </td>

                                    <td class="px-6 py-3 whitespace-nowrap">
                                        <span
                                            class="rounded-md px-2.5 py-1 text-xs font-semibold"
                                            :class="classesStatus(receita.status)"
                                        >
                                            {{ etiquetaStatus(receita.status) }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-else class="flex flex-col items-center justify-center px-6 py-12 text-center">
                        <h3 class="font-semibold text-gray-900">
                            Nenhuma receita no período
                        </h3>

                        <p class="mt-1 max-w-sm text-sm text-gray-500">
                            Não há receitas cadastradas para o período selecionado.
                        </p>
                    </div>
                </div>

                <!-- Tabela de despesas -->
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-200 px-6 py-4">
                        <h2 class="font-semibold text-gray-900">
                            Despesas
                        </h2>

                        <p class="mt-1 text-sm text-gray-500">
                            {{ dados.totais.qtd_despesas }} lançamento(s).
                        </p>
                    </div>

                    <div v-if="dados.despesas.length" class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                                <tr>
                                    <th class="px-6 py-3 font-semibold">
                                        Data
                                    </th>

                                    <th class="px-6 py-3 font-semibold">
                                        Descrição
                                    </th>

                                    <th class="px-6 py-3 font-semibold">
                                        Fornecedor
                                    </th>

                                    <th class="px-6 py-3 font-semibold">
                                        Categoria
                                    </th>

                                    <th class="px-6 py-3 font-semibold">
                                        Valor
                                    </th>

                                    <th class="px-6 py-3 font-semibold">
                                        Status
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-100">
                                <tr
                                    v-for="despesa in dados.despesas"
                                    :key="despesa.id"
                                    class="transition hover:bg-red-50/30"
                                >
                                    <td class="px-6 py-3 whitespace-nowrap text-sm font-medium text-gray-700">
                                        {{ formatarData(despesa.data) }}
                                    </td>

                                    <td class="px-6 py-3">
                                        <p class="font-semibold text-gray-900">
                                            {{ despesa.descricao }}
                                        </p>
                                    </td>

                                    <td class="px-6 py-3 text-sm text-gray-600">
                                        {{ despesa.contraparte || '—' }}
                                    </td>

                                    <td class="px-6 py-3 text-sm text-gray-600">
                                        {{ despesa.categoria || '—' }}
                                    </td>

                                    <td class="px-6 py-3 whitespace-nowrap text-sm font-bold text-red-600">
                                        {{ formatarValor(despesa.valor_formatado) }}
                                    </td>

                                    <td class="px-6 py-3 whitespace-nowrap">
                                        <span
                                            class="rounded-md px-2.5 py-1 text-xs font-semibold"
                                            :class="classesStatus(despesa.status)"
                                        >
                                            {{ etiquetaStatus(despesa.status) }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-else class="flex flex-col items-center justify-center px-6 py-12 text-center">
                        <h3 class="font-semibold text-gray-900">
                            Nenhuma despesa no período
                        </h3>

                        <p class="mt-1 max-w-sm text-sm text-gray-500">
                            Não há despesas cadastradas para o período selecionado.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>