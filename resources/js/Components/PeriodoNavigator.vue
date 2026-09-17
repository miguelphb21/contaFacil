<script setup lang="ts">
import { router } from '@inertiajs/vue3'

interface Periodo {
    chave: string
    ano: number
    mes: number
    label: string
    anterior: string
    proximo: string
}

const props = defineProps<{
    periodo: Periodo
    rotaIndex: string
}>()

const MESES = [
    'Jan',
    'Fev',
    'Mar',
    'Abr',
    'Mai',
    'Jun',
    'Jul',
    'Ago',
    'Set',
    'Out',
    'Nov',
    'Dez',
]

const chaveDe = (ano: number, mes: number) => {
    return `${ano}-${String(mes).padStart(2, '0')}`
}

const navegar = (chave: string) => {
    router.get(props.rotaIndex, { periodo: chave }, { preserveScroll: true })
}

const navegarMes = (mes: number) => {
    navegar(chaveDe(props.periodo.ano, mes))
}

const navegarAno = (deslocamento: number) => {
    navegar(chaveDe(props.periodo.ano + deslocamento, props.periodo.mes))
}
</script>

<template>
    <div class="rounded-xl border border-gray-200 bg-white p-3 shadow-sm">
        <div class="flex items-center gap-2">
            <button
                type="button"
                @click="navegar(periodo.anterior)"
                class="flex h-10 w-10 items-center justify-center rounded-lg text-gray-500 transition hover:bg-emerald-50 hover:text-emerald-700"
                aria-label="Mês anterior"
            >
                <svg
                    class="h-5 w-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path d="m15 18-6-6 6-6" />
                </svg>
            </button>

            <div class="flex-1 text-center">
                <p class="text-lg font-bold tracking-tight text-gray-900">
                    {{ periodo.label }}
                </p>

                <p class="text-xs text-gray-400">
                    {{ periodo.ano }}
                </p>
            </div>

            <button
                type="button"
                @click="navegar(periodo.proximo)"
                class="flex h-10 w-10 items-center justify-center rounded-lg text-gray-500 transition hover:bg-emerald-50 hover:text-emerald-700"
                aria-label="Próximo mês"
            >
                <svg
                    class="h-5 w-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path d="m9 18 6-6-6-6" />
                </svg>
            </button>
        </div>

        <div class="mt-2 flex items-center justify-between gap-2 border-t border-gray-100 pt-2">
            <button
                type="button"
                @click="navegarAno(-1)"
                class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-emerald-50 hover:text-emerald-700"
                aria-label="Ano anterior"
            >
                <svg
                    class="h-4 w-4"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path d="m15 18-6-6 6-6" />
                </svg>
            </button>

            <p class="text-sm font-semibold text-gray-700">
                {{ periodo.ano }}
            </p>

            <button
                type="button"
                @click="navegarAno(1)"
                class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-emerald-50 hover:text-emerald-700"
                aria-label="Próximo ano"
            >
                <svg
                    class="h-4 w-4"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path d="m9 18 6-6-6-6" />
                </svg>
            </button>
        </div>

        <div class="mt-2 grid grid-cols-3 gap-1.5">
            <button
                v-for="(mes, indice) in MESES"
                :key="mes"
                type="button"
                @click="navegarMes(indice + 1)"
                class="flex h-9 items-center justify-center rounded-lg text-sm font-medium transition"
                :class="
                    indice + 1 === periodo.mes
                        ? 'bg-emerald-700 text-white'
                        : 'text-gray-600 hover:bg-emerald-50 hover:text-emerald-700'
                "
            >
                {{ mes }}
            </button>
        </div>
    </div>
</template>
