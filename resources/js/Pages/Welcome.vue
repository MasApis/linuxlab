<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import AppCard from '@/Components/AppCard.vue';
import AppButton from '@/Components/AppButton.vue';
import type { Module } from '@/types';

interface Props {
    modules?: Module[];
}

defineProps<Props>();
</script>

<template>
    <PublicLayout>
        <Head title="LinuxLab - Belajar Linux Interaktif untuk Pemula" />

        <div class="py-12 sm:py-16 space-y-16">
            <!-- Hero Section -->
            <div class="text-center space-y-6 max-w-3xl mx-auto px-4">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-brand-primary/10 text-brand-primary-light text-xs font-mono font-medium border border-brand-primary/20">
                    <span class="w-2 h-2 rounded-full bg-brand-primary animate-pulse"></span>
                    <span>TERMINAL NYATA BERBASIS BROWSER</span>
                </div>

                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white tracking-tight leading-tight">
                    Belajar Linux Praktis, <br />
                    <span class="text-brand-primary">Langsung dari Browser.</span>
                </h1>

                <p class="text-text-muted text-base sm:text-lg max-w-2xl mx-auto leading-relaxed">
                    Kuasai baris perintah Linux di lingkungan container terisolasi yang aman. Tanpa instalasi rumit, dilengkapi panduan materi terstruktur dan latihan dengan penilaian instan.
                </p>

                <div class="flex flex-wrap items-center justify-center gap-4 pt-2">
                    <Link :href="route('register')">
                        <AppButton variant="primary" size="lg" class="shadow-xl shadow-brand-primary/20">
                            <span>Daftar Akun Gratis</span>
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </AppButton>
                    </Link>
                    <Link :href="route('login')">
                        <AppButton variant="secondary" size="lg">
                            Masuk ke Akun
                        </AppButton>
                    </Link>
                </div>
            </div>

            <!-- Initial Modules Section (SPEC 10 Table Row 1) -->
            <div class="space-y-6">
                <div class="text-center space-y-2">
                    <h2 class="text-2xl sm:text-3xl font-bold text-white">
                        Kurikulum Pembelajaran Awal
                    </h2>
                    <p class="text-sm text-text-muted max-w-xl mx-auto">
                        Tiga modul inti terstruktur untuk membangun fondasi Linux Anda dari nol.
                    </p>
                </div>

                <div v-if="modules && modules.length > 0" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <AppCard
                        v-for="module in modules"
                        :key="module.id"
                        class="flex flex-col justify-between hover:border-brand-primary/40 transition-all duration-200 group"
                    >
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-mono font-bold px-2.5 py-0.5 rounded bg-brand-primary/10 text-brand-primary border border-brand-primary/20">
                                    MODUL 0{{ module.order }}
                                </span>
                                <span class="text-xs font-mono text-text-dim">
                                    {{ module.lessons_count }} Pelajaran
                                </span>
                            </div>

                            <h3 class="text-xl font-bold text-white group-hover:text-brand-primary-light transition-colors">
                                {{ module.title }}
                            </h3>

                            <p class="text-sm text-text-muted leading-relaxed line-clamp-3">
                                {{ module.description }}
                            </p>
                        </div>

                        <div class="pt-6 mt-6 border-t border-border/60">
                            <Link :href="route('modules.show', module.slug)" class="block">
                                <AppButton variant="secondary" size="sm" class="w-full justify-center">
                                    <span>Pelajari Modul</span>
                                    <svg class="w-4 h-4 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </AppButton>
                            </Link>
                        </div>
                    </AppCard>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
