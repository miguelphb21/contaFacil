<script setup>
defineProps({
    title: String,
    description: String,
});

const year = new Date().getFullYear();
</script>

<template>
    <div class="min-h-screen bg-white lg:grid lg:grid-cols-2">
        <div
            class="relative hidden flex-col justify-between overflow-hidden bg-[#12201b] p-12 lg:flex xl:p-16"
        >
            <div
                class="pointer-events-none absolute -right-24 -top-24 h-96 w-96 rounded-full bg-emerald-500/10 blur-3xl"
            />
            <div
                class="pointer-events-none absolute -bottom-32 -left-24 h-96 w-96 rounded-full bg-emerald-500/5 blur-3xl"
            />

            <div class="relative">
                <div class="flex items-center gap-3">
                    <svg width="44" height="44" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="36" height="36" rx="10" fill="#10B981" />
                        <path d="M11 23V17" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M17 23V13" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M23 23L28 12" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M23 23L20.5 19" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>

                    <div>
                        <h1 class="font-figtree text-2xl font-bold tracking-tight text-white">
                            Conta<span class="text-emerald-400">Fácil</span>
                        </h1>
                        <p class="text-xs text-[#8ba899]">
                            Gestão financeira
                        </p>
                    </div>
                </div>
            </div>

            <div class="relative">
                <h2 class="font-figtree text-4xl font-bold leading-tight tracking-tight text-white xl:text-5xl">
                    Suas finanças,<br>
                    <span class="text-emerald-400">sem complicação.</span>
                </h2>

                <p class="mt-5 max-w-md text-base leading-relaxed text-[#9bb2a6]">
                    Controle receitas, despesas, contrapartes e relatórios de todas as suas empresas em um único lugar.
                </p>

                <ul class="mt-10 space-y-5">
                    <li class="flex items-center gap-3">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-emerald-500/15">
                            <svg class="h-4 w-4 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 6 9 17l-5-5" />
                            </svg>
                        </span>
                        <span class="text-sm font-medium text-[#c8dcd2]">Dashboard com métricas financeiras</span>
                    </li>

                    <li class="flex items-center gap-3">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-emerald-500/15">
                            <svg class="h-4 w-4 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 6 9 17l-5-5" />
                            </svg>
                        </span>
                        <span class="text-sm font-medium text-[#c8dcd2]">Controle de receitas e despesas</span>
                    </li>

                    <li class="flex items-center gap-3">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-emerald-500/15">
                            <svg class="h-4 w-4 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 6 9 17l-5-5" />
                            </svg>
                        </span>
                        <span class="text-sm font-medium text-[#c8dcd2]">Relatórios em PDF e Excel</span>
                    </li>
                </ul>
            </div>

            <p class="relative text-xs text-[#7f9a8c]">
                © {{ year }} ContaFácil — Gestão financeira pessoal e empresarial.
            </p>
        </div>

        <div class="flex min-h-screen flex-col items-center justify-center bg-slate-50 px-4 py-10 sm:px-6 lg:px-12">
            <div class="mb-8 lg:hidden">
                <slot name="logo" />
            </div>

            <div class="w-full max-w-md">
                <h2 v-if="title" class="font-figtree text-3xl font-bold tracking-tight text-[#12201b]">
                    {{ title }}
                </h2>

                <p v-if="description" class="mt-2 text-sm leading-relaxed text-slate-500">
                    {{ description }}
                </p>

                <div class="mt-6 rounded-2xl bg-white p-8 shadow-lg shadow-slate-900/5 ring-1 ring-slate-900/5 sm:p-10">
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>