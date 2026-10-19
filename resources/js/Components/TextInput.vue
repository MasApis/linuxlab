<script setup lang="ts">
import { onMounted, ref } from 'vue';

const model = defineModel<string>({ required: true });

const input = ref<HTMLInputElement | null>(null);

onMounted(() => {
    if (input.value?.hasAttribute('autofocus')) {
        input.value?.focus();
    }
});

defineExpose({ focus: () => input.value?.focus() });
</script>

<template>
    <input
        class="rounded-input border border-border bg-surface-card text-text-primary placeholder:text-text-dim focus:border-brand-primary focus:ring-1 focus:ring-brand-primary px-3.5 py-2.5 text-sm transition-colors"
        v-model="model"
        ref="input"
    />
</template>
