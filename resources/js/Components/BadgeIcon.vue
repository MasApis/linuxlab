<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    icon?: string;
    size?: 'sm' | 'md' | 'lg' | 'xl';
}

const props = withDefaults(defineProps<Props>(), {
    icon: 'trophy',
    size: 'md',
});

// Deteksi apakah icon berupa emoji (panjang karakter pendek atau karakter non-ASCII)
const isEmoji = computed(() => {
    if (!props.icon) return false;
    // Deteksi emoji via regex Unicode
    const emojiRegex = /(\p{Emoji_Presentation}|\p{Extended_Pictographic})/u;
    return emojiRegex.test(props.icon.trim()) && props.icon.trim().length <= 4;
});

const sizeClasses = computed(() => {
    switch (props.size) {
        case 'sm':
            return 'w-4 h-4 text-xs';
        case 'lg':
            return 'w-8 h-8 text-2xl';
        case 'xl':
            return 'w-10 h-10 text-3xl';
        case 'md':
        default:
            return 'w-6 h-6 text-lg';
    }
});

// Normalisasi nama icon
const normalizedIcon = computed(() => (props.icon ? props.icon.trim().toLowerCase() : 'trophy'));
</script>

<template>
    <!-- 1. Jika berupa Emoji (misal 🏆, 🚀, 📁, dll.) -->
    <span v-if="isEmoji" class="inline-flex items-center justify-center select-none" :class="sizeClasses">
        {{ icon }}
    </span>

    <!-- 2. Ikon SVG Berdasarkan Nama Preset -->
    <!-- Rocket / Langkah Pertama -->
    <svg
        v-else-if="normalizedIcon === 'rocket_launch' || normalizedIcon === 'rocket'"
        class="inline-block shrink-0"
        :class="sizeClasses"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor"
    >
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.59 14.37a6 6 0 01-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 006.16-12.12A14.98 14.98 0 009.63 8.41m5.96 5.96a14.926 14.926 0 01-5.841 2.58m-.119-8.54a6 6 0 00-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 00-2.58 5.84m2.699 2.7a14.965 14.965 0 01-2.699-2.7m0 0L3 21l3.5-1.5" />
    </svg>

    <!-- Folder Open / Penjelajah File System -->
    <svg
        v-else-if="normalizedIcon === 'folder_open' || normalizedIcon === 'folder'"
        class="inline-block shrink-0"
        :class="sizeClasses"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor"
    >
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z" />
    </svg>

    <!-- Security / Penjaga Izin -->
    <svg
        v-else-if="normalizedIcon === 'security' || normalizedIcon === 'shield'"
        class="inline-block shrink-0"
        :class="sizeClasses"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor"
    >
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
    </svg>

    <!-- Terminal / Ahli Teks -->
    <svg
        v-else-if="normalizedIcon === 'terminal' || normalizedIcon === 'code'"
        class="inline-block shrink-0"
        :class="sizeClasses"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor"
    >
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
    </svg>

    <!-- School / Pembelajar Tekun -->
    <svg
        v-else-if="normalizedIcon === 'school' || normalizedIcon === 'academic'"
        class="inline-block shrink-0"
        :class="sizeClasses"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor"
    >
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5" />
    </svg>

    <!-- Help / Suka Bertanya -->
    <svg
        v-else-if="normalizedIcon === 'help' || normalizedIcon === 'question'"
        class="inline-block shrink-0"
        :class="sizeClasses"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor"
    >
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>

    <!-- Verified / Penolong Komunitas -->
    <svg
        v-else-if="normalizedIcon === 'verified' || normalizedIcon === 'check_circle'"
        class="inline-block shrink-0"
        :class="sizeClasses"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor"
    >
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
    </svg>

    <!-- Trophy / Default Award -->
    <svg
        v-else
        class="inline-block shrink-0"
        :class="sizeClasses"
        fill="currentColor"
        viewBox="0 0 24 24"
    >
        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
    </svg>
</template>
