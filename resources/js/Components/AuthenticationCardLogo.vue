<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <Link :href="'/'">
        <div class="flex flex-col items-center gap-2 sm:flex-row sm:gap-3">
            <svg width="44" height="44" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect width="36" height="36" rx="10" fill="#10B981" />
                <path d="M11 23V17" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M17 23V13" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M23 23L28 12" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M23 23L20.5 19" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
            </svg>

            <div>
                <h1 class="font-figtree text-2xl font-bold tracking-tight text-[#12201b]">
                    Conta<span class="text-emerald-600">Fácil</span>
                </h1>

                <p class="text-xs text-slate-500">
                    Gestão financeira
                </p>
            </div>
        </div>
    </Link>
</template>