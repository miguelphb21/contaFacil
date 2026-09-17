<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'
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
}

defineProps<Props>()
</script>

<template>
    <AppLayout>
        <div class="min-h-screen bg-gray-50 p-6 lg:p-8">
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
            />
        </div>
    </AppLayout>
</template>