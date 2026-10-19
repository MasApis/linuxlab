<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        status?: number;
        message?: string;
    }>(),
    {
        status: 404,
        message: 'Halaman Tidak Ditemukan',
    }
);

const title = computed(() => {
    return {
        404: '404 - Halaman Tidak Ditemukan',
        403: '403 - Akses Ditolak',
        500: '500 - Kesalahan Server',
        503: '503 - Layanan Tidak Tersedia',
    }[props.status] || `${props.status} - Terjadi Kesalahan`;
});

const description = computed(() => {
    return {
        404: 'Maaf, halaman yang Anda cari tidak ditemukan atau telah dipindahkan.',
        403: 'Maaf, Anda tidak memiliki izin untuk mengakses halaman ini.',
        500: 'Terjadi kesalahan internal pada server kami. Silakan coba beberapa saat lagi.',
        503: 'Layanan sedang dalam pemeliharaan. Silakan kembali lagi nanti.',
    }[props.status] || props.message || 'Terjadi kesalahan tak terduga.';
});
</script>

<template>
    <Head :title="title" />
    <div class="min-h-screen bg-slate-900 text-slate-100 flex flex-col items-center justify-center p-6">
        <div class="text-center max-w-md mx-auto">
            <p class="text-6xl font-extrabold text-emerald-400 font-mono mb-2">
                {{ status }}
            </p>
            <h1 class="text-2xl font-bold text-slate-100 mb-3">
                {{ title }}
            </h1>
            <p class="text-slate-400 text-sm mb-6">
                {{ description }}
            </p>
            <Link
                href="/"
                class="inline-flex items-center px-4 py-2 border border-emerald-500/30 rounded-lg text-sm font-medium text-emerald-400 bg-emerald-500/10 hover:bg-emerald-500/20 transition-colors"
            >
                Kembali ke Beranda
            </Link>
        </div>
    </div>
</template>
