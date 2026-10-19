<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';
import AppCard from '@/Components/AppCard.vue';
import AppButton from '@/Components/AppButton.vue';
import ProgressBar from '@/Components/ProgressBar.vue';
import type { Module } from '@/types';

interface Props {
    modules?: Module[];
}

defineProps<Props>();

const page = usePage();
const user = page.props.auth?.user as { name?: string; username?: string; email?: string } | undefined;
</script>

<template>
    <UserLayout>
        <Head title="Dashboard - LinuxLab" />

        <div class="py-8 space-y-8">
            <!-- Welcome Header Card -->
            <AppCard class="relative overflow-hidden bg-gradient-to-r from-surface-card to-surface-elevated border-border p-6 sm:p-8">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="space-y-2">
                        <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-brand-primary/10 text-brand-primary-light text-xs font-mono font-medium border border-brand-primary/20">
                            <span class="w-1.5 h-1.5 rounded-full bg-brand-primary animate-pulse"></span>
                            <span>SESI AKTIF</span>
                        </div>
                        <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                            Selamat Datang, {{ user?.name || 'Pengguna' }}!
                        </h1>
                        <p class="text-sm text-text-muted font-mono">
                            Username: @{{ user?.username || 'user' }}
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <Link :href="route('modules.index')">
                            <AppButton variant="primary" size="md">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                <span>Mulai Belajar</span>
                            </AppButton>
                        </Link>
                        <Link :href="route('profile.edit')">
                            <AppButton variant="secondary" size="md">
                                Kelola Profil
                            </AppButton>
                        </Link>
                    </div>
                </div>
            </AppCard>

            <!-- Learning Modules Grid (Wireframe 18_wf_dashboard) -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-white">
                            Modul Pembelajaran Tersedia
                        </h2>
                        <p class="text-xs sm:text-sm text-text-muted">
                            Mulai perjalanan Anda dari dasar sistem berkas Linux hingga pemrosesan teks.
                        </p>
                    </div>
                    <Link :href="route('modules.index')" class="text-xs sm:text-sm font-semibold text-brand-primary hover:text-brand-primary-light transition-colors">
                        Lihat Semua &rarr;
                    </Link>
                </div>

                <div v-if="modules && modules.length > 0" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <AppCard
                        v-for="module in modules"
                        :key="module.id"
                        class="flex flex-col justify-between hover:border-border-focus transition-all duration-150 group"
                    >
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-mono font-bold px-2 py-0.5 rounded bg-surface-elevated text-brand-primary border border-brand-primary/20">
                                    MODUL 0{{ module.order }}
                                </span>
                                <span class="text-xs font-mono text-text-dim">
                                    {{ module.lessons_count }} Pelajaran
                                </span>
                            </div>

                            <h3 class="text-lg font-bold text-white group-hover:text-brand-primary-light transition-colors">
                                {{ module.title }}
                            </h3>

                            <p class="text-xs text-text-muted line-clamp-2 leading-relaxed">
                                {{ module.description }}
                            </p>
                        </div>

                        <div class="pt-4 mt-4 border-t border-border/60 space-y-3">
                            <div class="space-y-1">
                                <div class="flex justify-between text-xs">
                                    <span class="text-text-dim">Progres</span>
                                    <span class="font-mono text-text-muted">{{ module.completion_percentage ?? 0 }}%</span>
                                </div>
                                <ProgressBar
                                    :value="module.completion_percentage ?? 0"
                                    :show-percentage="false"
                                    size="sm"
                                />
                            </div>

                            <Link :href="route('modules.show', module.slug)" class="block">
                                <AppButton variant="secondary" size="sm" class="w-full justify-center">
                                    Buka Modul
                                </AppButton>
                            </Link>
                        </div>
                    </AppCard>
                </div>
            </div>
        </div>
    </UserLayout>
</template>
