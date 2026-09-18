<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import type { CategoriaItem, LancamentoTipo } from '@/types'
import { computed, watch } from 'vue'

interface FormProps {
    tipo: LancamentoTipo
    nome: string
    descricao: string | null
}

const emit = defineEmits<{
    fechado: []
}>()

const props = defineProps<{
    aberto: boolean
    categoria: CategoriaItem | null
}>()

const form = useForm<FormProps>({
    tipo: 'despesa',
    nome: '',
    descricao: null,
})

const titulo = computed(() =>
    props.categoria ? 'Editar categoria' : 'Nova categoria',
)

watch(
    () => props.aberto,
    (aberto) => {
        if (!aberto) {
            return
        }

        form.clearErrors()
        form.reset()

        if (props.categoria) {
            form.tipo = props.categoria.tipo
            form.nome = props.categoria.nome
            form.descricao = props.categoria.descricao
        }
    },
)

const enviar = () => {
    const dados = form.transform((valores) => ({
        ...valores,
        descricao: valores.descricao === '' ? null : valores.descricao,
    }))

    if (props.categoria) {
        dados.patch(route('categorias.update', props.categoria.id), {
            preserveScroll: true,
            onSuccess: () => emit('fechado'),
        })

        return
    }

    dados.post(route('categorias.store'), {
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
                class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"
                @click="emit('fechado')"
            />

            <div class="relative w-full max-w-lg overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl">
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
                            <label class="mb-2 block text-sm font-medium text-slate-700">
                                Tipo
                            </label>

                            <div class="grid grid-cols-2 gap-2">
                                <button
                                    type="button"
                                    @click="form.tipo = 'despesa'"
                                    class="rounded-lg border px-4 py-2.5 text-sm font-semibold transition"
                                    :class="form.tipo === 'despesa'
                                        ? 'border-slate-200 bg-slate-100 text-slate-900'
                                        : 'border-slate-300 bg-white text-slate-500 hover:bg-slate-50'"
                                >
                                    Despesa
                                </button>

                                <button
                                    type="button"
                                    @click="form.tipo = 'receita'"
                                    class="rounded-lg border px-4 py-2.5 text-sm font-semibold transition"
                                    :class="form.tipo === 'receita'
                                        ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                                        : 'border-slate-300 bg-white text-slate-500 hover:bg-slate-50'"
                                >
                                    Receita
                                </button>
                            </div>
                        </div>

                        <div>
                            <label
                                for="categoria_nome"
                                class="mb-2 block text-sm font-medium"
                                :class="form.errors.nome ? 'text-red-600' : 'text-slate-700'"
                            >
                                Nome
                            </label>

                            <input
                                id="categoria_nome"
                                v-model="form.nome"
                                type="text"
                                placeholder="Ex.: Aluguel, Vendas, Marketing"
                                class="w-full rounded-lg border bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition"
                                :class="form.errors.nome
                                    ? 'border-red-500 bg-red-50'
                                    : 'border-slate-300 focus:border-slate-900 focus:ring-2 focus:ring-slate-900/10'"
                            />

                            <p
                                v-if="form.errors.nome"
                                class="mt-1.5 text-sm font-medium text-red-600"
                            >
                                {{ form.errors.nome }}
                            </p>
                        </div>

                        <div>
                            <label
                                for="categoria_descricao"
                                class="mb-2 block text-sm font-medium text-slate-700"
                            >
                                Descrição
                            </label>

                            <textarea
                                id="categoria_descricao"
                                v-model="form.descricao"
                                rows="2"
                                maxlength="500"
                                placeholder="Descrição opcional"
                                class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-slate-900 focus:ring-2 focus:ring-slate-900/10"
                            />
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