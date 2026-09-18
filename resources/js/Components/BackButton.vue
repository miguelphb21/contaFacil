<script setup lang="ts">
import { router } from '@inertiajs/vue3'

interface Props {
    label?: string
    fallback?: string
}

withDefaults(defineProps<Props>(), {
    label: 'Voltar',
    fallback: '/dashboard',
})

const goBack = (fallback: string) => {
    if (window.history.length > 1) {
        window.history.back()
        return
    }

    router.visit(fallback)
}
</script>

<template>
    <button
        type="button"
        @click="goBack(fallback)"
        class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 transition hover:text-slate-900"
    >
        <svg
            class="h-4 w-4"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
        >
            <path d="M19 12H5" />
            <path d="m12 19-7-7 7-7" />
        </svg>

        {{ label }}
    </button>
</template>

