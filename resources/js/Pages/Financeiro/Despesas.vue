<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import LancamentoList from '@/Components/Financeiro/LancamentoList.vue'
import type {
    LancamentoItem,
    Opcao,
    Periodo,
    ResumoMensal,
} from '@/types'

interface Props {
    lancamentos: LancamentoItem[]
    categorias: Opcao[]
    contrapartes: Opcao[]
    resumo: ResumoMensal
    periodo: Periodo
    filtroData: string | null
    novo?: boolean
}

defineProps<Props>()
</script>

<template>
    <Head :title="'Despesas'" />

    <AppLayout>
        <div class="min-h-screen bg-[#f6f6f2] p-4 sm:p-6 lg:p-8">
            <LancamentoList
                titulo="Despesas"
                descricao="Acompanhe as saídas de caixa do mês."
                tipo="despesa"
                prefixo-rota="despesas"
                :lancamentos="lancamentos"
                :categorias="categorias"
                :contrapartes="contrapartes"
                :resumo="resumo"
                :periodo="periodo"
                :filtro-data="filtroData"
                :novo="novo ?? false"
            />
        </div>
    </AppLayout>
</template>