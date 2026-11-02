<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';
import AppCard from '@/Components/AppCard.vue';
import ProgressBar from '@/Components/ProgressBar.vue';
import AppButton from '@/Components/AppButton.vue';

interface LessonProgressItem {
    id: number;
    title: string;
    slug: string;
    status: 'not_started' | 'in_progress' | 'completed';
    completed_at?: string | null;
}

interface ModuleProgressItem {
    id: number;
    title: string;
    slug: string;
    description: string;
    lessons_count: number;
    completion_percentage: number;
    status: 'not_started' | 'in_progress' | 'completed';
    lessons: LessonProgressItem[];
}

interface AttemptItem {
    id: number;
    is_correct: boolean;
    score: number;
    feedback: string;
    created_at: string;
    exercise: {
        id?: number;
        title?: string;
        points?: number;
    };
    lesson: {
        id?: number;
        title?: string;
        slug?: string;
    };
    module: {
        id?: number;
        title?: string;
        slug?: string;
    };
}

interface PaginatedAttempts {
    data: AttemptItem[];
    current_page: number;
    last_page: number;
    prev_page_url: string | null;
    next_page_url: string | null;
    total: number;
}

interface OverallProgress {
    percentage: number;
    completed_lessons_count: number;
    total_lessons_count: number;
    completed_modules_count: number;
    total_modules_count: number;
}

interface Props {
    overallProgress: OverallProgress;
    modules: ModuleProgressItem[];
    attempts: PaginatedAttempts;
}

defineProps<Props>();
</script>

<template>
    <UserLayout>
        <Head title="Progres & Riwayat - LinuxLab" />

        <div class="py-8 space-y-8">
            <!-- Header Banner -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-2 border-b border-border">
                <div>
                    <h1 class="font-display text-2xl sm:text-3xl font-bold text-ink tracking-tight">
                        Progres & Riwayat Belajar
                    </h1>
                    <p class="text-sm text-text-muted mt-1">
                        Lacak kemajuan per modul dan tinjau riwayat evaluasi latihan praktikum Anda.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('achievements.index')">
                        <AppButton variant="secondary" size="md">
                            Lihat Pencapaian
                        </AppButton>
                    </Link>
                </div>
            </div>

            <!-- Overall Progress Summary -->
            <AppCard class="p-6 bg-gradient-to-r from-surface-card via-surface-elevated to-surface-card border-border">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-6">
                    <div class="space-y-1 text-center sm:text-left">
                        <span class="text-xs font-bold uppercase tracking-wider text-brand-primary-light">Total Pencapaian Kurikulum</span>
                        <h2 class="text-xl font-bold text-text-primary">
                            {{ overallProgress.completed_lessons_count }} dari {{ overallProgress.total_lessons_count }} Pelajaran Telah Tuntas
                        </h2>
                        <p class="text-xs text-text-muted">
                            {{ overallProgress.completed_modules_count }} dari {{ overallProgress.total_modules_count }} Modul telah selesai 100%.
                        </p>
                    </div>

                    <div class="w-full sm:w-64 space-y-1.5">
                        <div class="flex justify-between text-xs font-mono">
                            <span class="text-text-dim">Progres Keseluruhan</span>
                            <span class="font-bold tabular-nums text-brand-primary-light">{{ overallProgress.percentage }}%</span>
                        </div>
                        <ProgressBar :value="overallProgress.percentage" :show-percentage="false" size="md" />
                    </div>
                </div>
            </AppCard>

            <!-- Progress per Modul (FR-PROG-02) -->
            <div class="space-y-4">
                <h2 class="font-display text-xl font-bold text-ink">
                    Progres Modul Pembelajaran
                </h2>

                <div class="space-y-4">
                    <AppCard
                        v-for="module in modules"
                        :key="module.id"
                        class="p-6 space-y-4 hover:border-border-focus transition-colors"
                    >
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold px-2 py-0.5 rounded bg-brand-primary/10 text-brand-primary-light border border-brand-primary/20">
                                        MODUL 0{{ module.id }}
                                    </span>
                                    <span
                                        v-if="module.status === 'completed'"
                                        class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-brand-primary/15 text-brand-primary-light border border-brand-primary/30"
                                    >
                                        Selesai 100%
                                    </span>
                                    <span
                                        v-else-if="module.status === 'in_progress'"
                                        class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-brand-secondary/15 text-brand-secondary-light border border-brand-secondary/30"
                                    >
                                        Sedang Berjalan
                                    </span>
                                    <span
                                        v-else
                                        class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-surface-highest text-text-dim"
                                    >
                                        Belum Mulai
                                    </span>
                                </div>
                                <h3 class="font-display text-lg font-bold text-ink">
                                    {{ module.title }}
                                </h3>
                                <p class="text-xs text-text-muted max-w-2xl leading-relaxed">
                                    {{ module.description }}
                                </p>
                            </div>

                            <div class="w-full sm:w-48 space-y-1 flex-shrink-0">
                                <div class="flex justify-between text-xs">
                                    <span class="text-text-dim">Penyelesaian</span>
                                    <span class="font-bold tabular-nums text-text-primary">{{ module.completion_percentage }}%</span>
                                </div>
                                <ProgressBar :value="module.completion_percentage" :show-percentage="false" size="sm" />
                            </div>
                        </div>

                        <!-- Lessons breakdown within module -->
                        <div class="pt-4 border-t border-border/60">
                            <h4 class="text-xs font-semibold text-text-dim uppercase tracking-wider mb-2">
                                Status Pelajaran
                            </h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                                <Link
                                    v-for="lesson in module.lessons"
                                    :key="lesson.id"
                                    :href="`/modules/${module.slug}/lessons/${lesson.slug}`"
                                    class="flex items-center justify-between p-2.5 rounded bg-surface-high/60 hover:bg-surface-high border border-border/40 text-xs transition-colors"
                                >
                                    <div class="flex items-center gap-2 truncate">
                                        <span
                                            v-if="lesson.status === 'completed'"
                                            class="w-2 h-2 rounded-full bg-brand-primary flex-shrink-0"
                                        ></span>
                                        <span
                                            v-else-if="lesson.status === 'in_progress'"
                                            class="w-2 h-2 rounded-full bg-brand-secondary flex-shrink-0"
                                        ></span>
                                        <span
                                            v-else
                                            class="w-2 h-2 rounded-full bg-text-dim/40 flex-shrink-0"
                                        ></span>
                                        <span class="truncate text-text-primary font-medium">{{ lesson.title }}</span>
                                    </div>

                                    <span
                                        class="text-[10px] font-mono flex-shrink-0 ml-2"
                                        :class="{
                                            'text-brand-primary-light': lesson.status === 'completed',
                                            'text-brand-secondary-light': lesson.status === 'in_progress',
                                            'text-text-dim': lesson.status === 'not_started',
                                        }"
                                    >
                                        {{ lesson.status === 'completed' ? 'Selesai' : (lesson.status === 'in_progress' ? 'Berjalan' : 'Belum') }}
                                    </span>
                                </Link>
                            </div>
                        </div>
                    </AppCard>
                </div>
            </div>

            <!-- Riwayat Percobaan Latihan (FR-EX-04) -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="font-display text-xl font-bold text-ink">
                            Riwayat Percobaan Latihan
                        </h2>
                        <p class="text-xs sm:text-sm text-text-muted">
                            Daftar hasil penilaian otomatis dari latihan yang pernah Anda kerjakan.
                        </p>
                    </div>
                    <span class="text-xs font-mono text-text-dim">
                        Total Percobaan: {{ attempts.total }}
                    </span>
                </div>

                <AppCard v-if="attempts.data && attempts.data.length > 0" class="overflow-hidden p-0 border-border">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-surface-high text-text-dim border-b border-border uppercase tracking-wider text-[11px]">
                                <tr>
                                    <th class="px-4 py-3">Waktu</th>
                                    <th class="px-4 py-3">Modul & Pelajaran</th>
                                    <th class="px-4 py-3">Latihan</th>
                                    <th class="px-4 py-3 text-center">Status</th>
                                    <th class="px-4 py-3 text-right">Skor</th>
                                    <th class="px-4 py-3">Umpan Balik</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border/60">
                                <tr
                                    v-for="attempt in attempts.data"
                                    :key="attempt.id"
                                    class="hover:bg-surface-elevated/40 transition-colors"
                                >
                                    <td class="px-4 py-3 font-mono text-text-muted whitespace-nowrap">
                                        {{ attempt.created_at }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="text-text-primary font-medium">{{ attempt.module?.title }}</div>
                                        <div class="text-[11px] text-text-dim">{{ attempt.lesson?.title }}</div>
                                    </td>
                                    <td class="px-4 py-3 font-medium text-text-primary">
                                        {{ attempt.exercise?.title }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span
                                            class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider"
                                            :class="attempt.is_correct ? 'bg-brand-primary/15 text-brand-primary-light border border-brand-primary/30' : 'bg-brand-danger/10 text-brand-danger-light border border-brand-danger/30'"
                                        >
                                            {{ attempt.is_correct ? 'Benar' : 'Belum Benar' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold tabular-nums" :class="attempt.is_correct ? 'text-brand-primary-light' : 'text-text-dim'">
                                        {{ attempt.score }} / {{ attempt.exercise?.points ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-text-muted max-w-xs truncate" :title="attempt.feedback">
                                        {{ attempt.feedback }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="attempts.last_page > 1" class="px-4 py-3 border-t border-border flex items-center justify-between text-xs">
                        <span class="text-text-dim">
                            Halaman {{ attempts.current_page }} dari {{ attempts.last_page }}
                        </span>
                        <div class="flex items-center gap-2">
                            <Link
                                v-if="attempts.prev_page_url"
                                :href="attempts.prev_page_url"
                                class="px-3 py-1 rounded bg-surface-high text-text-primary hover:bg-surface-card border border-border"
                            >
                                Sebelumnya
                            </Link>
                            <Link
                                v-if="attempts.next_page_url"
                                :href="attempts.next_page_url"
                                class="px-3 py-1 rounded bg-surface-high text-text-primary hover:bg-surface-card border border-border"
                            >
                                Selanjutnya
                            </Link>
                        </div>
                    </div>
                </AppCard>

                <!-- Empty State (UI-02) -->
                <div v-else class="rounded-card border border-dashed border-border p-12 text-center space-y-3">
                    <div class="w-12 h-12 rounded-full bg-surface-high text-text-dim mx-auto flex items-center justify-center font-display font-bold text-xl">
                        &gt;_
                    </div>
                    <h3 class="font-display text-base font-bold text-ink">Belum Ada Riwayat Latihan</h3>
                    <p class="text-xs text-text-muted max-w-sm mx-auto">
                        Anda belum pernah mengirim jawaban latihan. Mulai praktik interaktif di modul pembelajaran untuk melihat riwayat penilaian Anda di sini.
                    </p>
                    <Link :href="route('modules.index')">
                        <AppButton variant="primary" size="sm" class="mt-2">
                            Mulai Latihan
                        </AppButton>
                    </Link>
                </div>
            </div>
        </div>
    </UserLayout>
</template>
