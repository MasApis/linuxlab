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
    'inline-flex items-center justify-center font-display font-bold uppercase tracking-wide transition-all duration-150 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-brand-primary focus-visible:ring-offset-surface-base disabled:opacity-50 disabled:cursor-not-allowed disabled:active:translate-y-0 disabled:shadow-none select-none rounded-button border-2';

const variantClasses = computed(() => {
    switch (props.variant) {
        case 'secondary':
            return 'bg-surface-card text-text-muted border-border shadow-none hover:border-border-focus hover:text-text-primary active:bg-surface-high';
        case 'accent':
            return 'bg-brand-tertiary text-white border-transparent shadow-[0_4px_0_0_#1899d6] hover:brightness-105 active:translate-y-[4px] active:shadow-none';
        case 'danger':
            return 'bg-brand-danger text-white border-transparent shadow-[0_4px_0_0_#9e1f1f] hover:brightness-105 active:translate-y-[4px] active:shadow-none';
        case 'ghost':
            return 'bg-transparent text-text-muted border-transparent shadow-none hover:text-text-primary hover:bg-surface-high active:bg-surface-highest';
        case 'primary':
        default:
            return 'bg-brand-primary text-white border-transparent shadow-[0_4px_0_0_#245700] hover:brightness-125 active:translate-y-[4px] active:shadow-none';
    }
});

const sizeClasses = computed(() => {
    switch (props.size) {
        case 'sm':
            return 'min-h-10 px-4 py-1.5 text-xs gap-1.5';
        case 'lg':
            return 'min-h-14 px-8 py-3 text-base gap-2.5';
        case 'md':
        default:
            return 'min-h-12 px-6 py-2 text-sm gap-2';
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
