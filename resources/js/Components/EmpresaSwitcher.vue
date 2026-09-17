<script setup lang="ts">
import { router } from '@inertiajs/vue3'

interface EmpresaResumida {
    id: number
    razao_social: string
    nome_fantasia: string | null
}

defineProps<{
    empresaAtual: EmpresaResumida | null
    empresas: EmpresaResumida[]
}>()

const nomeExibicao = (empresa: EmpresaResumida) =>
    empresa.nome_fantasia || empresa.razao_social

const selecionar = (event: Event) => {
    const empresaId = Number((event.target as HTMLSelectElement).value)

    router.post(route('empresas.selecionar.armazenar'), {
        empresa_id: empresaId,
    })
}
</script>

<template>
    <div class="px-3">
        <p class="mb-1.5 px-1 text-[11px] font-semibold uppercase tracking-wider text-gray-400">
            Empresa ativa
        </p>

        <label class="sr-only" for="empresa-switcher">Empresa ativa</label>

        <select
            id="empresa-switcher"
            class="w-full cursor-pointer rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm font-semibold text-gray-800 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
            :value="empresaAtual?.id"
            @change="selecionar"
        >
            <option :value="null" disabled>
                Selecione uma empresa
            </option>

            <option
                v-for="empresa in empresas"
                :key="empresa.id"
                :value="empresa.id"
            >
                {{ nomeExibicao(empresa) }}
            </option>
        </select>

        <p
            v-if="empresaAtual"
            class="mt-1.5 truncate px-1 text-xs text-gray-400"
        >
            {{ empresaAtual.razao_social }}
        </p>
    </div>
</template>