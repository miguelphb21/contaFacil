<script setup lang="ts">
import { ref } from 'vue';

const contasOpen = ref(true);
const relatoriosOpen = ref(false);

const menu = [
    {
        label: 'Dashboard',
        href: '/dashboard',
        icon: 'dashboard',
    },
    {
        label: 'Empresas',
        href: '/empresas',
        icon: 'building',
    },
    {
        label: 'Fornecedores',
        href: '/fornecedores',
        icon: 'truck',
    },
    {
        label: 'Categorias',
        href: '/categorias',
        icon: 'tag',
    },
];
</script>

<template>
    <aside
        class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col border-r border-emerald-900/40 bg-[#063b2c] text-white"
    >
        <!-- Logo -->
        <div class="flex h-20 items-center border-b border-white/10 px-6">
            <a href="/dashboard" class="flex items-center gap-3">
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/15 text-emerald-400"
                >
                    <svg
                        class="h-7 w-7"
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
                    <h1 class="text-xl font-bold tracking-tight">
                        Pague<span class="text-emerald-400">Conta</span>
                    </h1>

                    <p class="text-xs text-emerald-100/50">
                        Gestão financeira
                    </p>
                </div>
            </a>
        </div>

        <!-- Navegação -->
        <nav class="flex-1 overflow-y-auto px-4 py-6">
            <!-- Principal -->
            <div>
                <p
                    class="mb-3 px-3 text-[11px] font-semibold uppercase tracking-wider text-emerald-100/40"
                >
                    Principal
                </p>

                <div class="space-y-1">
                    <!-- Dashboard -->
                    <a
                        href="/dashboard"
                        class="group flex items-center gap-3 rounded-xl bg-emerald-500 px-3 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-950/20 transition hover:bg-emerald-400"
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
                    </a>

                    <!-- Empresas -->
                    <a
                        href="/empresas"
                        class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium text-emerald-50/75 transition hover:bg-white/5 hover:text-white"
                    >
                        <svg
                            class="h-5 w-5 text-emerald-300/70 transition group-hover:text-emerald-300"
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

                        <span class="flex-1">Empresas</span>

                        <span
                            class="rounded-md bg-white/5 px-2 py-0.5 text-[10px] text-emerald-100/50"
                        >
                            15
                        </span>
                    </a>
                </div>
            </div>

            <!-- Financeiro -->
            <div class="mt-8">
                <p
                    class="mb-3 px-3 text-[11px] font-semibold uppercase tracking-wider text-emerald-100/40"
                >
                    Financeiro
                </p>

                <div class="space-y-1">
                    <!-- Contas -->
                    <button
                        type="button"
                        @click="contasOpen = !contasOpen"
                        class="group flex w-full items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium text-emerald-50/75 transition hover:bg-white/5 hover:text-white"
                    >
                        <svg
                            class="h-5 w-5 text-emerald-300/70 group-hover:text-emerald-300"
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

                        <span class="flex-1 text-left">Contas</span>

                        <svg
                            class="h-4 w-4 transition-transform duration-200"
                            :class="{ 'rotate-180': contasOpen }"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </button>

                    <!-- Submenu Contas -->
                    <div
                        v-show="contasOpen"
                        class="ml-5 space-y-1 border-l border-emerald-300/15 pl-4"
                    >
                        <a
                            href="/contas"
                            class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-emerald-100/60 transition hover:bg-white/5 hover:text-white"
                        >
                            <span
                                class="h-1.5 w-1.5 rounded-full bg-emerald-400"
                            ></span>
                            Por empresa
                        </a>

                        <a
                            href="/contas/todas"
                            class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-emerald-100/60 transition hover:bg-white/5 hover:text-white"
                        >
                            <span
                                class="h-1.5 w-1.5 rounded-full bg-emerald-400"
                            ></span>
                            Todas as empresas
                        </a>

                        <a
                            href="/contas/nova"
                            class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-emerald-100/60 transition hover:bg-white/5 hover:text-white"
                        >
                            <span
                                class="flex h-4 w-4 items-center justify-center rounded border border-emerald-400/40 text-[10px] text-emerald-300"
                            >
                                +
                            </span>
                            Nova conta
                        </a>
                    </div>

                    <!-- Fornecedores -->
                    <a
                        href="/fornecedores"
                        class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium text-emerald-50/75 transition hover:bg-white/5 hover:text-white"
                    >
                        <svg
                            class="h-5 w-5 text-emerald-300/70 group-hover:text-emerald-300"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                d="M3 17h18"
                            />
                            <path
                                d="M5 17V8h10l4 4v5"
                            />
                            <circle cx="7" cy="17" r="2" />
                            <circle cx="17" cy="17" r="2" />
                        </svg>

                        <span>Fornecedores</span>
                    </a>

                    <!-- Categorias -->
                    <a
                        href="/categorias"
                        class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium text-emerald-50/75 transition hover:bg-white/5 hover:text-white"
                    >
                        <svg
                            class="h-5 w-5 text-emerald-300/70 group-hover:text-emerald-300"
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
                    </a>
                </div>
            </div>

            <!-- Relatórios -->
            <div class="mt-8">
                <p
                    class="mb-3 px-3 text-[11px] font-semibold uppercase tracking-wider text-emerald-100/40"
                >
                    Análises
                </p>

                <button
                    type="button"
                    @click="relatoriosOpen = !relatoriosOpen"
                    class="group flex w-full items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium text-emerald-50/75 transition hover:bg-white/5 hover:text-white"
                >
                    <svg
                        class="h-5 w-5 text-emerald-300/70 group-hover:text-emerald-300"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="M4 19V5" />
                        <path d="M4 19h16" />
                        <path d="m7 15 4-5 3 3 5-7" />
                    </svg>

                    <span class="flex-1 text-left">Relatórios</span>

                    <svg
                        class="h-4 w-4 transition-transform duration-200"
                        :class="{ 'rotate-180': relatoriosOpen }"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="m6 9 6 6 6-6" />
                    </svg>
                </button>

                <div
                    v-show="relatoriosOpen"
                    class="ml-5 mt-1 space-y-1 border-l border-emerald-300/15 pl-4"
                >
                    <a
                        href="/relatorios/empresas"
                        class="block rounded-lg px-3 py-2.5 text-sm text-emerald-100/60 transition hover:bg-white/5 hover:text-white"
                    >
                        Por empresa
                    </a>

                    <a
                        href="/relatorios/consolidado"
                        class="block rounded-lg px-3 py-2.5 text-sm text-emerald-100/60 transition hover:bg-white/5 hover:text-white"
                    >
                        Consolidado
                    </a>
                </div>
            </div>
        </nav>

        <!-- Rodapé -->
        <div class="border-t border-white/10 p-4">
            <!-- Usuário -->
            <div
                class="mb-3 flex items-center gap-3 rounded-xl bg-white/5 p-3"
            >
                <div
                    class="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-500 font-semibold text-white"
                >
                    U
                </div>

                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold">
                        Usuário
                    </p>

                    <p class="truncate text-xs text-emerald-100/50">
                        Administrador
                    </p>
                </div>

                <button
                    type="button"
                    class="text-emerald-100/50 transition hover:text-white"
                    title="Configurações"
                >
                    <svg
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z"
                        />
                        <path
                            d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1-1.8 1.8-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.6v.2h-2.5V20a1.7 1.7 0 0 0-1-1.6 1.7 1.7 0 0 0-1.9.3l-.1.1-1.8-1.8.1-.1a1.7 1.7 0 0 0 .3-1.9 1.7 1.7 0 0 0-1.6-1H5v-2.5h.2a1.7 1.7 0 0 0 1.6-1 1.7 1.7 0 0 0-.3-1.9l-.1-.1 1.8-1.8.1.1a1.7 1.7 0 0 0 1.9.3 1.7 1.7 0 0 0 1-1.6V5h2.5v.2a1.7 1.7 0 0 0 1 1.6 1.7 1.7 0 0 0 1.9-.3l.1-.1 1.8 1.8-.1.1a1.7 1.7 0 0 0-.3 1.9 1.7 1.7 0 0 0 1.6 1h.2v2.5h-.2a1.7 1.7 0 0 0-1.6 1Z"
                        />
                    </svg>
                </button>
            </div>

            <!-- Sair -->
            <button
                type="button"
                class="group flex w-full items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium text-emerald-100/60 transition hover:bg-red-500/10 hover:text-red-300"
            >
                <svg
                    class="h-5 w-5 transition group-hover:text-red-300"
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
