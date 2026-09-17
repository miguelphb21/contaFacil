<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import type {
    LancamentoTipo,
    OpcaoTipoContraparte,
    OpcaoTipoLancamento,
    RecorrenciaItem,
} from '@/types'
import { computed, watch } from 'vue'

interface FormProps {
    tipo: LancamentoTipo
    descricao: string
    valor: string
    dia: number | null
    data_inicio: string
    data_fim: string | null
    forma_pagamento: string | null
    ativa: boolean
    categoria_id: number | null
    contraparte_id: number | null
}

const emit = defineEmits<{
    fechado: []
}>()

const props = defineProps<{
    aberto: boolean
    recorrencia: RecorrenciaItem | null
    categorias: OpcaoTipoLancamento[]
    contrapartes: OpcaoTipoContraparte[]
}>()

const form = useForm<FormProps>({
    tipo: 'despesa',
    descricao: '',
    valor: '',
    dia: null,
    data_inicio: '',
    data_fim: null,
    forma_pagamento: null,
    ativa: true,
    categoria_id: null,
    contraparte_id: null,
})

const titulo = computed(() =>
    props.recorrencia ? 'Editar recorrência' : 'Nova recorrência',
)

const categoriasFiltradas = computed(() =>
    props.categorias.filter((categoria) => categoria.tipo === form.tipo),
)

const contrapartesFiltradas = computed(() => {
    const tipoEsperado = form.tipo === 'despesa' ? 'fornecedor' : 'cliente'

    return props.contrapartes.filter(
        (contraparte) => contraparte.tipo === tipoEsperado,
    )
})

const ajustarTipo = (tipo: LancamentoTipo) => {
    form.tipo = tipo
    form.categoria_id = null
    form.contraparte_id = null
}

const dias = Array.from({ length: 28 }, (_, indice) => indice + 1)

watch(
    () => props.aberto,
    (aberto) => {
        if (!aberto) {
            return
        }

        form.clearErrors()
        form.reset()

        if (props.recorrencia) {
            form.tipo = props.recorrencia.tipo
            form.descricao = props.recorrencia.descricao
            form.valor = props.recorrencia.valor
            form.dia = props.recorrencia.dia
            form.data_inicio = props.recorrencia.data_inicio.slice(0, 10)
            form.data_fim = props.recorrencia.data_fim?.slice(0, 10) ?? null
            form.forma_pagamento = props.recorrencia.forma_pagamento
            form.ativa = props.recorrencia.ativa
            form.categoria_id = props.recorrencia.categoria?.id ?? null
            form.contraparte_id = props.recorrencia.contraparte?.id ?? null
        }
    },
)

const enviar = () => {
    const dados = form.transform((valores) => ({
        ...valores,
        valor: String(valores.valor).replace(/\./g, '').replace(',', '.'),
        data_fim: valores.data_fim === '' ? null : valores.data_fim,
    }))

    if (props.recorrencia) {
        dados.patch(route('recorrencias.update', props.recorrencia.id), {
            preserveScroll: true,
            onSuccess: () => emit('fechado'),
        })

        return
    }

    dados.post(route('recorrencias.store'), {
        preserveScroll: true,
        onSuccess: () => emit('fechado'),
    })
}
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

            <div class="relative max-h-[90vh] w-full max-w-xl overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-xl">
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
                            <label class="mb-2 block text-sm font-medium text-gray-700">
                                Tipo
                            </label>

                            <div class="grid grid-cols-2 gap-2">
                                <button
                                    type="button"
                                    @click="ajustarTipo('despesa')"
                                    class="rounded-lg border px-4 py-2.5 text-sm font-semibold transition"
                                    :class="form.tipo === 'despesa'
                                        ? 'border-red-200 bg-red-50 text-red-700'
                                        : 'border-gray-300 bg-white text-gray-500 hover:bg-gray-50'"
                                >
                                    Despesa
                                </button>

                                <button
                                    type="button"
                                    @click="ajustarTipo('receita')"
                                    class="rounded-lg border px-4 py-2.5 text-sm font-semibold transition"
                                    :class="form.tipo === 'receita'
                                        ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                                        : 'border-gray-300 bg-white text-gray-500 hover:bg-gray-50'"
                                >
                                    Receita
                                </button>
                            </div>
                        </div>

                        <div>
                            <label
                                for="recorrencia_descricao"
                                class="mb-2 block text-sm font-medium"
                                :class="form.errors.descricao ? 'text-red-600' : 'text-gray-700'"
                            >
                                Descrição
                            </label>

                            <input
                                id="recorrencia_descricao"
                                v-model="form.descricao"
                                type="text"
                                placeholder="Ex.: Aluguel da loja"
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
                                    for="recorrencia_valor"
                                    class="mb-2 block text-sm font-medium"
                                    :class="form.errors.valor ? 'text-red-600' : 'text-gray-700'"
                                >
                                    Valor
                                </label>

                                <input
                                    id="recorrencia_valor"
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
                                    for="recorrencia_dia"
                                    class="mb-2 block text-sm font-medium"
                                    :class="form.errors.dia ? 'text-red-600' : 'text-gray-700'"
                                >
                                    Dia do vencimento
                                </label>

                                <select
                                    id="recorrencia_dia"
                                    v-model="form.dia"
                                    class="w-full rounded-lg border bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition"
                                    :class="form.errors.dia
                                        ? 'border-red-500 bg-red-50'
                                        : 'border-gray-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100'"
                                >
                                    <option :value="null" disabled>
                                        Selecione o dia
                                    </option>

                                    <option
                                        v-for="dia in dias"
                                        :key="dia"
                                        :value="dia"
                                    >
                                        Dia {{ dia }}
                                    </option>
                                </select>

                                <p
                                    v-if="form.errors.dia"
                                    class="mt-1.5 text-sm font-medium text-red-600"
                                >
                                    {{ form.errors.dia }}
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    for="recorrencia_data_inicio"
                                    class="mb-2 block text-sm font-medium"
                                    :class="form.errors.data_inicio ? 'text-red-600' : 'text-gray-700'"
                                >
                                    Data de início
                                </label>

                                <input
                                    id="recorrencia_data_inicio"
                                    v-model="form.data_inicio"
                                    type="date"
                                    class="w-full rounded-lg border bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition"
                                    :class="form.errors.data_inicio
                                        ? 'border-red-500 bg-red-50'
                                        : 'border-gray-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100'"
                                />

                                <p
                                    v-if="form.errors.data_inicio"
                                    class="mt-1.5 text-sm font-medium text-red-600"
                                >
                                    {{ form.errors.data_inicio }}
                                </p>
                            </div>

                            <div>
                                <label
                                    for="recorrencia_data_fim"
                                    class="mb-2 block text-sm font-medium text-gray-700"
                                >
                                    Data final
                                </label>

                                <input
                                    id="recorrencia_data_fim"
                                    v-model="form.data_fim"
                                    type="date"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                />

                                <p class="mt-1.5 text-xs text-gray-400">
                                    Deixe em branco para não expirar.
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    for="recorrencia_categoria"
                                    class="mb-2 block text-sm font-medium text-gray-700"
                                >
                                    Categoria
                                </label>

                                <select
                                    id="recorrencia_categoria"
                                    v-model="form.categoria_id"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                >
                                    <option :value="null">
                                        Sem categoria
                                    </option>

                                    <option
                                        v-for="categoria in categoriasFiltradas"
                                        :key="categoria.id"
                                        :value="categoria.id"
                                    >
                                        {{ categoria.nome }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label
                                    for="recorrencia_contraparte"
                                    class="mb-2 block text-sm font-medium text-gray-700"
                                >
                                    Contraparte
                                </label>

                                <select
                                    id="recorrencia_contraparte"
                                    v-model="form.contraparte_id"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                >
                                    <option :value="null">
                                        Sem contraparte
                                    </option>

                                    <option
                                        v-for="contraparte in contrapartesFiltradas"
                                        :key="contraparte.id"
                                        :value="contraparte.id"
                                    >
                                        {{ contraparte.nome }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    for="recorrencia_forma_pagamento"
                                    class="mb-2 block text-sm font-medium text-gray-700"
                                >
                                    Forma de pagamento
                                </label>

                                <input
                                    id="recorrencia_forma_pagamento"
                                    v-model="form.forma_pagamento"
                                    type="text"
                                    maxlength="50"
                                    placeholder="Ex.: Pix, Cartão, Boleto"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                />
                            </div>

                            <label
                                for="recorrencia_ativa"
                                class="flex cursor-pointer items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3"
                            >
                                <input
                                    id="recorrencia_ativa"
                                    v-model="form.ativa"
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                                />

                                <span class="text-sm font-medium text-gray-700">
                                    Recorrência ativa
                                </span>
                            </label>
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