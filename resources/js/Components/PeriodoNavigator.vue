<script setup lang="ts">
import { computed, nextTick, ref, watch } from 'vue'
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
    filtroData?: string | null
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

const navegarPeriodo = (chave: string, data: string | null = null) => {
    const parametros: Record<string, string> = { periodo: chave }

    if (data !== null) {
        parametros.data = data
    }

    router.get(props.rotaIndex, parametros, { preserveScroll: true })
}

const selecionarMes = (mes: number) => {
    navegarPeriodo(chaveDe(props.periodo.ano, mes))
}

const irParaMesAnterior = () => {
    navegarPeriodo(props.periodo.anterior)
}

const irParaProximoMes = () => {
    navegarPeriodo(props.periodo.proximo)
}

const navegarAno = (deslocamento: number) => {
    navegarPeriodo(chaveDe(props.periodo.ano + deslocamento, props.periodo.mes))
}

const mesesRef = ref<HTMLElement | null>(null)

const revelarMesSelecionado = () => {
    nextTick(() => {
        const ativo = mesesRef.value?.querySelector<HTMLElement>(
            '[data-ativo="true"]',
        )

        ativo?.scrollIntoView({
            behavior: 'smooth',
            block: 'nearest',
            inline: 'center',
        })
    })
}

watch(() => props.periodo.chave, revelarMesSelecionado, { immediate: true })

const mostrarCalendario = ref(false)
const dataEscolhida = ref(props.filtroData ?? '')

watch(
    () => props.filtroData,
    (valor) => {
        dataEscolhida.value = valor ?? ''
    },
)

const aplicarData = (evento: Event) => {
    const valor = (evento.target as HTMLInputElement).value

    if (valor === '') {
        return
    }

    mostrarCalendario.value = false

    navegarPeriodo(valor.slice(0, 7), valor)
}

const limparData = () => {
    dataEscolhida.value = ''
    mostrarCalendario.value = false

    navegarPeriodo(props.periodo.chave)
}

const dataFormatada = computed(() => {
    if (!props.filtroData) {
        return ''
    }

    const [ano, mes, dia] = props.filtroData.split('-')

    return `${dia}/${mes}/${ano}`
})
</script>

<template>
    <div class="space-y-2.5">
        <div
            class="rounded-xl border border-slate-200 bg-white p-2.5 shadow-sm sm:p-3"
        >
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-1">
                    <button
                        type="button"
                        @click="navegarAno(-1)"
                        class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 sm:h-9 sm:w-9"
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

                    <span
                        class="min-w-[3.5rem] text-center text-sm font-bold tracking-tight text-slate-900"
                    >
                        {{ periodo.ano }}
                    </span>

                    <button
                        type="button"
                        @click="navegarAno(1)"
                        class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 sm:h-9 sm:w-9"
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

                <div class="relative">
                    <button
                        type="button"
                        @click="mostrarCalendario = !mostrarCalendario"
                        class="flex items-center justify-center gap-1.5 rounded-lg border px-2.5 py-1.5 text-sm font-semibold transition sm:gap-2 sm:px-3 sm:py-2"
                        :class="
                            filtroData
                                ? 'border-slate-900 text-slate-900'
                                : 'border-slate-200 text-slate-600 hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900'
                        "
                    >
                        <svg
                            class="h-4 w-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path d="M8 2v4M16 2v4M3 10h18" />
                            <rect width="18" height="18" x="3" y="4" rx="2" />
                        </svg>

                        Selecionar data
                    </button>

                    <div
                        v-if="mostrarCalendario"
                        class="absolute right-0 z-30 mt-2 w-60 rounded-xl border border-slate-200 bg-white p-4 shadow-xl sm:w-72"
                    >
                        <p
                            class="text-[11px] font-semibold uppercase tracking-wider text-slate-400"
                        >
                            Data específica
                        </p>

                        <input
                            v-model="dataEscolhida"
                            type="date"
                            @change="aplicarData"
                            class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-slate-900 focus:ring-2 focus:ring-slate-100"
                        />

                        <div class="mt-3 flex items-center justify-between">
                            <button
                                v-if="filtroData"
                                type="button"
                                @click="limparData"
                                class="text-xs font-semibold text-slate-500 transition hover:text-slate-900"
                            >
                                Limpar filtro
                            </button>

                            <span v-else></span>

                            <button
                                type="button"
                                @click="mostrarCalendario = false"
                                class="text-xs font-semibold text-slate-500 transition hover:text-slate-900"
                            >
                                Fechar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-2 flex items-center gap-1">
                <button
                    type="button"
                    @click="irParaMesAnterior"
                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 sm:h-9 sm:w-9"
                    aria-label="Mês anterior"
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

                <div
                    ref="mesesRef"
                    class="flex flex-1 items-center gap-1 overflow-x-auto [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
                >
                    <button
                        v-for="(mes, indice) in MESES"
                        :key="mes"
                        type="button"
                        :data-ativo="indice + 1 === periodo.mes"
                        @click="selecionarMes(indice + 1)"
                        class="flex h-8 min-w-[2.75rem] shrink-0 items-center justify-center rounded-lg px-2 text-xs font-medium transition sm:h-9 sm:min-w-[3.25rem] sm:px-3 sm:text-sm"
                        :class="
                            indice + 1 === periodo.mes
                                ? 'bg-slate-900 text-white shadow-sm'
                                : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'
                        "
                    >
                        {{ mes }}
                    </button>
                </div>

                <button
                    type="button"
                    @click="irParaProximoMes"
                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 sm:h-9 sm:w-9"
                    aria-label="Próximo mês"
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
        </div>

        <div
            v-if="filtroData"
            class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white px-4 py-2.5 shadow-sm"
        >
            <p class="text-sm text-slate-600">
                <span class="font-semibold text-slate-900">Data:</span>
                {{ dataFormatada }}
            </p>

            <button
                type="button"
                @click="limparData"
                class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-semibold text-slate-500 transition hover:bg-slate-100 hover:text-slate-900"
            >
                <svg
                    class="h-3.5 w-3.5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path d="M18 6 6 18M6 6l12 12" />
                </svg>

                Limpar
            </button>
        </div>
    </div>
</template>