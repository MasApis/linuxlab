<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    href: string;
}>();
</script>

<template>
    <Link
        :href="href"
        class="block w-full px-4 py-2 text-start text-sm leading-5 text-text-muted transition duration-150 ease-in-out hover:bg-surface-high hover:text-text-primary focus:bg-surface-high focus:text-text-primary focus:outline-none"
    >
        <slot />
    </Link>
</template>
