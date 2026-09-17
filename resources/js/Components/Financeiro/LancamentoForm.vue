<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
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
}>()

const form = useForm<FormProps>({
    descricao: '',
    valor: '',
    data_vencimento: '',
    status: 'pendente',
    forma_pagamento: null,
    categoria_id: null,
    contraparte_id: null,
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
            form.valor = props.lancamento.valor
            form.data_vencimento = props.lancamento.data.slice(0, 10)
            form.status = props.lancamento.status
            form.forma_pagamento = props.lancamento.forma_pagamento
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
                class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm"
                @click="emit('fechado')"
            />

            <div class="relative w-full max-w-xl overflow-hidden rounded-xl border border-gray-200 bg-white shadow-xl">
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-5">
                    <h2 class="text-lg font-bold text-gray-900">
                        {{ titulo }}
                    </h2>

                    <button
                        type="button"
                        @click="emit('fechado')"
                        class="flex h-9 w-9 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-700"
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
                                :class="form.errors.descricao ? 'text-red-600' : 'text-gray-700'"
                            >
                                Descrição
                            </label>

                            <input
                                id="lancamento_descricao"
                                v-model="form.descricao"
                                type="text"
                                placeholder="Ex.: Venda à vista"
                                class="w-full rounded-lg border bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition"
                                :class="form.errors.descricao
                                    ? 'border-red-500 bg-red-50'
                                    : 'border-gray-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100'"
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
                                    :class="form.errors.valor ? 'text-red-600' : 'text-gray-700'"
                                >
                                    Valor
                                </label>

                                <input
                                    id="lancamento_valor"
                                    v-model="form.valor"
                                    type="text"
                                    inputmode="decimal"
                                    placeholder="0,00"
                                    class="w-full rounded-lg border bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition"
                                    :class="form.errors.valor
                                        ? 'border-red-500 bg-red-50'
                                        : 'border-gray-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100'"
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
                                    :class="erroData ? 'text-red-600' : 'text-gray-700'"
                                >
                                    Data
                                </label>

                                <input
                                    id="lancamento_data"
                                    v-model="form.data_vencimento"
                                    type="date"
                                    class="w-full rounded-lg border bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition"
                                    :class="erroData
                                        ? 'border-red-500 bg-red-50'
                                        : 'border-gray-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100'"
                                />

                                <p
                                    v-if="erroData"
                                    class="mt-1.5 text-sm font-medium text-red-600"
                                >
                                    {{ erroData }}
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    for="lancamento_categoria"
                                    class="mb-2 block text-sm font-medium text-gray-700"
                                >
                                    Categoria
                                </label>

                                <select
                                    id="lancamento_categoria"
                                    v-model="form.categoria_id"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
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
                                    class="mb-2 block text-sm font-medium text-gray-700"
                                >
                                    Contraparte
                                </label>

                                <select
                                    id="lancamento_contraparte"
                                    v-model="form.contraparte_id"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
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
                                    class="mb-2 block text-sm font-medium text-gray-700"
                                >
                                    Forma de pagamento
                                </label>

                                <input
                                    id="lancamento_forma_pagamento"
                                    v-model="form.forma_pagamento"
                                    type="text"
                                    maxlength="50"
                                    placeholder="Ex.: Pix, Cartão, Boleto"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                />
                            </div>
                        </div>
                    </div>

                    <div
                        class="flex items-center justify-end gap-3 border-t border-gray-100 bg-gray-50 px-6 py-4"
                    >
                        <button
                            type="button"
                            @click="emit('fechado')"
                            class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                        >
                            Cancelar
                        </button>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-lg bg-emerald-700 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-800 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            {{ form.processing ? 'Salvando...' : 'Salvar' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </Transition>
</template>
