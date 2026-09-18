<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import type { Flash } from '@/types'

interface EmpresaResumida {
    id: number
    razao_social: string
    nome_fantasia: string | null
}

interface PageProps {
    flash: Flash
    [key: string]: unknown
}

defineProps<{
    empresas: EmpresaResumida[]
}>()

const page = usePage<PageProps>()

const selecionar = (id: number) => {
    router.post(route('empresas.selecionar.armazenar'), {
        empresa_id: id,
    })
}
</script>

<template>
    <Head :title="'Selecionar empresa'" />

    <AppLayout>
        <div
            class="flex min-h-screen items-center justify-center bg-[#f6f6f2] p-6"
        >
            <div class="w-full max-w-lg">
                <div
                    class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
                >
                    <div class="border-b border-slate-100 px-6 py-6 text-center">
                        <div
                            class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100 text-slate-600"
                        >
                            <svg
                                class="h-7 w-7"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path d="M4 21V5l8-2 8 2v16" />
                                <path d="M9 21v-4h6v4" />
                            </svg>
                        </div>

                        <h1
                            class="text-xl font-bold tracking-tight text-slate-900"
                        >
                            Selecione uma empresa
                        </h1>

                        <p class="mt-1 text-sm text-slate-500">
                            Escolha a empresa para trabalhar no financeiro.
                        </p>
                    </div>

                    <div class="space-y-3 p-6">
                        <button
                            v-for="empresa in empresas"
                            :key="empresa.id"
                            type="button"
                            @click="selecionar(empresa.id)"
                            class="flex w-full items-center gap-3 rounded-xl border border-slate-200 bg-white p-4 text-left transition hover:border-slate-300 hover:bg-slate-100/60"
                        >
                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-100 font-semibold text-slate-600"
                            >
                                {{ empresa.razao_social.charAt(0).toUpperCase() }}
                            </div>

                            <div class="min-w-0 flex-1">
                                <p class="font-semibold text-slate-900">
                                    {{ empresa.nome_fantasia || empresa.razao_social }}
                                </p>

                                <p class="truncate text-sm text-slate-400">
                                    {{ empresa.razao_social }}
                                </p>
                            </div>

                            <svg
                                class="h-5 w-5 text-slate-300"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path d="m9 18 6-6-6-6" />
                            </svg>
                        </button>

                        <Link
                            :href="route('empresas.create')"
                            class="flex w-full items-center justify-center gap-2 rounded-xl border border-dashed border-slate-300 bg-transparent p-4 text-sm font-semibold text-slate-600 transition hover:border-slate-400 hover:bg-slate-100"
                        >
                            <span class="text-lg leading-none">+</span>
                            Cadastrar nova empresa
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>