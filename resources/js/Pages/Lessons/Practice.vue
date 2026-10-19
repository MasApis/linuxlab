<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';
import AppCard from '@/Components/AppCard.vue';
import AppButton from '@/Components/AppButton.vue';

interface Props {
    module: {
        id: number;
        title: string;
        slug: string;
    };
    lesson: {
        id: number;
        title: string;
        slug: string;
        order: number;
    };
}

defineProps<Props>();
</script>

<template>
    <UserLayout>
        <Head :title="`Praktik: ${lesson.title} - LinuxLab`" />

        <div class="py-8 space-y-6 max-w-4xl mx-auto">
            <!-- Breadcrumbs -->
            <nav class="flex items-center gap-2 text-xs sm:text-sm text-text-muted font-medium">
                <Link :href="route('dashboard')" class="hover:text-brand-primary transition-colors flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Beranda</span>
                </Link>
                <span class="text-text-dim">/</span>
                <Link :href="route('modules.show', module.slug)" class="hover:text-brand-primary transition-colors">
                    {{ module.title }}
                </Link>
                <span class="text-text-dim">/</span>
                <Link :href="route('lessons.show', [module.slug, lesson.slug])" class="hover:text-brand-primary transition-colors">
                    {{ lesson.title }}
                </Link>
                <span class="text-text-dim">/</span>
                <span class="text-white font-semibold">Praktik</span>
            </nav>

            <!-- Main Practice Card -->
            <AppCard class="border-border p-6 sm:p-10 space-y-6">
                <!-- Header -->
                <div class="space-y-3 text-center sm:text-left">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-brand-primary/10 text-brand-primary text-xs font-mono font-bold border border-brand-primary/30">
                        <span class="w-2 h-2 rounded-full bg-brand-primary animate-pulse"></span>
                        <span>LABORATORIUM PRAKTIK TERMINAL</span>
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                        {{ lesson.title }}
                    </h1>

                    <p class="text-text-muted text-sm max-w-2xl">
                        Modul {{ module.title }} • Pelajaran {{ lesson.order }}
                    </p>
                </div>

                <!-- Terminal Simulation Placeholder Box -->
                <div class="rounded-xl overflow-hidden bg-surface-base border border-border shadow-2xl">
                    <!-- Terminal Bar -->
                    <div class="bg-surface-elevated px-4 py-2.5 border-b border-border flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded-full bg-red-500/80"></span>
                                <span class="w-3 h-3 rounded-full bg-yellow-500/80"></span>
                                <span class="w-3 h-3 rounded-full bg-green-500/80"></span>
                            </div>
                            <span class="font-mono text-xs text-text-dim ml-2">terminal-session-placeholder</span>
                        </div>
                        <span class="text-[11px] font-mono text-brand-primary bg-brand-primary/10 px-2 py-0.5 rounded border border-brand-primary/20">
                            Fase 2: Terhubung
                        </span>
                    </div>

                    <!-- Terminal Content -->
                    <div class="p-6 font-mono text-xs sm:text-sm space-y-4">
                        <div class="text-brand-primary-light">
                            LinuxLab Terminal Environment [v1.0]
                        </div>
                        <div class="text-text-muted">
                            Sesi praktik untuk pelajaran: <strong class="text-white">{{ lesson.title }}</strong>
                        </div>
                        <div class="p-4 rounded-lg bg-surface-card border border-border/70 text-text-muted text-xs leading-relaxed space-y-2">
                            <p class="text-brand-tertiary font-semibold flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Informasi Tahap Pengembangan</span>
                            </p>
                            <p>
                                Integrasi terminal Linux nyata berbasis container terisolasi (xterm.js + WebSocket + Docker) akan diaktifkan pada <strong class="text-white">Fase 3</strong>, dan penilaian latihan otomatis akan diaktifkan pada <strong class="text-white">Fase 4 & Fase 5</strong>.
                            </p>
                            <p>
                                Tautan "Mulai Praktik" telah aktif dan mengarah ke route ini (<code class="text-brand-primary">lessons.practice</code>) sesuai spesifikasi.
                            </p>
                        </div>

                        <div class="flex items-center gap-2 pt-2 text-text-dim">
                            <span class="text-brand-primary">student@linuxlab:~$</span>
                            <span class="animate-pulse">_</span>
                        </div>
                    </div>
                </div>

                <!-- Back Action -->
                <div class="flex items-center justify-between pt-4 border-t border-border">
                    <Link :href="route('lessons.show', [module.slug, lesson.slug])">
                        <AppButton variant="secondary" size="md">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            <span>Kembali ke Materi Pelajaran</span>
                        </AppButton>
                    </Link>

                    <Link :href="route('modules.show', module.slug)">
                        <AppButton variant="ghost" size="sm" class="text-text-muted hover:text-white">
                            <span>Daftar Pelajaran Modul</span>
                        </AppButton>
                    </Link>
                </div>
            </AppCard>
        </div>
    </UserLayout>
</template>
