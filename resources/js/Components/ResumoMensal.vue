<script setup lang="ts">
import { computed } from 'vue'

interface Resumo {
    total_receitas: string
    total_despesas: string
    saldo: string
    pagas: string
    pendentes: string
}

const props = defineProps<{
    resumo: Resumo
}>()

const saldoPositivo = computed(() => {
    const numero = Number(props.resumo.saldo.replace(/\./g, '').replace(',', '.'))

    return !Number.isNaN(numero) && numero >= 0
})
</script>

<template>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-emerald-100 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-500">
                    Receitas
                </p>

                <div
                    class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700"
                >
                    <svg
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="M22 7 9 20l-7-7" />
                        <path d="m22 7-8.5 1.8" />
                        <path d="M22 7 20.2 15.5" />
                    </svg>
                </div>
            </div>

            <p class="mt-3 text-2xl font-bold tracking-tight text-emerald-700">
                {{ resumo.total_receitas }}
            </p>
        </div>

        <div class="rounded-xl border border-red-100 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-500">
                    Despesas
                </p>

                <div
                    class="flex h-9 w-9 items-center justify-center rounded-lg bg-red-50 text-red-600"
                >
                    <svg
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="M2 7 15 20l7-7L22 7z" />
                        <path d="m2 7 8.5 1.8L22 7" />
                        <path d="M2 7l1.8 8.5" />
                    </svg>
                </div>
            </div>

            <p class="mt-3 text-2xl font-bold tracking-tight text-red-600">
                {{ resumo.total_despesas }}
            </p>
        </div>

        <div
            class="rounded-xl border bg-white p-5 shadow-sm"
            :class="saldoPositivo ? 'border-emerald-100' : 'border-red-100'"
        >
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-500">
                    Saldo
                </p>

                <div
                    class="flex h-9 w-9 items-center justify-center rounded-lg"
                    :class="saldoPositivo
                        ? 'bg-emerald-50 text-emerald-700'
                        : 'bg-red-50 text-red-600'"
                >
                    <svg
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <rect x="3" y="5" width="18" height="14" rx="2" />
                        <path d="M3 10h18M7 15h4" />
                    </svg>
                </div>
            </div>

            <p
                class="mt-3 text-2xl font-bold tracking-tight"
                :class="saldoPositivo ? 'text-emerald-700' : 'text-red-600'"
            >
                {{ resumo.saldo }}
            </p>

            <p class="mt-1 text-xs text-gray-400">
                Pagas {{ resumo.pagas }} / Pendentes {{ resumo.pendentes }}
            </p>
        </div>
    </div>
</template>