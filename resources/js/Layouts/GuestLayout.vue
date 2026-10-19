<script setup lang="ts">
import AppLogo from '@/Components/AppLogo.vue';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-surface-base px-4 py-8 sm:justify-center font-sans text-text-primary"
    >
        <div class="mb-6">
            <AppLogo size="lg" href="/" :show-subtitle="true" />
        </div>

        <div
            class="w-full max-w-md rounded-card border border-border bg-surface-card p-6 sm:p-8 shadow-xl"
        >
            <slot />
        </div>
    </div>
</template>
