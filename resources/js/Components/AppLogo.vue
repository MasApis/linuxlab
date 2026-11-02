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
            class="flex items-center justify-center rounded-xl bg-brand-primary text-white shadow-sm transition-transform group-active:translate-y-px"
            :class="{
                'h-7 w-7': size === 'sm',
                'h-9 w-9': size === 'md',
                'h-11 w-11': size === 'lg',
            }"
        >
            <svg class="w-[55%] h-[55%]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
        </div>

        <div class="flex flex-col leading-none">
            <span
                class="font-display font-bold tracking-tight text-brand-primary-light"
                :class="{
                    'text-base': size === 'sm',
                    'text-lg': size === 'md',
                    'text-2xl': size === 'lg',
                }"
            >
                LinuxLab
            </span>
            <span v-if="showSubtitle" class="text-[10px] text-text-dim uppercase tracking-wider font-medium mt-0.5">
                Terminal Interaktif
            </span>
        </div>
    </Link>
</template>
