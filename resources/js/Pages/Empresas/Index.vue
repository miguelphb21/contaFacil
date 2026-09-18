<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'
import FlashMessage from '@/Components/FlashMessage.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import type { Flash } from '@/types'

interface Empresa {
    id: number
    razao_social: string
    nome_fantasia: string | null
    cnpj: string
}

interface EmpresaResumida {
    id: number
    razao_social: string
    nome_fantasia: string | null
}

interface PageProps {
    flash: Flash
    empresas: EmpresaResumida[]
    empresa_atual: EmpresaResumida | null
    [key: string]: unknown
}

const props = defineProps<{
    empresas: Empresa[]
}>()

const page = usePage<PageProps>()

const excluirEmpresa = (id: number) => {
    if (!confirm('Deseja realmente excluir esta empresa?')) {
        return
    }

    router.delete(route('empresas.destroy', id), {
        preserveScroll: true,
    })
}

const selecionar = (id: number) => {
    if (id === page.props.empresa_atual?.id) {
        return
    }

    router.post(route('empresas.selecionar.armazenar'), {
        empresa_id: id,
    })
}
</script>

<template>
    <Head :title="'Empresas'" />

    <AppLayout>
        <div class="min-h-screen bg-[#f6f6f2] p-6 lg:p-8">
            <div class="mx-auto max-w-6xl">
                <div
                    class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <p class="text-sm font-medium text-slate-500">
                            Configurações
                        </p>

                        <h1
                            class="mt-1 text-2xl font-bold tracking-tight text-slate-900"
                        >
                            Empresas
                        </h1>

                        <p class="mt-1 text-sm text-slate-500">
                            Gerencie as empresas do seu escritório.
                        </p>
                    </div>

                    <Link
                        :href="route('empresas.create')"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700"
                    >
                        <span class="text-lg leading-none">+</span>
                        Nova empresa
                    </Link>
                </div>

                <FlashMessage
                    :success="page.props.flash?.success"
                    :error="page.props.flash?.error"
                />

                <div class="mb-6">
                    <div
                        class="w-full rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:max-w-xs"
                    >
                        <p class="text-sm font-medium text-slate-500">
                            Empresas do escritório
                        </p>

                        <p class="mt-2 text-3xl font-bold text-slate-900">
                            {{ empresas.length }}
                        </p>
                    </div>
                </div>

                <div
                    class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
                >
                    <div class="border-b border-slate-200 px-6 py-5">
                        <h2 class="font-semibold text-slate-900">
                            Empresas cadastradas
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Selecione a empresa para trabalhar no financeiro.
                        </p>
                    </div>

                    <div v-if="empresas.length" class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead
                                class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500"
                            >
                                <tr>
                                    <th class="px-6 py-4 font-semibold">
                                        Empresa
                                    </th>

                                    <th class="px-6 py-4 font-semibold">
                                        CNPJ
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
                                    v-for="empresa in empresas"
                                    :key="empresa.id"
                                    class="transition hover:bg-slate-100/60"
                                    :class="page.props.empresa_atual?.id === empresa.id ? 'bg-slate-100/60' : ''"
                                >
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-100 font-semibold text-slate-600"
                                            >
                                                {{ empresa.razao_social.charAt(0).toUpperCase() }}
                                            </div>

                                            <div class="min-w-0">
                                                <p class="font-semibold text-slate-900">
                                                    {{ empresa.razao_social }}
                                                </p>

                                                <p class="text-xs text-slate-400">
                                                    {{ empresa.nome_fantasia || 'Sem nome fantasia' }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="rounded-md bg-slate-100 px-2.5 py-1 font-mono text-xs text-slate-700"
                                        >
                                            {{ empresa.cnpj }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            v-if="page.props.empresa_atual?.id === empresa.id"
                                            class="rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700"
                                        >
                                            Ativa
                                        </span>

                                        <span
                                            v-else
                                            class="rounded-md bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500"
                                        >
                                            Inativa
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex justify-end gap-2">
                                            <button
                                                v-if="page.props.empresa_atual?.id !== empresa.id"
                                                type="button"
                                                @click="selecionar(empresa.id)"
                                                class="rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-900"
                                            >
                                                Selecionar
                                            </button>

                                            <button
                                                type="button"
                                                @click="excluirEmpresa(empresa.id)"
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
                        <h3 class="font-semibold text-slate-900">
                            Nenhuma empresa cadastrada
                        </h3>

                        <p class="mt-1 max-w-sm text-sm text-slate-500">
                            Cadastre sua primeira empresa para começar.
                        </p>

                        <Link
                            :href="route('empresas.create')"
                            class="mt-5 rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700"
                        >
                            Cadastrar empresa
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>