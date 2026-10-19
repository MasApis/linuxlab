<script setup lang="ts">
import UserLayout from '@/Layouts/UserLayout.vue';
</script>

<template>
    <UserLayout>
        <!-- Optional Page Header Slot -->
        <header v-if="$slots.header" class="mb-6 pb-4 border-b border-border/60">
            <slot name="header" />
        </header>

        <!-- Page Content -->
        <slot />
    </UserLayout>
</template>
