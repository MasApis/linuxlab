<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    value: number;
    max?: number;
    label?: string;
    showPercentage?: boolean;
    size?: 'sm' | 'md' | 'lg';
    variant?: 'primary' | 'secondary' | 'tertiary';
}

const props = withDefaults(defineProps<Props>(), {
    max: 100,
    label: undefined,
    showPercentage: true,
    size: 'md',
    variant: 'primary',
});

const percentage = computed(() => {
    if (props.max <= 0) return 0;
    const clamped = Math.min(Math.max(props.value, 0), props.max);
    return Math.round((clamped / props.max) * 100);
});

const barColor = computed(() => {
    switch (props.variant) {
        case 'secondary':
            return 'bg-brand-secondary';
        case 'tertiary':
            return 'bg-brand-tertiary';
        case 'primary':
        default:
            return 'bg-brand-primary';
    }
});

const trackHeight = computed(() => {
    switch (props.size) {
        case 'sm':
            return 'h-1.5';
        case 'lg':
            return 'h-3';
        case 'md':
        default:
            return 'h-2';
    }
});
</script>

<template>
    <div class="w-full flex flex-col gap-1.5">
        <!-- Label & Percentage Header -->
        <div
            v-if="label || showPercentage"
            class="flex items-center justify-between text-xs"
        >
            <span v-if="label" class="font-medium text-text-muted">
                {{ label }}
            </span>
            <span
                v-if="showPercentage"
                class="font-semibold tabular-nums text-text-primary ml-auto"
            >
                {{ percentage }}%
            </span>
        </div>

        <!-- Progress Track -->
        <div
            class="w-full bg-surface-high rounded-full overflow-hidden"
            :class="trackHeight"
            role="progressbar"
            :aria-valuenow="percentage"
            aria-valuemin="0"
            aria-valuemax="100"
        >
            <div
                class="h-full rounded-full transition-all duration-500 ease-out shadow-sm"
                :class="barColor"
                :style="{ width: `${percentage}%` }"
            ></div>
        </div>
    </div>
</template>
