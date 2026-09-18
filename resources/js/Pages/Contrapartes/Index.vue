<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'
import FlashMessage from '@/Components/FlashMessage.vue'
import ContraparteForm from '@/Components/Financeiro/ContraparteForm.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import type { ContraparteItem, Flash } from '@/types'
import { computed, ref } from 'vue'

type ContraparteTipo = ContraparteItem['tipo']

interface PageProps {
    flash: Flash
    [key: string]: unknown
}

const props = defineProps<{
    contrapartes: ContraparteItem[]
    filtro: string | null
}>()

const page = usePage<PageProps>()

const aberto = ref(false)
const editando = ref<ContraparteItem | null>(null)

const abrirNovo = () => {
    editando.value = null
    aberto.value = true
}

const abrirEdicao = (contraparte: ContraparteItem) => {
    editando.value = contraparte
    aberto.value = true
}

const fechar = () => {
    aberto.value = false
    editando.value = null
}

const filtros: Array<{ valor: ContraparteTipo | null; label: string }> = [
    { valor: null, label: 'Todas' },
    { valor: 'fornecedor', label: 'Fornecedores' },
    { valor: 'cliente', label: 'Clientes' },
]

const filtroAtivo = computed<ContraparteTipo | null>(() => {
    if (props.filtro === 'fornecedor' || props.filtro === 'cliente') {
        return props.filtro
    }

    return null
})

const rotaFiltro = (tipo: ContraparteTipo | null) =>
    route('contrapartes.index', tipo ? { tipo } : {})

const inicial = (nome: string) => nome.trim().charAt(0).toUpperCase()

const excluir = (id: number) => {
    if (!confirm('Deseja realmente excluir esta contraparte?')) {
        return
    }

    router.delete(route('contrapartes.destroy', id), {
        preserveScroll: true,
    })
}
</script>

<template>
    <Head :title="'Contrapartes'" />

    <AppLayout>
        <div class="min-h-screen bg-[#f6f6f2] p-6 lg:p-8">
            <div class="mx-auto max-w-6xl">
                <div
                    class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <p class="text-sm font-medium text-slate-500">
                            Cadastros
                        </p>

                        <h1
                            class="mt-1 text-2xl font-bold tracking-tight text-slate-900"
                        >
                            Contrapartes
                        </h1>

                        <p class="mt-1 text-sm text-slate-500">
                            Fornecedores e clientes das suas operações.
                        </p>
                    </div>

                    <button
                        type="button"
                        @click="abrirNovo"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700"
                    >
                        <span class="text-lg leading-none">+</span>
                        Nova contraparte
                    </button>
                </div>

                <FlashMessage
                    :success="page.props.flash?.success"
                    :error="page.props.flash?.error"
                />

                <div class="mb-6 flex flex-col gap-3">
                    <div
                        class="w-full rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:max-w-xs"
                    >
                        <p class="text-sm font-medium text-slate-500">
                            Contrapartes cadastradas
                        </p>

                        <p class="mt-2 text-3xl font-bold text-slate-900">
                            {{ contrapartes.length }}
                        </p>
                    </div>

                    <div
                        class="inline-flex w-fit rounded-xl border border-slate-200 bg-white p-1 shadow-sm"
                    >
                        <Link
                            v-for="opcao in filtros"
                            :key="opcao.label"
                            :href="rotaFiltro(opcao.valor)"
                            class="rounded-lg px-4 py-2 text-sm font-medium transition"
                            :class="filtroAtivo === opcao.valor
                                ? 'bg-slate-900 text-white'
                                : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900'"
                        >
                            {{ opcao.label }}
                        </Link>
                    </div>
                </div>

                <div
                    class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
                >
                    <div class="border-b border-slate-200 px-6 py-5">
                        <h2 class="font-semibold text-slate-900">
                            Contrapartes cadastradas
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            {{ contrapartes.length }} contraparte(s).
                        </p>
                    </div>

                    <div v-if="contrapartes.length" class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead
                                class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500"
                            >
                                <tr>
                                    <th class="px-6 py-4 font-semibold">
                                        Contraparte
                                    </th>

                                    <th class="px-6 py-4 font-semibold">
                                        Tipo
                                    </th>

                                    <th class="px-6 py-4 font-semibold">
                                        CPF / CNPJ
                                    </th>

                                    <th class="px-6 py-4 font-semibold">
                                        Contato
                                    </th>

                                    <th class="px-6 py-4 text-right font-semibold">
                                        Ações
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-100">
                                <tr
                                    v-for="contraparte in contrapartes"
                                    :key="contraparte.id"
                                    class="transition hover:bg-slate-100/60"
                                >
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg text-sm font-semibold"
                                                :class="contraparte.tipo === 'fornecedor'
                                                    ? 'bg-slate-100 text-slate-600'
                                                    : 'bg-emerald-100 text-emerald-700'"
                                            >
                                                {{ inicial(contraparte.nome) }}
                                            </div>

                                            <div class="min-w-0">
                                                <p class="truncate font-semibold text-slate-900">
                                                    {{ contraparte.nome }}
                                                </p>

                                                <p
                                                    v-if="contraparte.endereco"
                                                    class="truncate text-xs text-slate-400"
                                                >
                                                    {{ contraparte.endereco }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="rounded-md px-2.5 py-1 text-xs font-semibold"
                                            :class="contraparte.tipo === 'fornecedor'
                                                ? 'bg-slate-100 text-slate-600'
                                                : 'bg-emerald-100 text-emerald-700'"
                                        >
                                            {{ contraparte.tipo === 'fornecedor' ? 'Fornecedor' : 'Cliente' }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                        {{ contraparte.cpf_cnpj || '—' }}
                                    </td>

                                    <td class="max-w-xs px-6 py-4 text-sm text-slate-500">
                                        <p>{{ contraparte.telefone || 'Sem telefone' }}</p>

                                        <p class="truncate text-xs text-slate-400">
                                            {{ contraparte.email || '' }}
                                        </p>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex justify-end gap-2">
                                            <button
                                                type="button"
                                                @click="abrirEdicao(contraparte)"
                                                class="rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-900"
                                            >
                                                Editar
                                            </button>

                                            <button
                                                type="button"
                                                @click="excluir(contraparte.id)"
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
                            class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-slate-400"
                        >
                            <svg
                                class="h-8 w-8"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path d="M3 17h18" />
                                <path d="M5 17V8h10l4 4v5" />
                                <circle cx="7" cy="17" r="2" />
                                <circle cx="17" cy="17" r="2" />
                            </svg>
                        </div>

                        <h3 class="mt-4 font-semibold text-slate-900">
                            Nenhuma contraparte cadastrada
                        </h3>

                        <p class="mt-1 max-w-sm text-sm text-slate-500">
                            Cadastre fornecedores e clientes para usar nos seus
                            lançamentos.
                        </p>

                        <button
                            type="button"
                            @click="abrirNovo"
                            class="mt-5 rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700"
                        >
                            Cadastrar contraparte
                        </button>
                    </div>
                </div>

                <ContraparteForm
                    :aberto="aberto"
                    :contraparte="editando"
                    @fechado="fechar"
                />
            </div>
        </div>
    </AppLayout>
</template>