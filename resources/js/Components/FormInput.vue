<script setup lang="ts">
import { computed, useId } from 'vue';
import InputError from '@/Components/InputError.vue';

interface Props {
    modelValue?: string | number;
    label?: string;
    id?: string;
    type?: string;
    placeholder?: string;
    error?: string;
    hint?: string;
    disabled?: boolean;
    required?: boolean;
    isMono?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: '',
    label: undefined,
    id: undefined,
    type: 'text',
    placeholder: undefined,
    error: undefined,
    hint: undefined,
    disabled: false,
    required: false,
    isMono: false,
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

const generatedId = useId();
const inputId = computed(() => props.id || generatedId);

const onInput = (event: Event) => {
    const target = event.target as HTMLInputElement;
    emit('update:modelValue', target.value);
};
</script>

<template>
    <div class="w-full flex flex-col gap-1.5">
        <label
            v-if="label"
            :for="inputId"
            class="text-[13px] font-extrabold uppercase tracking-wider text-text-muted flex items-center justify-between"
        >
            <span>
                {{ label }}
                <span v-if="required" class="text-brand-danger ml-0.5">*</span>
            </span>
            <slot name="label-extra" />
        </label>

        <div class="relative flex items-center">
            <div
                v-if="$slots.prefix"
                class="absolute left-3 flex items-center pointer-events-none text-text-dim"
            >
                <slot name="prefix" />
            </div>

            <input
                :id="inputId"
                :type="type"
                :value="modelValue"
                :placeholder="placeholder"
                :disabled="disabled"
                :required="required"
                @input="onInput"
                class="w-full rounded-input border-2 bg-surface-card text-text-primary placeholder:text-text-dim transition-colors duration-150 focus:outline-none text-sm px-4 py-4 disabled:opacity-50 disabled:cursor-not-allowed"
                :class="[
                    isMono ? 'font-mono' : 'font-sans',
                    error
                        ? 'border-brand-danger focus:border-brand-danger focus:ring-2 focus:ring-brand-danger/30'
                        : 'border-border focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/30',
                    $slots.prefix ? 'pl-9' : '',
                    $slots.suffix ? 'pr-9' : '',
                ]"
            />

            <div
                v-if="$slots.suffix"
                class="absolute right-3 flex items-center pointer-events-none text-text-dim"
            >
                <slot name="suffix" />
            </div>
        </div>

        <p v-if="hint && !error" class="text-xs text-text-dim">
            {{ hint }}
        </p>

        <InputError :message="error" />
    </div>
</template>
