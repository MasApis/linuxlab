<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AppCard from '@/Components/AppCard.vue';

const page = usePage();
const user = page.props.auth?.user as { name?: string; email?: string } | undefined;
</script>

<template>
    <AdminLayout>
        <Head title="Panel Administrator - LinuxLab" />

        <div class="space-y-6">
            <!-- Header Ringkasan -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-white tracking-tight">Dashboard Administrator</h1>
                    <p class="text-sm text-text-muted mt-1">
                        Selamat datang di panel pengelolaan LinuxLab, {{ user?.name || 'Admin' }}.
                    </p>
                </div>

                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-button bg-brand-primary/10 border border-brand-primary/20 text-brand-primary-light text-xs font-mono">
                    <span class="w-2 h-2 rounded-full bg-brand-primary"></span>
                    <span>ROLE: ADMINISTRATOR</span>
                </div>
            </div>

            <!-- Placeholder Konten Admin -->
            <AppCard>
                <div class="text-center py-12 space-y-3">
                    <div class="w-12 h-12 rounded-full bg-brand-primary/10 border border-brand-primary/30 text-brand-primary flex items-center justify-center mx-auto">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white">
                        Panel Administrasi LinuxLab
                    </h3>
                    <p class="text-sm text-text-muted max-w-lg mx-auto">
                        Manajemen modul, pelajaran, latihan, lencana, moderasi forum, dan kelola pengguna akan diimplementasikan pada tahap masing-masing (Fase 2, Fase 4, & Fase 5).
                    </p>
                </div>
            </AppCard>
        </div>
    </AdminLayout>
</template>
