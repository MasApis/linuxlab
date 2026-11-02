<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps<{
    href: string;
    active?: boolean;
}>();

const classes = computed(() =>
    props.active
        ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-brand-primary text-start text-base font-medium text-text-primary bg-brand-primary/10 focus:outline-none focus:text-text-primary focus:bg-brand-primary/10 focus:border-brand-primary-hover transition duration-150 ease-in-out'
        : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-text-muted hover:text-text-primary hover:bg-surface-high hover:border-border-focus focus:outline-none focus:text-text-primary focus:bg-surface-high focus:border-border-focus transition duration-150 ease-in-out',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
