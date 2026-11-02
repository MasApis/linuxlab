<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';
import AppCard from '@/Components/AppCard.vue';
import AppButton from '@/Components/AppButton.vue';
import ProgressBar from '@/Components/ProgressBar.vue';
import EmptyState from '@/Components/EmptyState.vue';
import type { Module } from '@/types';

interface Props {
    module: Module;
}

const props = defineProps<Props>();

const firstLesson = props.module.lessons && props.module.lessons.length > 0
    ? props.module.lessons[0]
    : null;
</script>

<template>
    <UserLayout>
        <Head :title="`${module.title} - LinuxLab`" />

        <div class="py-8 space-y-8">
            <!-- Breadcrumb Navigation -->
            <nav class="flex items-center gap-2 text-xs sm:text-sm text-text-muted font-medium">
                <Link :href="route('dashboard')" class="hover:text-brand-primary-light transition-colors flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Beranda</span>
                </Link>
                <span class="text-text-dim">/</span>
                <Link :href="route('modules.index')" class="hover:text-brand-primary-light transition-colors">
                    Modul
                </Link>
                <span class="text-text-dim">/</span>
                <span class="text-ink font-semibold truncate">{{ module.title }}</span>
            </nav>

            <!-- Module Hero & Summary Card -->
            <AppCard class="relative overflow-hidden bg-surface-card border-border p-6 sm:p-8">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
                    <!-- Left: Metadata, Title, Description, and First Action -->
                    <div class="lg:col-span-8 space-y-4">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-xs font-mono font-bold px-2.5 py-0.5 rounded bg-brand-primary/10 text-brand-primary-light border border-brand-primary/30">
                                MODUL 0{{ module.order }}
                            </span>
                            <span class="text-xs font-mono px-2.5 py-0.5 rounded bg-surface-elevated text-text-muted border border-border">
                                Tingkat: Pemula
                            </span>
                            <span class="text-xs font-mono px-2.5 py-0.5 rounded bg-surface-elevated text-text-muted border border-border">
                                Linux / POSIX Standard
                            </span>
                        </div>

                        <h1 class="font-display text-2xl sm:text-3xl font-bold text-ink tracking-tight">
                            {{ module.title }}
                        </h1>

                        <p class="text-text-muted text-sm sm:text-base leading-relaxed max-w-2xl">
                            {{ module.description }}
                        </p>

                        <div v-if="firstLesson" class="pt-2">
                            <Link :href="route('lessons.show', [module.slug, firstLesson.slug])">
                                <AppButton variant="primary" size="md">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Mulai Pelajaran Pertama</span>
                                </AppButton>
                            </Link>
                        </div>
                    </div>

                    <!-- Right: Progress Overview Card -->
                    <div class="lg:col-span-4 bg-surface-elevated/70 border border-border rounded-xl p-5 space-y-4">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-mono text-text-dim">STATUS MODUL</span>
                            <span
                                class="font-mono font-bold px-2 py-0.5 rounded border"
                                :class="(module.completion_percentage ?? 0) === 100 ? 'bg-brand-primary/15 text-brand-primary-light border border-brand-primary/30' : ((module.completion_percentage ?? 0) > 0 ? 'bg-brand-secondary/15 text-brand-secondary-light border border-brand-secondary/30' : 'bg-surface-card text-text-dim border border-border')"
                            >
                                {{ module.completion_percentage ?? 0 }}% SELESAI
                            </span>
                        </div>

                        <ProgressBar
                            :value="module.completion_percentage ?? 0"
                            :show-percentage="false"
                            size="md"
                        />

                        <div class="flex justify-between text-xs text-text-muted pt-1">
                            <span>Total Pelajaran:</span>
                            <span class="tabular-nums font-semibold text-text-primary">
                                {{ module.lessons?.length ?? 0 }} Pelajaran
                            </span>
                        </div>

                        <div class="text-[11px] text-text-dim bg-surface-card/60 p-2.5 rounded border border-border/50">
                            Selesaikan setiap pelajaran dan latihan untuk membuka lencana dan menyelesaikan modul 100%.
                        </div>
                    </div>
                </div>
            </AppCard>

            <!-- Lessons List Section (FR-MOD-01: lessons.order) -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="font-display text-xl font-bold text-ink">
                            Daftar Pelajaran
                        </h2>
                        <p class="text-xs sm:text-sm text-text-muted">
                            Ikuti materi secara berurutan sesuai kurikulum terstruktur.
                        </p>
                    </div>
                    <span class="text-xs font-mono text-text-dim">
                        {{ module.lessons?.length ?? 0 }} Pelajaran Tersedia
                    </span>
                </div>

                <!-- Lessons Accordion/List -->
                <div v-if="module.lessons && module.lessons.length > 0" class="space-y-3">
                    <div
                        v-for="lesson in module.lessons"
                        :key="lesson.id"
                        class="p-4 sm:p-5 rounded-card bg-surface-card border transition-all duration-150 flex flex-col sm:flex-row sm:items-center justify-between gap-4 group"
                        :class="lesson.is_completed ? 'border-brand-primary/40 bg-surface-card' : 'border-border hover:border-border-focus'"
                    >
                        <div class="flex items-start sm:items-center gap-4">
                            <!-- Order Number / Completed Badge -->
                            <div
                                class="w-10 h-10 rounded-lg border font-mono font-bold text-sm flex items-center justify-center shrink-0 transition-colors"
                                :class="lesson.is_completed ? 'bg-brand-primary/15 text-brand-primary-light border border-brand-primary/40' : 'bg-brand-secondary/15 text-brand-secondary-light border border-brand-secondary/40'"
                            >
                                <span v-if="lesson.is_completed">✓</span>
                                <span v-else>0{{ lesson.order }}</span>
                            </div>

                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-[11px] font-mono text-text-dim uppercase">
                                        Pelajaran {{ module.order }}.{{ lesson.order }}
                                    </span>
                                    <span class="text-xs text-text-dim">•</span>
                                    <span
                                        v-if="lesson.is_completed"
                                        class="text-[11px] font-bold text-brand-primary-light"
                                    >
                                        Selesai 100%
                                    </span>
                                    <span
                                        v-else-if="lesson.status === 'in_progress'"
                                        class="text-[11px] font-bold text-brand-secondary-light"
                                    >
                                        Sedang Berjalan
                                    </span>
                                    <span
                                        v-else
                                        class="text-[11px] font-mono text-text-dim"
                                    >
                                        Materi & Cheat Sheet
                                    </span>
                                </div>
                                <h3 class="font-display text-base sm:text-lg font-bold text-ink group-hover:text-brand-primary-light transition-colors">
                                    {{ lesson.title }}
                                </h3>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="sm:self-center shrink-0 flex flex-col sm:flex-row gap-2">
                            <Link :href="route('lessons.practice', lesson.slug)">
                                <AppButton variant="primary" size="sm" class="w-full sm:w-auto">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span>Mulai Praktik</span>
                                </AppButton>
                            </Link>
                            <Link :href="route('lessons.show', [module.slug, lesson.slug])">
                                <AppButton variant="secondary" size="sm" class="w-full sm:w-auto">
                                    <span>{{ lesson.is_completed ? 'Tinjau Materi' : 'Baca Materi' }}</span>
                                    <svg class="w-4 h-4 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </AppButton>
                            </Link>
                        </div>
                    </div>
                </div>

                <EmptyState
                    v-else
                    title="Belum Ada Pelajaran"
                    description="Pelajaran untuk modul ini belum ditambahkan."
                />
            </div>
        </div>
    </UserLayout>
</template>
