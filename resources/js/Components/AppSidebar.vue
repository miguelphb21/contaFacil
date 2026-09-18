<script setup lang="ts">
import AppNotificationBell from '@/Components/AppNotificationBell.vue'
import EmpresaSwitcher from '@/Components/EmpresaSwitcher.vue'
import { Link, useForm, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

interface EmpresaResumida {
    id: number
    razao_social: string
    nome_fantasia: string | null
}

interface Usuario {
    name: string
    email: string
    [key: string]: unknown
}

interface PageProps {
    auth?: { user?: Usuario | null }
    empresas: EmpresaResumida[]
    empresa_atual: EmpresaResumida | null
    [key: string]: unknown
}

const page = usePage<PageProps>()

const url = computed(() => page.url)

const isActive = (prefix: string) =>
    url.value === prefix || url.value.startsWith(`${prefix}/`)

const empresas = computed(() => page.props.empresas ?? [])

const empresaAtual = computed(() => page.props.empresa_atual ?? null)

const usuario = computed(() => page.props.auth?.user ?? null)

const inicial = computed(
    () => usuario.value?.name?.trim().charAt(0).toUpperCase() ?? 'U',
)

const logout = useForm({})

const sair = () => {
    logout.post(route('logout'), {
        preserveScroll: true,
    })
}
</script>

<template>
    <aside
        class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col border-r border-[#22392f] bg-[#12201b] text-[#e9f2ed]"
    >
        <!-- Logo -->
        <div class="flex h-20 items-center justify-between border-b border-[#22392f] px-6">
            <Link
                :href="route('dashboard')"
                class="flex items-center gap-3"
            >
                <svg width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <!-- Fundo com cantos arredondados -->
                    <rect width="36" height="36" rx="10" fill="#10B981"/>

                    <!-- Barra de Despesa/Menor -->
                    <path d="M11 23V17" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>

                    <!-- Barra Intermediária -->
                    <path d="M17 23V13" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>

                    <!-- Barra de Receita formando o "Check" (Fácil) -->
                    <path d="M23 23L28 12" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M23 23L20.5 19" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>

                <div>
                    <h1 class="font-figtree text-xl font-bold tracking-tight text-white">
                        Conta<span class="text-emerald-400">Fácil</span>
                    </h1>

                    <p class="text-xs text-[#8ba899]">
                        Gestão financeira
                    </p>
                </div>
            </Link>

            <AppNotificationBell />
        </div>

        <!-- Empresa ativa -->
        <div class="border-b border-[#404040] px-4 py-4">
            <EmpresaSwitcher
                :empresa-atual="empresaAtual"
                :empresas="empresas"
            />
        </div>

        <!-- Navegação -->
        <nav class="flex-1 overflow-y-auto px-4 py-6">
            <div>
                <p
                    class="mb-3 px-3 text-[11px] font-semibold uppercase tracking-wider text-[#7f9a8c]"
                >
                    Principal
                </p>

                <div class="space-y-1">
                    <Link
                        :href="route('dashboard')"
                        class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold transition"
                        :class="isActive('/dashboard')
                            ? 'bg-[#244034] text-white'
                            : 'font-medium text-[#9bb2a6] hover:bg-[#1e352b] hover:text-white'"
                    >
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path d="M3 10.5 12 3l9 7.5" />
                            <path d="M5 9.5V21h14V9.5" />
                            <path d="M9 21v-6h6v6" />
                        </svg>

                        <span>Dashboard</span>
                    </Link>

                    <Link
                        :href="route('contrapartes.index')"
                        class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm transition"
                        :class="isActive('/contrapartes')
                            ? 'bg-[#244034] font-semibold text-white'
                            : 'font-medium text-[#9bb2a6] hover:bg-[#1e352b] hover:text-white'"
                    >
                        <svg
                            class="h-5 w-5"
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

                        <span>Contrapartes</span>
                    </Link>

                    <Link
                        :href="route('categorias.index')"
                        class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm transition"
                        :class="isActive('/categorias')
                            ? 'bg-[#244034] font-semibold text-white'
                            : 'font-medium text-[#9bb2a6] hover:bg-[#1e352b] hover:text-white'"
                    >
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                d="M20 13 11 22l-9-9V4a2 2 0 0 1 2-2h9l9 9-2 2Z"
                            />
                            <circle cx="7" cy="7" r="1" />
                        </svg>

                        <span>Categorias</span>
                    </Link>
                </div>
            </div>

            <div class="mt-8">
                <p
                    class="mb-3 px-3 text-[11px] font-semibold uppercase tracking-wider text-[#7f9a8c]"
                >
                    Financeiro
                </p>

                <div class="space-y-1">
                    <Link
                        :href="route('receitas.index')"
                        class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm transition"
                        :class="isActive('/receitas')
                            ? 'bg-[#244034] font-semibold text-white'
                            : 'font-medium text-[#9bb2a6] hover:bg-[#1e352b] hover:text-white'"
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

                        <span>Receitas</span>
                    </Link>

                    <Link
                        :href="route('despesas.index')"
                        class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm transition"
                        :class="isActive('/despesas')
                            ? 'bg-[#244034] font-semibold text-white'
                            : 'font-medium text-[#9bb2a6] hover:bg-[#1e352b] hover:text-white'"
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

                        <span>Despesas</span>
                    </Link>

                    <Link
                        :href="route('relatorios.index')"
                        class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm transition"
                        :class="isActive('/relatorios')
                            ? 'bg-[#244034] font-semibold text-white'
                            : 'font-medium text-[#9bb2a6] hover:bg-[#1e352b] hover:text-white'"
                    >
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path d="M8 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2h-1" />
                            <path d="M8 5h8v4H8z" />
                            <path d="M8 13h8" />
                            <path d="M8 17h5" />
                        </svg>

                        <span>Relatórios</span>
                    </Link>
                </div>
            </div>

            <div class="mt-8">
                <p
                    class="mb-3 px-3 text-[11px] font-semibold uppercase tracking-wider text-[#7f9a8c]"
                >
                    Configurações
                </p>

                <div class="space-y-1">
                    <Link
                        :href="route('empresas.index')"
                        class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm transition"
                        :class="isActive('/empresas')
                            ? 'bg-[#244034] font-semibold text-white'
                            : 'font-medium text-[#9bb2a6] hover:bg-[#1e352b] hover:text-white'"
                    >
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path d="M4 21V5l8-2 8 2v16" />
                            <path d="M8 7h2" />
                            <path d="M14 7h2" />
                            <path d="M8 11h2" />
                            <path d="M14 11h2" />
                            <path d="M9 21v-4h6v4" />
                        </svg>

                        <span>Empresas</span>
                    </Link>
                </div>
            </div>
        </nav>

        <!-- Rodapé -->
        <div class="border-t border-[#22392f] p-4">
            <div class="mb-3 flex items-center gap-3 rounded-xl bg-[#182a22] p-3">
                <div
                    class="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-600 font-semibold text-white"
                >
                    {{ inicial }}
                </div>

                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-[#e9f2ed]">
                        {{ usuario?.name }}
                    </p>

                    <p class="truncate text-xs text-[#8ba899]">
                        {{ usuario?.email }}
                    </p>
                </div>
            </div>

            <button
                type="button"
                @click="sair"
                class="group flex w-full items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium text-[#9bb2a6] transition hover:bg-red-500/10 hover:text-red-300"
            >
                <svg
                    class="h-5 w-5 text-[#83a091] transition group-hover:text-red-300"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path d="M10 17l5-5-5-5" />
                    <path d="M15 12H3" />
                    <path d="M21 19V5a2 2 0 0 0-2-2h-6" />
                </svg>

                <span>Sair</span>
            </button>
        </div>
    </aside>
</template>
