<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import LancamentoList from '@/Components/Financeiro/LancamentoList.vue'
import type {
    LancamentoItem,
    LancamentoTipo,
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
    <Head :title="'Receitas'" />

    <AppLayout>
        <div class="min-h-screen bg-[#f6f6f2] p-4 sm:p-6 lg:p-8">
            <LancamentoList
                titulo="Receitas"
                descricao="Acompanhe as entradas de caixa do mês."
                tipo="receita"
                prefixo-rota="receitas"
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