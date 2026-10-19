<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import AppCard from '@/Components/AppCard.vue';
import AppButton from '@/Components/AppButton.vue';

interface RankingEntry {
    rank: number;
    user_id: number;
    name: string;
    username: string;
    total_score: number;
    correct_exercises_count: number;
    completed_modules_count: number;
    badges_count: number;
    is_current_user: boolean;
}

interface Props {
    rankings: RankingEntry[];
    currentUserRank?: number | null;
}

defineProps<Props>();
</script>

<template>
    <PublicLayout>
        <Head title="Papan Peringkat (Leaderboard) - LinuxLab" />

        <div class="py-10 max-w-5xl mx-auto space-y-8">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 pb-4 border-b border-border">
                <div class="space-y-2">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-brand-secondary/10 text-brand-secondary text-xs font-mono font-medium border border-brand-secondary/20">
                        <span>PAPAN PERINGKAT SEPANJANG WAKTU</span>
                    </div>
                    <h1 class="text-3xl sm:text-4xl font-extrabold text-text-primary tracking-tight">
                        Leaderboard LinuxLab
                    </h1>
                    <p class="text-sm text-text-muted max-w-xl leading-relaxed">
                        Peringkat pembelajar terbaik berdasarkan akumulasi skor tertinggi latihan praktikum Linux.
                    </p>
                </div>

                <!-- Current User Position Badge (FR-LEAD-03) -->
                <div v-if="$page.props.auth?.user" class="flex-shrink-0">
                    <div
                        v-if="currentUserRank"
                        class="px-4 py-2.5 rounded-card bg-surface-card border border-brand-secondary/40 flex items-center gap-3 shadow-md"
                    >
                        <div class="w-8 h-8 rounded-full bg-brand-secondary/15 text-brand-secondary flex items-center justify-center font-bold text-sm border border-brand-secondary/30">
                            ★
                        </div>
                        <div class="text-xs">
                            <span class="text-text-dim block">Posisi Anda Saat Ini:</span>
                            <span class="text-sm font-bold font-mono text-brand-secondary">Peringkat #{{ currentUserRank }}</span>
                        </div>
                    </div>
                    <div
                        v-else
                        class="px-4 py-2.5 rounded-card bg-surface-card border border-border flex items-center gap-2 text-xs text-text-dim"
                    >
                        <span>Belum masuk peringkat. Selesaikan latihan untuk masuk papan skor!</span>
                    </div>
                </div>
            </div>

            <!-- Leaderboard Table Card -->
            <AppCard v-if="rankings && rankings.length > 0" class="overflow-hidden p-0 border-border shadow-xl">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-surface-elevated text-text-dim font-mono border-b border-border uppercase tracking-wider text-xs">
                            <tr>
                                <th class="px-5 py-3.5 text-center w-16">Rank</th>
                                <th class="px-5 py-3.5">Pengguna</th>
                                <th class="px-5 py-3.5 text-right">Total Skor</th>
                                <th class="px-5 py-3.5 text-center">Latihan Selesai</th>
                                <th class="px-5 py-3.5 text-center">Modul Selesai</th>
                                <th class="px-5 py-3.5 text-center">Lencana</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/60">
                            <tr
                                v-for="entry in rankings"
                                :key="entry.user_id"
                                class="transition-colors"
                                :class="[
                                    entry.is_current_user
                                        ? 'bg-brand-primary/10 hover:bg-brand-primary/15 font-semibold text-text-primary border-l-4 border-l-brand-primary'
                                        : 'hover:bg-surface-elevated/40 text-text-muted',
                                ]"
                            >
                                <!-- Rank Column -->
                                <td class="px-5 py-4 text-center font-mono font-bold">
                                    <span
                                        v-if="entry.rank === 1"
                                        class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/40 text-xs shadow-sm"
                                        title="Peringkat 1"
                                    >
                                        🥇
                                    </span>
                                    <span
                                        v-else-if="entry.rank === 2"
                                        class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-slate-400/20 text-slate-200 border border-slate-400/40 text-xs shadow-sm"
                                        title="Peringkat 2"
                                    >
                                        🥈
                                    </span>
                                    <span
                                        v-else-if="entry.rank === 3"
                                        class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-amber-700/20 text-amber-400 border border-amber-700/40 text-xs shadow-sm"
                                        title="Peringkat 3"
                                    >
                                        🥉
                                    </span>
                                    <span v-else class="text-text-dim text-xs">
                                        #{{ entry.rank }}
                                    </span>
                                </td>

                                <!-- User Name / Username (FR-LEAD-04: NO EMAIL) -->
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-8 h-8 rounded-full flex items-center justify-center font-mono font-bold text-xs flex-shrink-0 border"
                                            :class="entry.is_current_user ? 'bg-brand-primary/20 text-brand-primary border-brand-primary/40' : 'bg-surface-elevated text-text-muted border-border'"
                                        >
                                            {{ entry.name.charAt(0).toUpperCase() }}
                                        </div>
                                        <div>
                                            <div class="font-medium text-text-primary flex items-center gap-2">
                                                <span>{{ entry.name }}</span>
                                                <span
                                                    v-if="entry.is_current_user"
                                                    class="text-[10px] font-mono font-bold px-1.5 py-0.2 rounded bg-brand-primary/20 text-brand-primary border border-brand-primary/30"
                                                >
                                                    ANDA
                                                </span>
                                            </div>
                                            <div class="text-xs font-mono text-text-dim">
                                                @{{ entry.username }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Total Score (FR-LEAD-02: Best score per exercise) -->
                                <td class="px-5 py-4 text-right font-mono font-bold text-base" :class="entry.is_current_user ? 'text-brand-primary' : 'text-text-primary'">
                                    {{ entry.total_score }} <span class="text-xs text-text-dim font-normal">pts</span>
                                </td>

                                <!-- Exercises Count -->
                                <td class="px-5 py-4 text-center font-mono text-xs text-text-muted">
                                    {{ entry.correct_exercises_count }} latihan
                                </td>

                                <!-- Modules Count -->
                                <td class="px-5 py-4 text-center font-mono text-xs text-text-muted">
                                    {{ entry.completed_modules_count }} modul
                                </td>

                                <!-- Badges Count -->
                                <td class="px-5 py-4 text-center font-mono text-xs text-brand-secondary">
                                    {{ entry.badges_count }} lencana
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </AppCard>

            <!-- Empty State (UI-02) -->
            <div v-else class="rounded-card border border-dashed border-border p-12 text-center space-y-3">
                <div class="w-12 h-12 rounded-full bg-surface-elevated text-text-dim mx-auto flex items-center justify-center font-mono text-xl">
                    🏆
                </div>
                <h3 class="text-base font-bold text-text-primary">Papan Peringkat Masih Kosong</h3>
                <p class="text-xs text-text-muted max-w-sm mx-auto">
                    Belum ada pengguna yang menyelesaikan latihan dengan benar. Jadilah yang pertama masuk ke dalam papan peringkat!
                </p>
                <div class="pt-2">
                    <Link :href="route('modules.index')">
                        <AppButton variant="primary" size="sm">
                            Mulai Latihan
                        </AppButton>
                    </Link>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
