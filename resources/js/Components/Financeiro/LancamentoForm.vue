<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import Checkbox from '@/Components/Checkbox.vue'
import type { LancamentoItem, LancamentoStatus, Opcao } from '@/types'
import { computed, watch } from 'vue'

interface FormProps {
    descricao: string
    valor: string
    data_vencimento: string
    status: LancamentoStatus
    forma_pagamento: string | null
    categoria_id: number | null
    contraparte_id: number | null
    recorrente: boolean
    data_fim: string | null
}

const emit = defineEmits<{
    fechado: []
}>()

const props = defineProps<{
    aberto: boolean
    lancamento: LancamentoItem | null
    categorias: Opcao[]
    contrapartes: Opcao[]
    prefixoRota: string
    periodo: string
}>()

const form = useForm<FormProps>({
    descricao: '',
    valor: '',
    data_vencimento: '',
    status: 'pendente',
    forma_pagamento: null,
    categoria_id: null,
    contraparte_id: null,
    recorrente: false,
    data_fim: null,
})

const titulo = computed(() =>
    props.lancamento ? 'Editar lançamento' : 'Novo lançamento',
)

const erroData = computed(() => (form.errors as { data?: string }).data)

watch(
    () => props.aberto,
    (aberto) => {
        if (!aberto) {
            return
        }

        form.clearErrors()
        form.reset()

        if (props.lancamento) {
            form.descricao = props.lancamento.descricao
            form.valor = Number(props.lancamento.valor).toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            })
            form.data_vencimento = props.lancamento.data.slice(0, 10)
            form.status = props.lancamento.status
            form.forma_pagamento = props.lancamento.forma_pagamento
            form.categoria_id = props.lancamento.categoria?.id ?? null
            form.contraparte_id = props.lancamento.contraparte?.id ?? null
            form.recorrente = props.lancamento.recorrencia !== null
            form.data_fim = props.lancamento.recorrencia?.data_fim?.slice(0, 10) ?? null
        }
    },
)

const mostrarFormaPagamento = computed(
    () => form.status === 'pago',
)

const enviar = () => {
    const dados = form.transform((dados) => ({
        descricao: dados.descricao,
        valor: String(dados.valor).replace(/\./g, '').replace(',', '.'),
        data: dados.data_vencimento,
        status: dados.status,
        forma_pagamento: dados.forma_pagamento,
        categoria_id: dados.categoria_id,
        contraparte_id: dados.contraparte_id,
        recorrente: dados.recorrente,
        data_fim: dados.recorrente ? dados.data_fim : null,
        periodo: props.periodo,
    }))

    if (props.lancamento) {
        dados.patch(
            route(`${props.prefixoRota}.update`, props.lancamento.id),
            {
                preserveScroll: true,
                onSuccess: () => emit('fechado'),
            },
        )

        return
    }

    dados.post(route(`${props.prefixoRota}.store`), {
        preserveScroll: true,
        onSuccess: () => emit('fechado'),
    })
}

const statusOpcoes: Array<{ valor: FormProps['status']; label: string }> = [
    { valor: 'pendente', label: 'Pendente' },
    { valor: 'pago', label: 'Pago' },
    { valor: 'cancelado', label: 'Cancelado' },
]
</script>

<template>
    <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="aberto"
            class="fixed inset-0 z-[60] flex items-center justify-center p-4"
        >
            <div
                class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"
                @click="emit('fechado')"
            />

            <div class="relative w-full max-w-xl overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                    <h2 class="text-lg font-bold text-slate-900">
                        {{ titulo }}
                    </h2>

                    <button
                        type="button"
                        @click="emit('fechado')"
                        class="flex h-9 w-9 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                        aria-label="Fechar"
                    >
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path d="M18 6 6 18M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="enviar">
                    <div class="space-y-5 px-6 py-6">
                        <div>
                            <label
                                for="lancamento_descricao"
                                class="mb-2 block text-sm font-medium"
                                :class="form.errors.descricao ? 'text-red-600' : 'text-slate-700'"
                            >
                                Descrição
                            </label>

                            <input
                                id="lancamento_descricao"
                                v-model="form.descricao"
                                type="text"
                                placeholder="Ex.: Venda à vista"
                                class="w-full rounded-lg border bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition"
                                :class="form.errors.descricao
                                    ? 'border-red-500 bg-red-50'
                                    : 'border-slate-300 focus:border-slate-900 focus:ring-2 focus:ring-slate-900/10'"
                            />

                            <p
                                v-if="form.errors.descricao"
                                class="mt-1.5 text-sm font-medium text-red-600"
                            >
                                {{ form.errors.descricao }}
                            </p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    for="lancamento_valor"
                                    class="mb-2 block text-sm font-medium"
                                    :class="form.errors.valor ? 'text-red-600' : 'text-slate-700'"
                                >
                                    Valor
                                </label>

                                <input
                                    id="lancamento_valor"
                                    v-model="form.valor"
                                    type="text"
                                    inputmode="decimal"
                                    placeholder="0,00"
                                    class="w-full rounded-lg border bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition"
                                    :class="form.errors.valor
                                        ? 'border-red-500 bg-red-50'
                                        : 'border-slate-300 focus:border-slate-900 focus:ring-2 focus:ring-slate-900/10'"
                                />

                                <p
                                    v-if="form.errors.valor"
                                    class="mt-1.5 text-sm font-medium text-red-600"
                                >
                                    {{ form.errors.valor }}
                                </p>
                            </div>

                            <div>
                                <label
                                    for="lancamento_data"
                                    class="mb-2 block text-sm font-medium"
                                    :class="erroData ? 'text-red-600' : 'text-slate-700'"
                                >
                                    Data
                                </label>

                                <input
                                    id="lancamento_data"
                                    v-model="form.data_vencimento"
                                    type="date"
                                    class="w-full rounded-lg border bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition"
                                    :class="erroData
                                        ? 'border-red-500 bg-red-50'
                                        : 'border-slate-300 focus:border-slate-900 focus:ring-2 focus:ring-slate-900/10'"
                                />

                                <p
                                    v-if="erroData"
                                    class="mt-1.5 text-sm font-medium text-red-600"
                                >
                                    {{ erroData }}
                                </p>
                            </div>
                        </div>

                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                            <label class="flex cursor-pointer items-center gap-3">
                                <Checkbox v-model:checked="form.recorrente" />

                                <span class="text-sm font-medium text-slate-700">
                                    Tornar esta movimentação recorrente
                                </span>
                            </label>

                            <div
                                v-if="form.recorrente"
                                class="mt-4 border-t border-slate-200 pt-4"
                            >
                                <p class="text-sm font-semibold text-slate-900">
                                    Período da conta
                                </p>

                                <div class="mt-3 grid grid-cols-2 gap-4">
                                    <div>
                                        <label
                                            for="lancamento_recorrencia_inicio"
                                            class="mb-2 block text-sm font-medium text-slate-700"
                                        >
                                            Data inicial
                                        </label>

                                        <input
                                            id="lancamento_recorrencia_inicio"
                                            :value="form.data_vencimento"
                                            type="date"
                                            readonly
                                            class="w-full rounded-lg border border-slate-200 bg-slate-100 px-4 py-2.5 text-sm text-slate-500 outline-none"
                                        />
                                    </div>

                                    <div>
                                        <label
                                            for="lancamento_recorrencia_fim"
                                            class="mb-2 block text-sm font-medium"
                                            :class="form.errors.data_fim ? 'text-red-600' : 'text-slate-700'"
                                        >
                                            Data final
                                        </label>

                                        <input
                                            id="lancamento_recorrencia_fim"
                                            v-model="form.data_fim"
                                            type="date"
                                            class="w-full rounded-lg border bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition"
                                            :class="form.errors.data_fim
                                                ? 'border-red-500 bg-red-50'
                                                : 'border-slate-300 focus:border-slate-900 focus:ring-2 focus:ring-slate-900/10'"
                                        />

                                        <p
                                            v-if="form.errors.data_fim"
                                            class="mt-1.5 text-sm font-medium text-red-600"
                                        >
                                            {{ form.errors.data_fim }}
                                        </p>

                                        <p
                                            v-else
                                            class="mt-1.5 text-xs text-slate-500"
                                        >
                                            Sem data final a recorrência é contínua (sem limite).
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    for="lancamento_categoria"
                                    class="mb-2 block text-sm font-medium text-slate-700"
                                >
                                    Categoria
                                </label>

                                <select
                                    id="lancamento_categoria"
                                    v-model="form.categoria_id"
                                    class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-slate-900 focus:ring-2 focus:ring-slate-900/10"
                                >
                                    <option :value="null">
                                        Sem categoria
                                    </option>

                                    <option
                                        v-for="categoria in categorias"
                                        :key="categoria.id"
                                        :value="categoria.id"
                                    >
                                        {{ categoria.nome }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label
                                    for="lancamento_contraparte"
                                    class="mb-2 block text-sm font-medium text-slate-700"
                                >
                                    Contraparte
                                </label>

                                <select
                                    id="lancamento_contraparte"
                                    v-model="form.contraparte_id"
                                    class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-slate-900 focus:ring-2 focus:ring-slate-900/10"
                                >
                                    <option :value="null">
                                        Sem contraparte
                                    </option>

                                    <option
                                        v-for="contraparte in contrapartes"
                                        :key="contraparte.id"
                                        :value="contraparte.id"
                                    >
                                        {{ contraparte.nome }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <!-- edição de status -->

                            <div v-if="mostrarFormaPagamento">
                                <label
                                    for="lancamento_forma_pagamento"
                                    class="mb-2 block text-sm font-medium text-slate-700"
                                >
                                    Forma de pagamento
                                </label>

                                <input
                                    id="lancamento_forma_pagamento"
                                    v-model="form.forma_pagamento"
                                    type="text"
                                    maxlength="50"
                                    placeholder="Ex.: Pix, Cartão, Boleto"
                                    class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-slate-900 focus:ring-2 focus:ring-slate-900/10"
                                />
                            </div>
                        </div>
                    </div>

                    <div
                        class="flex items-center justify-end gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4"
                    >
                        <button
                            type="button"
                            @click="emit('fechado')"
                            class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                        >
                            Cancelar
                        </button>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            {{ form.processing ? 'Salvando...' : 'Salvar' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </Transition>
</template>
