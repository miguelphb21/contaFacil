<script setup lang="ts">
import { computed } from 'vue'

interface Props {
    success?: string | null
    error?: string | null
}

const props = withDefaults(defineProps<Props>(), {
    success: null,
    error: null,
})

const message = computed(() => {
    if (props.success) {
        return {
            type: 'success',
            text: props.success,
        }
    }

    if (props.error) {
        return {
            type: 'error',
            text: props.error,
        }
    }

    return null
})
</script>

<template>
    <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="translate-y-2 opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="message"
            class="mb-6 overflow-hidden rounded-xl border shadow-sm"
            :class="
                message.type === 'success'
                    ? 'border-emerald-200 bg-emerald-50'
                    : 'border-red-200 bg-red-50'
            "
        >
            <div class="flex items-start gap-3 px-5 py-4">
                <!-- Ícone -->
                <div
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg"
                    :class="
                        message.type === 'success'
                            ? 'bg-emerald-100 text-emerald-700'
                            : 'bg-red-100 text-red-700'
                    "
                >
                    <svg
                        v-if="message.type === 'success'"
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="m5 12 4 4L19 6" />
                    </svg>

                    <svg
                        v-else
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="M12 9v4" />
                        <path d="M12 17h.01" />
                        <path d="M10.3 3.7 2.8 17a2 2 0 0 0 1.7 3h15a2 2 0 0 0 1.7-3l-7.5-13.3a2 2 0 0 0-3.4 0Z" />
                    </svg>
                </div>

                <!-- Mensagem -->
                <div class="min-w-0">
                    <p
                        class="text-sm font-semibold"
                        :class="
                            message.type === 'success'
                                ? 'text-emerald-800'
                                : 'text-red-800'
                        "
                    >
                        {{
                            message.type === 'success'
                                ? 'Operação realizada'
                                : 'Não foi possível concluir a operação'
                        }}
                    </p>

                    <p
                        class="mt-0.5 text-sm"
                        :class="
                            message.type === 'success'
                                ? 'text-emerald-700'
                                : 'text-red-700'
                        "
                    >
                        {{ message.text }}
                    </p>
                </div>
            </div>
        </div>
    </Transition>
</template>
