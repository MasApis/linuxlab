<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

withDefaults(
    defineProps<{
        href?: string;
        size?: 'sm' | 'md' | 'lg';
        showSubtitle?: boolean;
    }>(),
    {
        href: '/',
        size: 'md',
        showSubtitle: false,
    }
);
</script>

<template>
    <Link :href="href" class="inline-flex items-center gap-2.5 group transition-opacity hover:opacity-90">
        <!-- Terminal Icon Prompt -->
        <div
            class="flex items-center justify-center font-mono font-bold rounded-lg border border-brand-primary/30 bg-surface-elevated text-brand-primary shadow-sm group-hover:border-brand-primary/60 transition-colors"
            :class="{
                'h-7 w-7 text-xs': size === 'sm',
                'h-9 w-9 text-sm': size === 'md',
                'h-11 w-11 text-base': size === 'lg',
            }"
        >
            <span>&gt;_</span>
        </div>

        <div class="flex flex-col leading-none">
            <span
                class="font-bold tracking-tight text-text-primary group-hover:text-white transition-colors"
                :class="{
                    'text-base': size === 'sm',
                    'text-lg': size === 'md',
                    'text-2xl': size === 'lg',
                }"
            >
                Linux<span class="text-brand-primary">Lab</span>
            </span>
            <span v-if="showSubtitle" class="text-[10px] text-text-dim uppercase tracking-wider font-medium mt-0.5">
                Terminal Interaktif
            </span>
        </div>
    </Link>
</template>
