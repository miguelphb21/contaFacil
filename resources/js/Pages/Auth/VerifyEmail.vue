<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    status: String,
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');
</script>

<template>
    <Head title="Verificar e-mail" />

    <AuthenticationCard
        title="Verificar e-mail"
        description="Antes de continuar, confirme seu endereço de e-mail clicando no link que acabamos de enviar."
    >
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <div v-if="verificationLinkSent" class="mb-4 font-medium text-sm text-green-600">
            Um novo link de verificação foi enviado para o e-mail informado no seu perfil.
        </div>

        <form @submit.prevent="submit">
            <div class="mt-4 flex items-center justify-between">
                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Reenviar e-mail de verificação
                </PrimaryButton>

                <div>
                    <Link
                        :href="route('profile.show')"
                        class="underline text-sm text-emerald-600 hover:text-emerald-800 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500"
                    >
                        Editar perfil</Link>

                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="underline text-sm text-emerald-600 hover:text-emerald-800 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 ms-2"
                    >
                        Sair
                    </Link>
                </div>
            </div>
        </form>
    </AuthenticationCard>
</template>
