<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AppCard from '@/Components/AppCard.vue';

interface Stats {
    total_users: number;
    active_users: number;
    total_modules: number;
    published_modules: number;
    total_lessons: number;
    total_exercises: number;
    total_hints: number;
    total_badges: number;
    total_questions: number;
    unresolved_questions: number;
    total_answers: number;
    terminal_sessions_today: number;
    active_terminal_sessions: number;
    module_completions: number;
}

interface QuestionItem {
    id: number;
    title: string;
    is_resolved: boolean;
    answers_count: number;
    created_at: string;
    user?: { id: number; name: string; username: string };
    lesson?: { id: number; title: string };
}

interface UserItem {
    id: number;
    name: string;
    username: string;
    email: string;
    role: string;
    is_active: boolean;
    created_at: string;
}

interface AttemptItem {
    id: number;
    is_correct: boolean;
    score: number;
    created_at: string;
    user?: { id: number; name: string; username: string };
    exercise?: { id: number; title: string };
}

interface Props {
    stats: Stats;
    recentQuestions: QuestionItem[];
    recentUsers: UserItem[];
    recentAttempts: AttemptItem[];
}

defineProps<Props>();

const page = usePage();
const currentUser = page.props.auth?.user as { name?: string; email?: string } | undefined;

const formatDate = (dateStr: string) => {
    try {
        return new Intl.DateTimeFormat('id-ID', {
            dateStyle: 'medium',
            timeStyle: 'short',
        }).format(new Date(dateStr));
    } catch {
        return dateStr;
    }
};
</script>

<template>
    <AdminLayout>
        <Head title="Panel Administrator - LinuxLab" />

        <div class="space-y-8">
            <!-- Header Ringkasan & Welcome -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-surface-card p-6 rounded-card border border-border">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-brand-primary animate-pulse"></span>
                        <h1 class="text-xl sm:text-2xl font-bold text-text-primary tracking-tight">
                            Dashboard Administrator
                        </h1>
                    </div>
                    <p class="text-sm text-text-muted">
                        Selamat datang kembali, <span class="text-text-primary font-medium">{{ currentUser?.name || 'Administrator' }}</span>. Ringkasan operasional dan kurikulum LinuxLab.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <Link
                        href="/admin/exercises"
                        class="px-4 py-2 text-xs sm:text-sm font-semibold rounded-button bg-brand-primary text-brand-primary-dark hover:bg-brand-primary-hover transition-colors shadow-sm"
                    >
                        + Buat / Kelola Latihan
                    </Link>
                </div>
            </div>

            <!-- Bento Grid Statistik (FR-ADMIN-05) -->
            <div>
                <h2 class="text-xs font-mono uppercase tracking-wider text-text-muted mb-3">Ringkasan Statistik Sistem</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Stat 1: Pengguna -->
                    <div class="bg-surface-card rounded-card p-5 border border-border flex items-center gap-4">
                        <div class="w-12 h-12 rounded-lg bg-surface-elevated border border-border flex items-center justify-center text-brand-primary shrink-0">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-text-primary font-mono">{{ stats.total_users }}</div>
                            <div class="text-xs text-text-muted">Total Pengguna Terdaftar</div>
                            <div class="text-[11px] text-brand-primary mt-0.5 font-mono">{{ stats.active_users }} Akun Aktif</div>
                        </div>
                    </div>

                    <!-- Stat 2: Kurikulum & Latihan -->
                    <div class="bg-surface-card rounded-card p-5 border border-border flex items-center gap-4">
                        <div class="w-12 h-12 rounded-lg bg-surface-elevated border border-border flex items-center justify-center text-brand-secondary shrink-0">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-text-primary font-mono">{{ stats.total_exercises }}</div>
                            <div class="text-xs text-text-muted">Total Latihan Praktik</div>
                            <div class="text-[11px] text-brand-secondary mt-0.5 font-mono">{{ stats.total_modules }} Modul / {{ stats.total_lessons }} Pelajaran</div>
                        </div>
                    </div>

                    <!-- Stat 3: Sesi Praktik & Modul Selesai -->
                    <div class="bg-surface-card rounded-card p-5 border border-border flex items-center gap-4">
                        <div class="w-12 h-12 rounded-lg bg-surface-elevated border border-border flex items-center justify-center text-brand-tertiary shrink-0">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-text-primary font-mono">{{ stats.terminal_sessions_today }}</div>
                            <div class="text-xs text-text-muted">Sesi Terminal Hari Ini</div>
                            <div class="text-[11px] text-brand-tertiary mt-0.5 font-mono">{{ stats.module_completions }} Modul Telah Diselesaikan</div>
                        </div>
                    </div>

                    <!-- Stat 4: Komunitas Forum -->
                    <div class="bg-surface-card rounded-card p-5 border border-border flex items-center gap-4">
                        <div class="w-12 h-12 rounded-lg bg-surface-elevated border border-border flex items-center justify-center text-amber-400 shrink-0">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-text-primary font-mono">{{ stats.total_questions }}</div>
                            <div class="text-xs text-text-muted">Total Pertanyaan Forum</div>
                            <div class="text-[11px] text-amber-400 mt-0.5 font-mono">{{ stats.unresolved_questions }} Belum Terjawab / Terbuka</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pintasan Manajemen Cepat -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                <Link
                    href="/admin/modules"
                    class="p-4 rounded-card bg-surface-card border border-border hover:border-brand-primary/50 hover:bg-surface-elevated transition-all flex flex-col items-center text-center gap-2 group"
                >
                    <div class="w-9 h-9 rounded-lg bg-brand-primary/10 border border-brand-primary/20 flex items-center justify-center text-brand-primary group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <span class="text-xs font-semibold text-text-primary">Kelola Modul</span>
                </Link>

                <Link
                    href="/admin/lessons"
                    class="p-4 rounded-card bg-surface-card border border-border hover:border-brand-primary/50 hover:bg-surface-elevated transition-all flex flex-col items-center text-center gap-2 group"
                >
                    <div class="w-9 h-9 rounded-lg bg-brand-primary/10 border border-brand-primary/20 flex items-center justify-center text-brand-primary group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <span class="text-xs font-semibold text-text-primary">Kelola Pelajaran</span>
                </Link>

                <Link
                    href="/admin/exercises"
                    class="p-4 rounded-card bg-surface-card border border-border hover:border-brand-primary/50 hover:bg-surface-elevated transition-all flex flex-col items-center text-center gap-2 group"
                >
                    <div class="w-9 h-9 rounded-lg bg-brand-primary/10 border border-brand-primary/20 flex items-center justify-center text-brand-primary group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <span class="text-xs font-semibold text-text-primary">Latihan & Hint</span>
                </Link>

                <Link
                    href="/admin/badges"
                    class="p-4 rounded-card bg-surface-card border border-border hover:border-brand-primary/50 hover:bg-surface-elevated transition-all flex flex-col items-center text-center gap-2 group"
                >
                    <div class="w-9 h-9 rounded-lg bg-brand-primary/10 border border-brand-primary/20 flex items-center justify-center text-brand-primary group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                        </svg>
                    </div>
                    <span class="text-xs font-semibold text-text-primary">Master Lencana</span>
                </Link>

                <Link
                    href="/admin/forum"
                    class="p-4 rounded-card bg-surface-card border border-border hover:border-brand-primary/50 hover:bg-surface-elevated transition-all flex flex-col items-center text-center gap-2 group"
                >
                    <div class="w-9 h-9 rounded-lg bg-brand-primary/10 border border-brand-primary/20 flex items-center justify-center text-brand-primary group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </div>
                    <span class="text-xs font-semibold text-text-primary">Moderasi Forum</span>
                </Link>

                <Link
                    href="/admin/users"
                    class="p-4 rounded-card bg-surface-card border border-border hover:border-brand-primary/50 hover:bg-surface-elevated transition-all flex flex-col items-center text-center gap-2 group"
                >
                    <div class="w-9 h-9 rounded-lg bg-brand-primary/10 border border-brand-primary/20 flex items-center justify-center text-brand-primary group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <span class="text-xs font-semibold text-text-primary">Kelola Pengguna</span>
                </Link>
            </div>

            <!-- Grid Aktivitas Terbaru: Forum & Pengguna -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Pertanyaan Forum Terbaru -->
                <AppCard title="Pertanyaan Forum Terbaru" subtitle="Pertanyaan yang baru saja diajukan oleh siswa">
                    <template #header-action>
                        <Link href="/admin/forum" class="text-xs text-brand-primary hover:underline font-mono">
                            Lihat Semua →
                        </Link>
                    </template>

                    <div v-if="recentQuestions.length > 0" class="divide-y divide-border">
                        <div
                            v-for="question in recentQuestions"
                            :key="question.id"
                            class="py-3 flex items-start justify-between gap-3 text-sm"
                        >
                            <div class="space-y-1 min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="text-[10px] px-1.5 py-0.5 rounded font-mono"
                                        :class="question.is_resolved ? 'bg-brand-primary/10 text-brand-primary border border-brand-primary/30' : 'bg-amber-400/10 text-amber-400 border border-amber-400/30'"
                                    >
                                        {{ question.is_resolved ? 'Selesai' : 'Terbuka' }}
                                    </span>
                                    <span class="text-xs text-text-muted truncate">
                                        {{ question.lesson?.title || 'Pelajaran' }}
                                    </span>
                                </div>
                                <h4 class="font-medium text-text-primary truncate">{{ question.title }}</h4>
                                <div class="text-xs text-text-dim flex items-center gap-2">
                                    <span>Oleh: {{ question.user?.name || 'Anonim' }}</span>
                                    <span>•</span>
                                    <span>{{ formatDate(question.created_at) }}</span>
                                </div>
                            </div>

                            <div class="text-xs text-text-muted shrink-0 text-right">
                                <span class="font-mono text-brand-secondary">{{ question.answers_count }}</span> jawaban
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-8 text-xs text-text-muted">
                        Belum ada pertanyaan forum.
                    </div>
                </AppCard>

                <!-- Pendaftaran Pengguna Terbaru -->
                <AppCard title="Pengguna Terbaru" subtitle="Pendaftaran akun pengguna dan administrator terakhir">
                    <template #header-action>
                        <Link href="/admin/users" class="text-xs text-brand-primary hover:underline font-mono">
                            Kelola Semua →
                        </Link>
                    </template>

                    <div v-if="recentUsers.length > 0" class="divide-y divide-border">
                        <div
                            v-for="u in recentUsers"
                            :key="u.id"
                            class="py-3 flex items-center justify-between gap-3 text-sm"
                        >
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-8 h-8 rounded-full bg-surface-elevated border border-border flex items-center justify-center font-mono text-xs text-brand-primary shrink-0">
                                    {{ u.name.charAt(0).toUpperCase() }}
                                </div>
                                <div class="min-w-0">
                                    <div class="font-medium text-text-primary truncate">{{ u.name }}</div>
                                    <div class="text-xs text-text-muted truncate">@{{ u.username }} • {{ u.email }}</div>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 shrink-0">
                                <span
                                    class="text-[10px] px-2 py-0.5 rounded-badge uppercase font-mono font-semibold"
                                    :class="u.role === 'admin' ? 'bg-amber-400/10 text-amber-400 border border-amber-400/30' : 'bg-surface-elevated text-text-muted border border-border'"
                                >
                                    {{ u.role }}
                                </span>
                                <span
                                    class="text-[10px] px-2 py-0.5 rounded-badge uppercase font-mono font-semibold"
                                    :class="u.is_active ? 'bg-brand-primary/10 text-brand-primary border border-brand-primary/30' : 'bg-brand-danger/10 text-brand-danger border border-brand-danger/30'"
                                >
                                    {{ u.is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-8 text-xs text-text-muted">
                        Belum ada pengguna.
                    </div>
                </AppCard>
            </div>
        </div>
    </AdminLayout>
</template>
