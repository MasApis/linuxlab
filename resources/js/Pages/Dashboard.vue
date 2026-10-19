<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';
import AppCard from '@/Components/AppCard.vue';
import AppButton from '@/Components/AppButton.vue';

const page = usePage();
const user = page.props.auth?.user as { name?: string; username?: string; email?: string } | undefined;
</script>

<template>
    <UserLayout>
        <Head title="Dashboard - LinuxLab" />

        <div class="py-8 space-y-6">
            <!-- Welcome Header Card -->
            <AppCard class="relative overflow-hidden bg-gradient-to-r from-surface-card to-surface-elevated border-border">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-brand-primary/10 text-brand-primary-light text-xs font-mono font-medium mb-2 border border-brand-primary/20">
                            <span class="w-1.5 h-1.5 rounded-full bg-brand-primary animate-pulse"></span>
                            <span>SESI AKTIF</span>
                        </div>
                        <h1 class="text-2xl font-bold text-white tracking-tight">
                            Selamat Datang, {{ user?.name || 'Pengguna' }}!
                        </h1>
                        <p class="text-sm text-text-muted mt-1 font-mono">
                            Username: @{{ user?.username || 'user' }}
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <Link :href="route('profile.edit')">
                            <AppButton variant="secondary" size="sm">
                                Kelola Profil
                            </AppButton>
                        </Link>
                    </div>
                </div>
            </AppCard>

            <!-- Dashboard Content Placeholder -->
            <AppCard>
                <div class="text-center py-10 space-y-3">
                    <div class="w-12 h-12 rounded-full bg-brand-primary/10 border border-brand-primary/30 text-brand-primary flex items-center justify-center mx-auto">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white">
                        Dashboard Pembelajaran LinuxLab
                    </h3>
                    <p class="text-sm text-text-muted max-w-md mx-auto">
                        Modul belajar, kartu progres, dan riwayat latihan akan ditampilkan di sini pada tahap berikutnya (Fase 2 & Fase 5).
                    </p>
                </div>
            </AppCard>
        </div>
    </UserLayout>
</template>
