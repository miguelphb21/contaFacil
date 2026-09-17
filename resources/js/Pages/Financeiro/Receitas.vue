<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'
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
}

defineProps<Props>()
</script>

<template>
    <AppLayout>
        <div class="min-h-screen bg-gray-50 p-6 lg:p-8">
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
            />
        </div>
    </AppLayout>
</template>