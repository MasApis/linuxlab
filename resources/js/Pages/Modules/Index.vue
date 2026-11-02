<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';
import AppCard from '@/Components/AppCard.vue';
import AppButton from '@/Components/AppButton.vue';
import ProgressBar from '@/Components/ProgressBar.vue';
import EmptyState from '@/Components/EmptyState.vue';
import ModuleIcon from '@/Components/ModuleIcon.vue';
import type { Module } from '@/types';

interface Props {
    modules: Module[];
}

defineProps<Props>();

const getModuleBadgeColor = (order: number) => {
    switch (order) {
        case 1:
            return 'bg-brand-primary/10 text-brand-primary-light border-brand-primary/30';
        case 2:
            return 'bg-brand-secondary/10 text-brand-secondary border-brand-secondary/30';
        case 3:
            return 'bg-brand-tertiary/10 text-brand-tertiary border-brand-tertiary/30';
        default:
            return 'bg-surface-elevated text-text-muted border-border';
    }
};
</script>

<template>
    <UserLayout>
        <Head title="Daftar Modul - LinuxLab" />

        <div class="py-8 space-y-8">
            <!-- Header Section -->
            <div class="space-y-3">
                <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-brand-primary/10 text-brand-primary-light text-xs font-mono font-medium border border-brand-primary/20">
                    <span class="w-1.5 h-1.5 rounded-full bg-brand-primary"></span>
                    <span>KURIKULUM LINUXLAB</span>
                </div>
                <h1 class="font-display text-3xl font-bold text-ink tracking-tight">
                    Modul Pembelajaran Linux
                </h1>
                <p class="text-text-muted text-sm sm:text-base max-w-3xl leading-relaxed">
                    Kuasai keterampilan sistem operasi Linux dari dasar hingga mahir melalui kurikulum berurutan: eksplorasi hierarki direktori, perizinan berkas yang aman, hingga manipulasi aliran teks baris perintah.
                </p>
            </div>

            <!-- Modules Grid -->
            <div v-if="modules.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <AppCard
                    v-for="module in modules"
                    :key="module.id"
                    class="flex flex-col justify-between hover:border-border-focus transition-all duration-200 group relative overflow-hidden"
                >

                    <div class="space-y-4">
                        <ModuleIcon :order="module.order" />
                        <!-- Module Badge & Lesson Count -->
                        <div class="flex items-center justify-between">
                            <span
                                class="text-xs font-display font-bold tracking-wide px-2.5 py-1 rounded border"
                                :class="getModuleBadgeColor(module.order)"
                            >
                                MODUL 0{{ module.order }}
                            </span>
                            <span class="text-xs font-mono text-text-dim flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                <span>{{ module.lessons_count ?? 0 }} Pelajaran</span>
                            </span>
                        </div>

                        <!-- Title & Description -->
                        <div class="space-y-2">
                            <h2 class="font-display text-xl font-bold text-ink group-hover:text-brand-primary-light transition-colors">
                                {{ module.title }}
                            </h2>
                            <p class="text-text-muted text-xs sm:text-sm line-clamp-3 leading-relaxed">
                                {{ module.description }}
                            </p>
                        </div>
                    </div>

                    <!-- Progress and Action Footer -->
                    <div class="pt-6 mt-6 border-t border-border/60 space-y-4">
                        <!-- Progress Bar Component -->
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-text-dim">Penyelesaian</span>
                                <span class="tabular-nums text-text-muted font-semibold">
                                    {{ module.completion_percentage ?? 0 }}%
                                    <span v-if="(module.completion_percentage ?? 0) === 100" class="text-brand-primary-light">(Selesai)</span>
                                    <span v-else-if="(module.completion_percentage ?? 0) > 0" class="text-brand-secondary">(Sedang Berjalan)</span>
                                    <span v-else class="text-text-dim">(Belum Dimulai)</span>
                                </span>
                            </div>
                            <ProgressBar
                                :value="module.completion_percentage ?? 0"
                                :show-percentage="false"
                                size="sm"
                            />
                        </div>

                        <!-- CTA Button -->
                        <Link :href="route('modules.show', module.slug)" class="block">
                            <AppButton
                                variant="secondary"
                                class="w-full justify-center group-hover:border-brand-primary/50"
                            >
                                <span>Buka Modul</span>
                                <svg class="w-4 h-4 ml-1.5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </AppButton>
                        </Link>
                    </div>
                </AppCard>
            </div>

            <!-- Empty State if no modules -->
            <EmptyState
                v-else
                title="Belum Ada Modul Tersedia"
                description="Modul pembelajaran sedang dipersiapkan oleh administrator."
            />
        </div>
    </UserLayout>
</template>
