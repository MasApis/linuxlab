<script setup lang="ts">
interface Props {
    title: string;
    description: string;
    icon?: 'progress' | 'exercise' | 'forum' | 'badge' | 'search' | 'default';
}

withDefaults(defineProps<Props>(), {
    icon: 'default',
});
</script>

<template>
    <div
        class="flex flex-col items-center justify-center text-center p-8 sm:p-12 rounded-card border border-dashed border-border bg-surface-card/40 my-4"
    >
        <!-- Icon Container -->
        <div class="mb-4 flex items-center justify-center w-14 h-14 rounded-2xl bg-surface-elevated border border-border text-text-muted shadow-inner">
            <slot name="icon">
                <!-- Progress Icon -->
                <svg
                    v-if="icon === 'progress'"
                    class="w-7 h-7 text-brand-primary"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="1.75"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                    />
                </svg>

                <!-- Exercise/Terminal Icon -->
                <svg
                    v-else-if="icon === 'exercise'"
                    class="w-7 h-7 text-brand-tertiary"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="1.75"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                    />
                </svg>

                <!-- Forum Icon -->
                <svg
                    v-else-if="icon === 'forum'"
                    class="w-7 h-7 text-brand-secondary"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="1.75"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
                    />
                </svg>

                <!-- Badge Icon -->
                <svg
                    v-else-if="icon === 'badge'"
                    class="w-7 h-7 text-brand-secondary"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="1.75"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"
                    />
                </svg>

                <!-- Search / Generic Empty Icon -->
                <svg
                    v-else
                    class="w-7 h-7 text-text-dim"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="1.75"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"
                    />
                </svg>
            </slot>
        </div>

        <!-- Title -->
        <h3 class="text-base font-semibold text-text-primary">
            {{ title }}
        </h3>

        <!-- Description -->
        <p class="text-xs text-text-muted mt-1.5 max-w-sm leading-relaxed">
            {{ description }}
        </p>

        <!-- Action Slot -->
        <div v-if="$slots.action" class="mt-5">
            <slot name="action" />
        </div>
    </div>
</template>
