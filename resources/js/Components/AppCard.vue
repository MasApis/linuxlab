<script setup lang="ts">
interface Props {
    title?: string;
    subtitle?: string;
    hoverable?: boolean;
    compact?: boolean;
}

withDefaults(defineProps<Props>(), {
    title: undefined,
    subtitle: undefined,
    hoverable: false,
    compact: false,
});
</script>

<template>
    <div
        class="rounded-card border border-border bg-surface-card transition-all duration-200"
        :class="{
            'hover:border-border-focus hover:bg-surface-card-hover hover:shadow-md hover:shadow-black/[0.06]': hoverable,
            'p-4': compact,
            'p-6': !compact,
        }"
    >
        <!-- Header -->
        <div
            v-if="title || subtitle || $slots.header || $slots['header-action']"
            class="flex items-start justify-between gap-4 mb-4"
        >
            <slot name="header">
                <div>
                    <h3 v-if="title" class="text-base font-semibold text-text-primary">
                        {{ title }}
                    </h3>
                    <p v-if="subtitle" class="text-xs text-text-muted mt-0.5">
                        {{ subtitle }}
                    </p>
                </div>
            </slot>

            <div v-if="$slots['header-action']" class="shrink-0">
                <slot name="header-action" />
            </div>
        </div>

        <!-- Body Content -->
        <div class="text-text-secondary text-sm">
            <slot />
        </div>

        <!-- Footer -->
        <div
            v-if="$slots.footer"
            class="mt-5 pt-4 border-t border-border/60 flex items-center justify-between gap-3 text-xs text-text-dim"
        >
            <slot name="footer" />
        </div>
    </div>
</template>
