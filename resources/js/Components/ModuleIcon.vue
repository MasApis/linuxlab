<script setup lang="ts">
import { computed } from 'vue';

// Lingkaran ikon modul sesuai Design System Pencil (IconCircle 48px).
// order 1: File System (hijau), 2: Permission (biru),
// 3: Text Processing (jingga), 4: Jaringan (ungu).
interface Props {
    order?: number;
}

const props = withDefaults(defineProps<Props>(), {
    order: 1,
});

const palette = computed(() => {
    switch (props.order) {
        case 2:
            return { bg: '#D6EEFF', fg: '#095F8F' };
        case 3:
            return { bg: '#FFE9C7', fg: '#A35C00' };
        case 4:
            return { bg: '#EFE0FF', fg: '#7B3FC4' };
        case 1:
        default:
            return { bg: '#D7FFB8', fg: '#2F6E00' };
    }
});
</script>

<template>
    <div
        class="w-12 h-12 rounded-full flex items-center justify-center shrink-0"
        :style="{ backgroundColor: palette.bg, color: palette.fg }"
        aria-hidden="true"
    >
        <!-- Modul 1: folder-tree -->
        <svg
            v-if="order === 1"
            class="w-6 h-6"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
        >
            <path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
            <circle cx="9" cy="13.5" r="1" fill="currentColor" stroke="none" />
            <circle cx="15" cy="13.5" r="1" fill="currentColor" stroke="none" />
            <circle cx="12" cy="17" r="1" fill="currentColor" stroke="none" />
            <path d="M9.5 14.5 12 16l2.5-1.5" />
        </svg>
        <!-- Modul 2: shield-check -->
        <svg
            v-else-if="order === 2"
            class="w-6 h-6"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
        >
            <path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1 1 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z" />
            <path d="m9 12 2 2 4-4" />
        </svg>
        <!-- Modul 3: file-text -->
        <svg
            v-else-if="order === 3"
            class="w-6 h-6"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
        >
            <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z" />
            <path d="M14 2v4a2 2 0 0 0 2 2h4" />
            <path d="M10 9H8" />
            <path d="M16 13H8" />
            <path d="M16 17H8" />
        </svg>
        <!-- Modul 4+: network -->
        <svg
            v-else
            class="w-6 h-6"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
        >
            <rect x="16" y="16" width="6" height="6" rx="1" />
            <rect x="2" y="16" width="6" height="6" rx="1" />
            <rect x="9" y="2" width="6" height="6" rx="1" />
            <path d="M5 16v-3a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3" />
            <path d="M12 12V8" />
        </svg>
    </div>
</template>
