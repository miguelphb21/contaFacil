<script setup lang="ts">
import AppSidebar from '@/Components/AppSidebar.vue'
</script>

<template>
    <div class="min-h-screen bg-[#c4c470]">
        <AppSidebar />

        <main class="min-h-screen lg:pl-72">
            <slot />
        </main>
    </div>
</template>

