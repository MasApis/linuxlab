<script setup lang="ts">
import BadgeIcon from '@/Components/BadgeIcon.vue';

interface Props {
    name: string;
    description: string;
    criteria?: string;
    isUnlocked?: boolean;
    unlockedAt?: string;
    icon?: string;
}

withDefaults(defineProps<Props>(), {
    criteria: undefined,
    isUnlocked: false,
    unlockedAt: undefined,
    icon: 'trophy',
});
</script>

<template>
    <div
        class="relative flex flex-col items-center text-center p-5 rounded-card border transition-all duration-200"
        :class="[
            isUnlocked
                ? 'bg-surface-card border-brand-secondary/40 shadow-md shadow-brand-secondary/5 hover:border-brand-secondary/70'
                : 'bg-surface-card/60 border-border/50 opacity-70 hover:opacity-90',
        ]"
    >
        <!-- Badge Icon Container -->
        <div class="relative mb-3">
            <div
                class="w-16 h-16 rounded-full flex items-center justify-center border-2 transition-transform duration-200"
                :class="[
                    isUnlocked
                        ? 'border-brand-secondary bg-brand-secondary/15 text-brand-secondary shadow-lg shadow-brand-secondary/20 scale-105'
                        : 'border-border bg-surface-high text-text-dim',
                ]"
            >
                <!-- Unlocked Icon -->
                <BadgeIcon
                    v-if="isUnlocked"
                    :icon="icon"
                    size="lg"
                />

                <!-- Locked Icon (Padlock) -->
                <svg
                    v-else
                    class="w-7 h-7"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                    />
                </svg>
            </div>

            <!-- Status Pill -->
            <span
                class="absolute -bottom-1 -right-1 text-[10px] font-semibold px-2 py-0.5 rounded-badge uppercase tracking-wider"
                :class="[
                    isUnlocked
                        ? 'bg-brand-secondary text-brand-secondary-dark'
                        : 'bg-surface-highest text-text-dim border border-border/50',
                ]"
            >
                {{ isUnlocked ? 'Diraih' : 'Terkunci' }}
            </span>
        </div>

        <!-- Name & Description -->
        <h4
            class="text-sm font-semibold tracking-tight transition-colors"
            :class="isUnlocked ? 'text-text-primary' : 'text-text-muted'"
        >
            {{ name }}
        </h4>

        <p class="text-xs text-text-muted mt-1 leading-relaxed">
            {{ description }}
        </p>

        <!-- Criteria / Unlocked Date Footer -->
        <div class="mt-3 pt-3 w-full border-t border-border/40 text-[11px] leading-tight">
            <span v-if="isUnlocked && unlockedAt" class="text-brand-secondary font-medium">
                Diraih pada: {{ unlockedAt }}
            </span>
            <span v-else-if="criteria" class="text-text-dim">
                Syarat: {{ criteria }}
            </span>
            <span v-else class="text-text-dim">
                Belum diperoleh
            </span>
        </div>
    </div>
</template>
