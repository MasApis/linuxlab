<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

interface Props {
    variant?: 'primary' | 'secondary' | 'accent' | 'danger' | 'ghost';
    size?: 'sm' | 'md' | 'lg';
    type?: 'button' | 'submit' | 'reset';
    disabled?: boolean;
    loading?: boolean;
    href?: string;
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'primary',
    size: 'md',
    type: 'button',
    disabled: false,
    loading: false,
    href: undefined,
});

const baseClasses =
    'inline-flex items-center justify-center font-medium transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-surface-base disabled:opacity-50 disabled:cursor-not-allowed select-none rounded-button';

const variantClasses = computed(() => {
    switch (props.variant) {
        case 'secondary':
            return 'bg-surface-elevated text-text-primary border border-border hover:bg-surface-high hover:border-border-focus focus:ring-slate-400/30 active:bg-surface-highest';
        case 'accent':
            return 'bg-brand-secondary text-brand-secondary-dark font-semibold hover:bg-brand-secondary-hover focus:ring-brand-secondary/50 active:bg-brand-secondary-hover';
        case 'danger':
            return 'bg-brand-danger text-white font-semibold hover:bg-brand-danger-hover focus:ring-brand-danger/50 active:bg-brand-danger-hover';
        case 'ghost':
            return 'bg-transparent text-text-muted hover:text-text-primary hover:bg-surface-card focus:ring-slate-400/30 active:bg-surface-elevated';
        case 'primary':
        default:
            return 'bg-brand-primary text-brand-primary-dark font-semibold hover:bg-brand-primary-hover focus:ring-brand-primary/50 active:bg-brand-primary-hover';
    }
});

const sizeClasses = computed(() => {
    switch (props.size) {
        case 'sm':
            return 'px-3 py-1.5 text-xs gap-1.5';
        case 'lg':
            return 'px-6 py-3 text-base gap-2.5';
        case 'md':
        default:
            return 'px-4 py-2 text-sm gap-2';
    }
});
</script>

<template>
    <Link
        v-if="href && !disabled"
        :href="href"
        :class="[baseClasses, variantClasses, sizeClasses]"
    >
        <svg
            v-if="loading"
            class="animate-spin -ml-0.5 h-4 w-4 shrink-0"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
        >
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
        </svg>
        <slot />
    </Link>

    <button
        v-else
        :type="type"
        :disabled="disabled || loading"
        :class="[baseClasses, variantClasses, sizeClasses]"
    >
        <svg
            v-if="loading"
            class="animate-spin -ml-0.5 h-4 w-4 shrink-0"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
        >
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
        </svg>
        <slot />
    </button>
</template>
