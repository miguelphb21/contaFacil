<script setup lang="ts">
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
        class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col border-r border-gray-200 bg-white text-gray-800"
    >
        <!-- Logo -->
        <div class="flex h-20 items-center border-b border-gray-100 px-6">
            <Link
                :href="route('dashboard')"
                class="flex items-center gap-3"
            >
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700"
                >
                    <svg
                        class="h-6 w-6"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="M12 22V12" />
                        <path d="M12 12C7 12 4 9 4 4c5 0 8 3 8 8Z" />
                        <path d="M12 17c0-5 3-8 8-8 0 5-3 8-8 8Z" />
                    </svg>
                </div>

                <div>
                    <h1 class="text-xl font-bold tracking-tight text-gray-900">
                        Conta<span class="text-emerald-700">Fácil</span>
                    </h1>

                    <p class="text-xs text-gray-400">
                        Gestão financeira
                    </p>
                </div>
            </Link>
        </div>

        <!-- Empresa ativa -->
        <div class="border-b border-gray-100 px-4 py-4">
            <EmpresaSwitcher
                :empresa-atual="empresaAtual"
                :empresas="empresas"
            />
        </div>

        <!-- Navegação -->
        <nav class="flex-1 overflow-y-auto px-4 py-6">
            <div>
                <p
                    class="mb-3 px-3 text-[11px] font-semibold uppercase tracking-wider text-gray-400"
                >
                    Principal
                </p>

                <div class="space-y-1">
                    <Link
                        :href="route('dashboard')"
                        class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold transition"
                        :class="isActive('/dashboard')
                            ? 'bg-emerald-50 text-emerald-700'
                            : 'font-medium text-gray-600 hover:bg-emerald-50 hover:text-emerald-700'"
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
                            ? 'bg-emerald-50 font-semibold text-emerald-700'
                            : 'font-medium text-gray-600 hover:bg-emerald-50 hover:text-emerald-700'"
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
                            ? 'bg-emerald-50 font-semibold text-emerald-700'
                            : 'font-medium text-gray-600 hover:bg-emerald-50 hover:text-emerald-700'"
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
                    class="mb-3 px-3 text-[11px] font-semibold uppercase tracking-wider text-gray-400"
                >
                    Financeiro
                </p>

                <div class="space-y-1">
                    <Link
                        :href="route('receitas.index')"
                        class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm transition"
                        :class="isActive('/receitas')
                            ? 'bg-emerald-50 font-semibold text-emerald-700'
                            : 'font-medium text-gray-600 hover:bg-emerald-50 hover:text-emerald-700'"
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
                            ? 'bg-emerald-50 font-semibold text-emerald-700'
                            : 'font-medium text-gray-600 hover:bg-emerald-50 hover:text-emerald-700'"
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
                        :href="route('recorrencias.index')"
                        class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm transition"
                        :class="isActive('/recorrencias')
                            ? 'bg-emerald-50 font-semibold text-emerald-700'
                            : 'font-medium text-gray-600 hover:bg-emerald-50 hover:text-emerald-700'"
                    >
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <rect
                                x="4"
                                y="3"
                                width="16"
                                height="18"
                                rx="2"
                            />
                            <path d="M8 7h8" />
                            <path d="M8 11h8" />
                            <path d="M8 15h5" />
                        </svg>

                        <span>Recorrências</span>
                    </Link>

                    <Link
                        :href="route('relatorios.index')"
                        class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm transition"
                        :class="isActive('/relatorios')
                            ? 'bg-emerald-50 font-semibold text-emerald-700'
                            : 'font-medium text-gray-600 hover:bg-emerald-50 hover:text-emerald-700'"
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
                    class="mb-3 px-3 text-[11px] font-semibold uppercase tracking-wider text-gray-400"
                >
                    Configurações
                </p>

                <div class="space-y-1">
                    <Link
                        :href="route('empresas.index')"
                        class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm transition"
                        :class="isActive('/empresas')
                            ? 'bg-emerald-50 font-semibold text-emerald-700'
                            : 'font-medium text-gray-600 hover:bg-emerald-50 hover:text-emerald-700'"
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
        <div class="border-t border-gray-100 p-4">
            <div class="mb-3 flex items-center gap-3 rounded-xl bg-gray-50 p-3">
                <div
                    class="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-600 font-semibold text-white"
                >
                    {{ inicial }}
                </div>

                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-gray-800">
                        {{ usuario?.name }}
                    </p>

                    <p class="truncate text-xs text-gray-400">
                        {{ usuario?.email }}
                    </p>
                </div>
            </div>

            <button
                type="button"
                @click="sair"
                class="group flex w-full items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium text-gray-500 transition hover:bg-red-50 hover:text-red-600"
            >
                <svg
                    class="h-5 w-5 text-gray-400 transition group-hover:text-red-500"
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