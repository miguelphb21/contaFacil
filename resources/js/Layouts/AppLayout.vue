<script setup lang="ts">
import AppSidebar from '@/Components/AppSidebar.vue'
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <AppSidebar />

        <main class="min-h-screen lg:pl-72">
            <slot />
        </main>
    </div>
</template>

