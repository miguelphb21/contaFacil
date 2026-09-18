<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'
import BackButton from '@/Components/BackButton.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const form = useForm({
    razao_social: '',
    nome_fantasia: '',
    cnpj: '',
})

const submit = () => {
    form.post(route('empresas.store'))
}
</script>

<template>
    <Head :title="'Nova empresa'" />

    <AppLayout>
        <div class="min-h-screen bg-[#f6f6f2] p-6 lg:p-8">
            <div class="mx-auto max-w-3xl">

                <div class="mb-8">
                    <BackButton
                        label="Voltar para empresas"
                        fallback="/empresas"
                    />

                    <div class="mt-4 flex items-center gap-2 text-sm">
                        <Link
                            :href="route('empresas.index')"
                            class="font-medium text-slate-500 transition hover:text-slate-900 hover:underline"
                        >
                            Empresas
                        </Link>

                        <span class="text-slate-300">/</span>

                        <span class="font-medium text-slate-900">
                            Nova empresa
                        </span>
                    </div>

                    <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">
                        Cadastrar empresa
                    </h1>

                    <p class="mt-1 text-sm text-slate-500">
                        Preencha os dados da empresa para adicioná-la ao sistema.
                    </p>
                </div>

                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <form @submit.prevent="submit">

                        <div class="space-y-6 p-6">

                            <div>
                                <label
                                    for="razao_social"
                                    class="mb-2 block text-sm font-medium"
                                    :class="form.errors.razao_social ? 'text-red-600' : 'text-slate-700'"
                                >
                                    Razão social
                                </label>

                                <input
                                    id="razao_social"
                                    v-model="form.razao_social"
                                    type="text"
                                    placeholder="Ex.: Comércio ABC LTDA"
                                    class="w-full rounded-lg border bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition"
                                    :class="form.errors.razao_social
                                        ? 'border-red-500 bg-red-50'
                                        : 'border-slate-300 focus:border-slate-900 focus:ring-2 focus:ring-slate-900/10'"
                                />

                                <p
                                    v-if="form.errors.razao_social"
                                    class="mt-1.5 text-sm font-medium text-red-600"
                                >
                                    {{ form.errors.razao_social }}
                                </p>
                            </div>

                            <div>
                                <label
                                    for="nome_fantasia"
                                    class="mb-2 block text-sm font-medium"
                                    :class="form.errors.nome_fantasia ? 'text-red-600' : 'text-slate-700'"
                                >
                                    Nome fantasia
                                </label>

                                <input
                                    id="nome_fantasia"
                                    v-model="form.nome_fantasia"
                                    type="text"
                                    placeholder="Ex.: Loja Central"
                                    class="w-full rounded-lg border bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition"
                                    :class="form.errors.nome_fantasia
                                        ? 'border-red-500 bg-red-50'
                                        : 'border-slate-300 focus:border-slate-900 focus:ring-2 focus:ring-slate-900/10'"
                                />

                                <p
                                    v-if="form.errors.nome_fantasia"
                                    class="mt-1.5 text-sm font-medium text-red-600"
                                >
                                    {{ form.errors.nome_fantasia }}
                                </p>
                            </div>

                            <div>
                                <label
                                    for="cnpj"
                                    class="mb-2 block text-sm font-medium"
                                    :class="form.errors.cnpj ? 'text-red-600' : 'text-slate-700'"
                                >
                                    CNPJ
                                </label>

                                <input
                                    id="cnpj"
                                    v-model="form.cnpj"
                                    type="text"
                                    maxlength="18"
                                    inputmode="numeric"
                                    placeholder="00.000.000/0000-00"
                                    class="w-full rounded-lg border bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition"
                                    :class="form.errors.cnpj
                                        ? 'border-red-500 bg-red-50'
                                        : 'border-slate-300 focus:border-slate-900 focus:ring-2 focus:ring-slate-900/10'"
                                />

                                <p
                                    v-if="form.errors.cnpj"
                                    class="mt-1.5 text-sm font-medium text-red-600"
                                >
                                    {{ form.errors.cnpj }}
                                </p>
                            </div>

                        </div>

                        <div class="flex items-center justify-end gap-3 border-t border-slate-200 bg-slate-50 px-6 py-4">
                            <Link
                                :href="route('empresas.index')"
                                class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                            >
                                Cancelar
                            </Link>

                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700 disabled:cursor-not-allowed disabled:opacity-60"
                            >
                                {{ form.processing ? 'Salvando...' : 'Salvar empresa' }}
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
