<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';
import AppCard from '@/Components/AppCard.vue';
import AppButton from '@/Components/AppButton.vue';
import ProgressBar from '@/Components/ProgressBar.vue';
import BadgeCard from '@/Components/BadgeCard.vue';

interface ModuleItem {
    id: number;
    title: string;
    slug: string;
    description: string;
    order: number;
    lessons_count: number;
    completion_percentage: number;
    status: 'not_started' | 'in_progress' | 'completed';
}

interface OverallProgress {
    percentage: number;
    completed_lessons_count: number;
    total_lessons_count: number;
    completed_modules_count: number;
    total_modules_count: number;
}

interface NextLesson {
    id: number;
    title: string;
    slug: string;
    module_slug: string;
    module_title: string;
}

interface BadgeItem {
    id: number;
    name: string;
    description: string;
    icon: string;
    awarded_at?: string;
}

interface Props {
    overallProgress: OverallProgress;
    modules: ModuleItem[];
    nextLesson?: NextLesson | null;
    recentBadges?: BadgeItem[];
    currentRank?: number | null;
}

const props = defineProps<Props>();

const page = usePage();
const user = page.props.auth?.user as { name?: string; username?: string; email?: string } | undefined;
</script>

<template>
    <UserLayout>
        <Head title="Dashboard - LinuxLab" />

        <div class="py-8 space-y-8">
            <!-- Hero / Welcome Banner -->
            <AppCard class="relative overflow-hidden bg-gradient-to-r from-surface-card via-surface-elevated to-surface-card border-border p-6 sm:p-8">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    <div class="space-y-3">
                        <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-brand-primary/10 text-brand-primary text-xs font-mono font-medium border border-brand-primary/20">
                            <span class="w-2 h-2 rounded-full bg-brand-primary animate-pulse"></span>
                            <span>SESI AKTIF: @{{ user?.username || 'user' }}</span>
                        </div>
                        <h1 class="text-2xl sm:text-3xl font-extrabold text-text-primary tracking-tight">
                            Selamat Datang, {{ user?.name || 'Pengguna' }}!
                        </h1>
                        <p class="text-sm text-text-muted max-w-2xl leading-relaxed">
                            Pantau kemajuan belajar Linux Anda, lanjutkan praktik terminal interaktif, dan kumpulkan lencana keahlian.
                        </p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-wrap items-center gap-3">
                        <Link
                            v-if="nextLesson"
                            :href="`/modules/${nextLesson.module_slug}/lessons/${nextLesson.slug}`"
                        >
                            <AppButton variant="primary" size="md">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Lanjutkan Belajar</span>
                            </AppButton>
                        </Link>
                        <Link v-else :href="route('modules.index')">
                            <AppButton variant="primary" size="md">
                                Mulai Belajar
                            </AppButton>
                        </Link>

                        <Link :href="route('progress.index')">
                            <AppButton variant="secondary" size="md">
                                Riwayat Progres
                            </AppButton>
                        </Link>
                    </div>
                </div>

                <!-- Next Lesson Hint Sub-bar -->
                <div v-if="nextLesson" class="mt-6 pt-4 border-t border-border/60 flex items-center gap-2 text-xs text-text-muted">
                    <span class="font-semibold text-text-dim">Pelajaran Selanjutnya:</span>
                    <span class="text-brand-primary font-medium">{{ nextLesson.module_title }} &rsaquo; {{ nextLesson.title }}</span>
                </div>
            </AppCard>

            <!-- Stats Overview Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Stat 1: Total Progress -->
                <AppCard class="p-5 flex flex-col justify-between space-y-2">
                    <div class="flex items-center justify-between text-text-muted text-xs">
                        <span>Progres Keseluruhan</span>
                        <svg class="w-4 h-4 text-brand-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl font-bold font-mono text-text-primary">{{ overallProgress.percentage }}%</span>
                        <span class="text-xs text-text-dim">selesai</span>
                    </div>
                    <ProgressBar :value="overallProgress.percentage" :show-percentage="false" size="sm" />
                </AppCard>

                <!-- Stat 2: Completed Lessons -->
                <AppCard class="p-5 flex flex-col justify-between space-y-2">
                    <div class="flex items-center justify-between text-text-muted text-xs">
                        <span>Pelajaran Selesai</span>
                        <svg class="w-4 h-4 text-brand-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <div class="flex items-baseline gap-1 font-mono">
                        <span class="text-2xl font-bold text-text-primary">{{ overallProgress.completed_lessons_count }}</span>
                        <span class="text-sm text-text-dim">/ {{ overallProgress.total_lessons_count }}</span>
                    </div>
                    <p class="text-[11px] text-text-dim">Pelajaran dengan latihan tervalidasi</p>
                </AppCard>

                <!-- Stat 3: Completed Modules -->
                <AppCard class="p-5 flex flex-col justify-between space-y-2">
                    <div class="flex items-center justify-between text-text-muted text-xs">
                        <span>Modul Selesai</span>
                        <svg class="w-4 h-4 text-brand-tertiary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                        </svg>
                    </div>
                    <div class="flex items-baseline gap-1 font-mono">
                        <span class="text-2xl font-bold text-text-primary">{{ overallProgress.completed_modules_count }}</span>
                        <span class="text-sm text-text-dim">/ {{ overallProgress.total_modules_count }}</span>
                    </div>
                    <p class="text-[11px] text-text-dim">Modul tuntas 100%</p>
                </AppCard>

                <!-- Stat 4: Leaderboard Position (FR-LEAD-03) -->
                <AppCard class="p-5 flex flex-col justify-between space-y-2">
                    <div class="flex items-center justify-between text-text-muted text-xs">
                        <span>Peringkat Leaderboard</span>
                        <svg class="w-4 h-4 text-brand-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span v-if="currentRank" class="text-2xl font-bold font-mono text-brand-secondary">
                            #{{ currentRank }}
                        </span>
                        <span v-else class="text-sm font-semibold text-text-dim">
                            Belum Masuk
                        </span>
                    </div>
                    <Link :href="route('leaderboard')" class="text-[11px] text-brand-primary hover:underline flex items-center gap-1">
                        <span>Lihat papan peringkat</span>
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </Link>
                </AppCard>
            </div>

            <!-- Modul Cards Section (FR-PROG-03) -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-text-primary">
                            Modul Pembelajaran
                        </h2>
                        <p class="text-xs sm:text-sm text-text-muted">
                            Kurikulum Linux terstruktur dari dasar sistem berkas hingga pemrosesan teks.
                        </p>
                    </div>
                    <Link :href="route('modules.index')" class="text-xs sm:text-sm font-semibold text-brand-primary hover:underline">
                        Lihat Semua Modul &rarr;
                    </Link>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <AppCard
                        v-for="module in modules"
                        :key="module.id"
                        class="flex flex-col justify-between hover:border-brand-primary/50 transition-all duration-150 group"
                    >
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-mono font-bold px-2 py-0.5 rounded bg-surface-elevated text-brand-primary border border-brand-primary/20">
                                    MODUL 0{{ module.order }}
                                </span>
                                <span
                                    v-if="module.status === 'completed'"
                                    class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-brand-primary/15 text-brand-primary border border-brand-primary/30"
                                >
                                    Selesai
                                </span>
                                <span
                                    v-else-if="module.status === 'in_progress'"
                                    class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-brand-secondary/15 text-brand-secondary border border-brand-secondary/30"
                                >
                                    Berjalan
                                </span>
                                <span
                                    v-else
                                    class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-surface-highest text-text-dim"
                                >
                                    Belum Mulai
                                </span>
                            </div>

                            <h3 class="text-lg font-bold text-text-primary group-hover:text-brand-primary transition-colors">
                                {{ module.title }}
                            </h3>

                            <p class="text-xs text-text-muted line-clamp-3 leading-relaxed">
                                {{ module.description }}
                            </p>
                        </div>

                        <div class="pt-4 mt-4 border-t border-border/60 space-y-3">
                            <div class="space-y-1">
                                <div class="flex justify-between text-xs">
                                    <span class="text-text-dim">{{ module.lessons_count }} Pelajaran</span>
                                    <span class="font-mono text-text-muted">{{ module.completion_percentage }}%</span>
                                </div>
                                <ProgressBar
                                    :value="module.completion_percentage"
                                    :show-percentage="false"
                                    size="sm"
                                />
                            </div>

                            <Link :href="route('modules.show', module.slug)" class="block">
                                <AppButton variant="secondary" size="sm" class="w-full justify-center">
                                    {{ module.status === 'completed' ? 'Tinjau Materi' : 'Buka Modul' }}
                                </AppButton>
                            </Link>
                        </div>
                    </AppCard>
                </div>
            </div>

            <!-- Recent Badges Section (FR-PROG-03) -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-text-primary">
                            Lencana Terbaru
                        </h2>
                        <p class="text-xs sm:text-sm text-text-muted">
                            Penghargaan dan lencana yang berhasil Anda raih melalui latihan dan praktik.
                        </p>
                    </div>
                    <Link :href="route('achievements.index')" class="text-xs sm:text-sm font-semibold text-brand-primary hover:underline">
                        Lihat Semua Pencapaian &rarr;
                    </Link>
                </div>

                <div v-if="recentBadges && recentBadges.length > 0" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    <BadgeCard
                        v-for="badge in recentBadges"
                        :key="badge.id"
                        :name="badge.name"
                        :description="badge.description"
                        :is-unlocked="true"
                        :unlocked-at="badge.awarded_at"
                    />
                </div>
                <div v-else class="rounded-card border border-dashed border-border p-8 text-center text-text-muted text-sm">
                    <p>Belum ada lencana yang diraih. Kerjakan latihan pertama Anda untuk membuka lencana <strong>Langkah Pertama</strong>!</p>
                </div>
            </div>
        </div>
    </UserLayout>
</template>
