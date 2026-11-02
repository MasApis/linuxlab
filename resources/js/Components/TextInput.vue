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
        class="rounded-input border-2 border-border bg-surface-card text-text-primary placeholder:text-text-dim focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/30 px-4 py-3 text-sm transition-colors"
        v-model="model"
        ref="input"
    />
</template>
