<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import type { ContraparteItem } from '@/types'
import { computed, watch } from 'vue'

interface FormProps {
    tipo: 'fornecedor' | 'cliente'
    nome: string
    cpf_cnpj: string | null
    telefone: string | null
    email: string | null
    endereco: string | null
}

const emit = defineEmits<{
    fechado: []
}>()

const props = defineProps<{
    aberto: boolean
    contraparte: ContraparteItem | null
}>()

const form = useForm<FormProps>({
    tipo: 'fornecedor',
    nome: '',
    cpf_cnpj: null,
    telefone: null,
    email: null,
    endereco: null,
})

const titulo = computed(() =>
    props.contraparte ? 'Editar contraparte' : 'Nova contraparte',
)

watch(
    () => props.aberto,
    (aberto) => {
        if (!aberto) {
            return
        }

        form.clearErrors()
        form.reset()

        if (props.contraparte) {
            form.tipo = props.contraparte.tipo
            form.nome = props.contraparte.nome
            form.cpf_cnpj = props.contraparte.cpf_cnpj
            form.telefone = props.contraparte.telefone
            form.email = props.contraparte.email
            form.endereco = props.contraparte.endereco
        }
    },
)

const enviar = () => {
    const dados = form.transform((valores) => ({
        ...valores,
        cpf_cnpj: valores.cpf_cnpj === '' ? null : valores.cpf_cnpj,
        telefone: valores.telefone === '' ? null : valores.telefone,
        email: valores.email === '' ? null : valores.email,
        endereco: valores.endereco === '' ? null : valores.endereco,
    }))

    if (props.contraparte) {
        dados.patch(route('contrapartes.update', props.contraparte.id), {
            preserveScroll: true,
            onSuccess: () => emit('fechado'),
        })

        return
    }

    dados.post(route('contrapartes.store'), {
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
                                    @click="form.tipo = 'fornecedor'"
                                    class="rounded-lg border px-4 py-2.5 text-sm font-semibold transition"
                                    :class="form.tipo === 'fornecedor'
                                        ? 'border-red-200 bg-red-50 text-red-700'
                                        : 'border-gray-300 bg-white text-gray-500 hover:bg-gray-50'"
                                >
                                    Fornecedor
                                </button>

                                <button
                                    type="button"
                                    @click="form.tipo = 'cliente'"
                                    class="rounded-lg border px-4 py-2.5 text-sm font-semibold transition"
                                    :class="form.tipo === 'cliente'
                                        ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                                        : 'border-gray-300 bg-white text-gray-500 hover:bg-gray-50'"
                                >
                                    Cliente
                                </button>
                            </div>
                        </div>

                        <div>
                            <label
                                for="contraparte_nome"
                                class="mb-2 block text-sm font-medium"
                                :class="form.errors.nome ? 'text-red-600' : 'text-gray-700'"
                            >
                                Nome
                            </label>

                            <input
                                id="contraparte_nome"
                                v-model="form.nome"
                                type="text"
                                placeholder="Ex.: Distribuidora ABC"
                                class="w-full rounded-lg border bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition"
                                :class="form.errors.nome
                                    ? 'border-red-500 bg-red-50'
                                    : 'border-gray-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100'"
                            />

                            <p
                                v-if="form.errors.nome"
                                class="mt-1.5 text-sm font-medium text-red-600"
                            >
                                {{ form.errors.nome }}
                            </p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    for="contraparte_cpf_cnpj"
                                    class="mb-2 block text-sm font-medium text-gray-700"
                                >
                                    CPF / CNPJ
                                </label>

                                <input
                                    id="contraparte_cpf_cnpj"
                                    v-model="form.cpf_cnpj"
                                    type="text"
                                    maxlength="20"
                                    placeholder="Ex.: 00.000.000/0000-00"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                />
                            </div>

                            <div>
                                <label
                                    for="contraparte_telefone"
                                    class="mb-2 block text-sm font-medium text-gray-700"
                                >
                                    Telefone
                                </label>

                                <input
                                    id="contraparte_telefone"
                                    v-model="form.telefone"
                                    type="text"
                                    maxlength="30"
                                    placeholder="Ex.: (11) 99999-9999"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                />
                            </div>
                        </div>

                        <div>
                            <label
                                for="contraparte_email"
                                class="mb-2 block text-sm font-medium text-gray-700"
                            >
                                E-mail
                            </label>

                            <input
                                id="contraparte_email"
                                v-model="form.email"
                                type="email"
                                maxlength="255"
                                placeholder="contato@empresa.com.br"
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                            />
                        </div>

                        <div>
                            <label
                                for="contraparte_endereco"
                                class="mb-2 block text-sm font-medium text-gray-700"
                            >
                                Endereço
                            </label>

                            <textarea
                                id="contraparte_endereco"
                                v-model="form.endereco"
                                rows="2"
                                maxlength="500"
                                placeholder="Rua, número, bairro, cidade, UF"
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                            />
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