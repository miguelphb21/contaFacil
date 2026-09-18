<script setup lang="ts">
import type { NotificacaoItem } from '@/types'
import { router, usePage } from '@inertiajs/vue3'
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'

const INTERVALO_POLLING_MS = 60_000
const TEMPO_FLASH_MS = 10_000

interface EmpresaResumida {
    id: number
    razao_social: string
    nome_fantasia: string | null
}

interface PageProps {
    empresa_atual?: EmpresaResumida | null
    [key: string]: unknown
}

interface RespostaNotificacoes {
    novas: NotificacaoItem[]
    nao_lidas: number
    notificacoes: NotificacaoItem[]
}

const page = usePage<PageProps>()

const empresaAtiva = computed(() => page.props.empresa_atual ?? null)

const temEmpresa = computed(() => empresaAtiva.value !== null && empresaAtiva.value !== undefined)

const notificacoes = ref<NotificacaoItem[]>([])
const naoLidas = ref(0)
const flashItens = ref<NotificacaoItem[]>([])
const aberto = ref(false)
const elementoRaiz = ref<HTMLElement | null>(null)

let temporizadorFlash: number | undefined
let temporizadorPolling: number | undefined

const exibindoFlash = computed(() => flashItens.value.length > 0)

const exibirDot = computed(() => naoLidas.value > 0 && !exibindoFlash.value && !aberto.value)

const desligarFlash = () => {
    window.clearTimeout(temporizadorFlash)
    flashItens.value = []
}

const alternarPainel = () => {
    aberto.value = !aberto.value

    if (aberto.value) {
        marcarTodasLidas()
    }
}

const aoClicarFora = (evento: MouseEvent) => {
    if (!aberto.value) {
        return
    }

    if (elementoRaiz.value?.contains(evento.target as Node)) {
        return
    }

    aberto.value = false
}

const carregar = async () => {
    if (empresaAtiva.value?.id === undefined) {
        return
    }

    const resposta = await fetch(route('notificacoes.index'), {
        headers: { Accept: 'application/json' },
        credentials: 'same-origin',
    })

    if (!resposta.ok) {
        return
    }

    const dados = (await resposta.json()) as RespostaNotificacoes

    notificacoes.value = dados.notificacoes
    naoLidas.value = dados.nao_lidas

    if (dados.novas.length > 0) {
        exibirFlash(dados.novas)
    }
}

const exibirFlash = (novas: NotificacaoItem[]) => {
    flashItens.value = novas

    window.clearTimeout(temporizadorFlash)

    temporizadorFlash = window.setTimeout(() => {
        flashItens.value = []
    }, TEMPO_FLASH_MS)
}

const navegar = async (notificacao: NotificacaoItem) => {
    desligarFlash()

    await marcarTodasLidas()

    if (notificacao.lancamento === null) {
        return
    }

    const periodo = notificacao.lancamento.data.slice(0, 7)
    const rota = notificacao.lancamento.tipo === 'despesa' ? 'despesas.index' : 'receitas.index'

    router.visit(route(rota, { periodo }))
}

const marcarTodasLidas = async () => {
    const resposta = await fetch(route('notificacoes.marcar-lidas'), {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN':
                (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement | null)?.content ?? '',
        },
        credentials: 'same-origin',
    })

    if (!resposta.ok) {
        return
    }

    naoLidas.value = 0
    notificacoes.value = notificacoes.value.map((notificacao) => ({
        ...notificacao,
        lido: true,
    }))
}

onMounted(() => {
    carregar()
    document.addEventListener('click', aoClicarFora)
    temporizadorPolling = window.setInterval(carregar, INTERVALO_POLLING_MS)
})

onBeforeUnmount(() => {
    window.clearTimeout(temporizadorFlash)
    window.clearInterval(temporizadorPolling)
})
</script>

<template>
    <div v-if="temEmpresa" ref="elementoRaiz" class="relative">
        <button
            type="button"
            :aria-label="aberto ? 'Fechar notificações' : 'Abrir notificações'"
            class="relative flex h-10 w-10 items-center justify-center rounded-lg text-[#8ba899] transition hover:bg-white/10 hover:text-white"
            @click="alternarPainel"
        >
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M6 8a6 6 0 1 1 12 0c0 7 3 9 3 9H3s3-2 3-9z"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
                <path
                    d="M10.3 21a1.94 1.94 0 0 0 3.4 0"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
            </svg>

            <span
                v-if="exibirDot"
                class="absolute right-2 top-2 h-2.5 w-2.5 rounded-full bg-red-500 ring-2 ring-[#12201b]"
            />
        </button>

        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0 translate-x-2"
            enter-to-class="opacity-100 translate-x-0"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100 translate-x-0"
            leave-to-class="opacity-0 translate-x-2"
        >
            <div
                v-if="exibindoFlash"
                class="absolute left-full top-1/2 z-[60] ml-3 w-96 -translate-y-1/2 overflow-hidden rounded-xl border border-emerald-200 bg-white shadow-xl shadow-black/10"
            >
                <div class="flex items-center justify-between gap-2 border-b border-slate-100 bg-slate-50/70 px-4 py-3">
                    <div class="flex min-w-0 items-center gap-2.5">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-100">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M6 8a6 6 0 1 1 12 0c0 7 3 9 3 9H3s3-2 3-9z"
                                    stroke="#059669"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </div>

                        <div class="min-w-0">
                            <p class="truncate font-semibold text-slate-800">
                                {{ flashItens.length === 1 ? 'Nova notificação' : `${flashItens.length} novas notificações` }}
                            </p>
                            <p class="text-xs text-slate-400">Clique para abrir o lançamento</p>
                        </div>
                    </div>

                    <button
                        type="button"
                        aria-label="Fechar notificações"
                        class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-slate-400 transition hover:bg-white hover:text-slate-600"
                        @click="desligarFlash"
                    >
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M18 6 6 18M6 6l12 12"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                            />
                        </svg>
                    </button>
                </div>

                <div class="flex max-h-72 flex-col overflow-y-auto">
                    <button
                        v-for="nova in flashItens"
                        :key="nova.id"
                        type="button"
                        class="group flex w-full items-start gap-3 border-b border-slate-50 px-4 py-3 text-left transition last:border-0 hover:bg-slate-50"
                        @click="navegar(nova)"
                    >
                        <div
                            class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg"
                            :class="nova.lancamento?.tipo === 'receita' ? 'bg-emerald-100' : 'bg-rose-100'"
                        >
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    v-if="nova.lancamento?.tipo === 'receita'"
                                    d="M20 6 9 17l-5-5"
                                    stroke="#059669"
                                    stroke-width="2.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                                <path
                                    v-else
                                    d="M18 6 6 18M6 6l12 12"
                                    stroke="#e11d48"
                                    stroke-width="2.5"
                                    stroke-linecap="round"
                                />
                            </svg>
                        </div>

                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-slate-800">{{ nova.mensagem }}</p>
                            <p v-if="nova.lancamento" class="mt-0.5 text-xs text-slate-400">
                                {{ nova.lancamento.descricao }} · {{ nova.lancamento.valor }} ·
                                {{ nova.lancamento.data }}
                            </p>
                        </div>

                        <svg
                            class="mt-1 shrink-0 text-slate-300 transition group-hover:text-slate-500"
                            width="14"
                            height="14"
                            viewBox="0 0 24 24"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                d="m9 18 6-6-6-6"
                                stroke="currentColor"
                                stroke-width="2.2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </button>
                </div>
            </div>
        </Transition>

        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0 -translate-y-2"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-2"
        >
            <div
                v-if="aberto"
                class="absolute left-full top-full z-[60] mt-2 w-96 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl shadow-black/10"
            >
                <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                    <h3 class="font-semibold text-slate-800">Notificações</h3>
                    <span class="text-xs text-slate-400">Marcadas como lidas ao abrir</span>
                </div>

                <div class="max-h-80 overflow-y-auto">
                    <div v-if="notificacoes.length === 0" class="px-4 py-8 text-center text-sm text-slate-500">
                        Nenhuma notificação por aqui.
                    </div>

                    <button
                        v-for="notificacao in notificacoes"
                        :key="notificacao.id"
                        type="button"
                        class="group flex w-full items-start gap-3 border-b border-slate-50 px-4 py-3 text-left transition last:border-0 hover:bg-slate-50"
                        @click="navegar(notificacao)"
                    >
                        <div
                            class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg"
                            :class="notificacao.lancamento?.tipo === 'receita' ? 'bg-emerald-100' : 'bg-rose-100'"
                        >
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    v-if="notificacao.lancamento?.tipo === 'receita'"
                                    d="M20 6 9 17l-5-5"
                                    stroke="#059669"
                                    stroke-width="2.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                                <path
                                    v-else
                                    d="M18 6 6 18M6 6l12 12"
                                    stroke="#e11d48"
                                    stroke-width="2.5"
                                    stroke-linecap="round"
                                />
                            </svg>
                        </div>

                        <div class="min-w-0 flex-1">
                            <p
                                class="break-words text-sm"
                                :class="notificacao.lido ? 'text-slate-500' : 'font-semibold text-slate-800'"
                            >
                                {{ notificacao.mensagem }}
                            </p>

                            <p v-if="notificacao.lancamento" class="mt-0.5 text-xs text-slate-400">
                                {{ notificacao.lancamento.descricao }} · {{ notificacao.lancamento.valor }}
                                · {{ notificacao.lancamento.data }}
                            </p>
                        </div>

                        <span v-if="!notificacao.lido" class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-red-500" />
                    </button>
                </div>
            </div>
        </Transition>
    </div>
</template>