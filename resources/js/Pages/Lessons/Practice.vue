<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import axios from 'axios';
import UserLayout from '@/Layouts/UserLayout.vue';
import AppCard from '@/Components/AppCard.vue';
import AppButton from '@/Components/AppButton.vue';
import Terminal from '@/Components/Terminal.vue';

interface SessionData {
    id: number;
    status: 'active' | 'expired' | 'terminated';
    expires_at: string;
    remaining_seconds: number;
    ws_token?: string | null;
    ws_port?: number;
}

interface HintItem {
    id: number;
    content: string;
    order: number;
}

interface AttemptItem {
    id: number;
    is_correct: boolean;
    score: number;
    feedback: string;
    created_at: string;
}

interface CheckResultItem {
    type: string;
    passed: boolean;
    message: string;
}

interface ExerciseItem {
    id: number;
    title: string;
    instruction: string;
    validation_type: 'system_state' | 'output' | 'combined';
    points: number;
    order: number;
    total_hints: number;
    unlocked_hints: HintItem[];
    has_passed: boolean;
    latest_attempt?: AttemptItem | null;
}

interface Props {
    module: {
        id: number;
        title: string;
        slug: string;
    };
    lesson: {
        id: number;
        title: string;
        slug: string;
        order: number;
    };
    initialSession?: SessionData | null;
    exercises?: ExerciseItem[];
}

const props = withDefaults(defineProps<Props>(), {
    exercises: () => [],
});

const currentSession = ref<SessionData | null>(props.initialSession || null);
const remainingSeconds = ref<number>(props.initialSession?.remaining_seconds || 0);
const terminalRef = ref<InstanceType<typeof Terminal> | null>(null);
const isLoading = ref(false);
const errorMessage = ref<string | null>(null);
const isTerminalConnected = ref(false);
const isFullscreen = ref(false);

// State Latihan & Penilaian (Fase 4: FR-EX-01 s.d. 04)
const exercisesList = ref<ExerciseItem[]>([...props.exercises]);
const activeExerciseIndex = ref(0);
const isChecking = ref(false);
const isRequestingHint = ref(false);
const lastCheckResult = ref<{
    is_correct: boolean;
    score: number;
    feedback: string;
    checks: CheckResultItem[];
} | null>(null);

const currentExercise = computed(() => {
    return exercisesList.value[activeExerciseIndex.value] || null;
});

const remainingHintsCount = computed(() => {
    if (!currentExercise.value) return 0;
    return Math.max(0, currentExercise.value.total_hints - currentExercise.value.unlocked_hints.length);
});

let timerInterval: ReturnType<typeof setInterval> | null = null;

// Format countdown mm:ss
const formattedTime = computed(() => {
    if (remainingSeconds.value <= 0) return '00:00';
    const m = Math.floor(remainingSeconds.value / 60);
    const s = remainingSeconds.value % 60;
    return `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
});

const isUrgentTime = computed(() => {
    return remainingSeconds.value > 0 && remainingSeconds.value <= 300; // kurang dari 5 menit
});

const startTimer = () => {
    stopTimer();
    if (remainingSeconds.value <= 0) return;

    timerInterval = setInterval(() => {
        if (remainingSeconds.value > 0) {
            remainingSeconds.value--;
        } else {
            handleSessionExpired();
        }
    }, 1000);
};

const stopTimer = () => {
    if (timerInterval) {
        clearInterval(timerInterval);
        timerInterval = null;
    }
};

const handleSessionExpired = () => {
    stopTimer();
    if (currentSession.value) {
        currentSession.value.status = 'expired';
    }
    isTerminalConnected.value = false;
    errorMessage.value = 'Masa berlaku sesi terminal Anda telah habis (kedaluwarsa). Silakan mulai sesi baru.';
};

// Mulai sesi baru (FR-TERM-01)
const startSession = async () => {
    isLoading.value = true;
    errorMessage.value = null;

    try {
        const response = await axios.post('/terminal/sessions', {
            exercise_id: currentExercise.value ? currentExercise.value.id : null,
        });

        if (response.data.success) {
            currentSession.value = {
                id: response.data.session.id,
                status: response.data.session.status,
                expires_at: response.data.session.expires_at,
                remaining_seconds: response.data.session.remaining_seconds,
                ws_token: response.data.ws_token,
                ws_port: response.data.ws_port,
            };
            remainingSeconds.value = response.data.session.remaining_seconds;
            startTimer();
        }
    } catch (err: any) {
        if (err.response?.status === 429) {
            errorMessage.value = err.response.data.message || 'Server praktik sedang penuh, coba lagi sesaat lagi.';
        } else {
            errorMessage.value = err.response?.data?.message || 'Gagal menyiapkan lingkungan terminal. Silakan coba lagi.';
        }
    } finally {
        isLoading.value = false;
    }
};

// Reset sesi (FR-TERM-04, SEC-TERM-06)
const resetSession = async () => {
    if (!currentSession.value) return;

    if (!confirm('Apakah Anda yakin ingin mengatur ulang (reset) sesi ini? Seluruh perubahan file sementara akan dibuang.')) {
        return;
    }

    isLoading.value = true;
    errorMessage.value = null;

    try {
        await axios.delete(`/terminal/sessions/${currentSession.value.id}`);
        stopTimer();
        currentSession.value = null;
        remainingSeconds.value = 0;
        isTerminalConnected.value = false;

        await startSession();
    } catch (err: any) {
        errorMessage.value = 'Gagal mengakhiri sesi terminal.';
    } finally {
        isLoading.value = false;
    }
};

const endSession = async () => {
    if (!currentSession.value) return;

    if (!confirm('Akhiri sesi terminal ini? File sementara yang dibuat akan dibuang secara permanen.')) {
        return;
    }

    isLoading.value = true;
    try {
        await axios.delete(`/terminal/sessions/${currentSession.value.id}`);
        stopTimer();
        currentSession.value = null;
        remainingSeconds.value = 0;
        isTerminalConnected.value = false;
    } catch (err: any) {
        errorMessage.value = 'Gagal mengakhiri sesi.';
    } finally {
        isLoading.value = false;
    }
};

const toggleFullscreen = () => {
    isFullscreen.value = !isFullscreen.value;
};

const clearTerminal = () => {
    terminalRef.value?.clear();
};

// Ganti tab latihan yang aktif
const selectExercise = (index: number) => {
    activeExerciseIndex.value = index;
    lastCheckResult.value = null;
    errorMessage.value = null;
};

// Minta Hint Bertahap (FR-EX-03, D-06)
const requestHint = async () => {
    if (!currentExercise.value || isRequestingHint.value) return;

    isRequestingHint.value = true;
    try {
        const response = await axios.post(`/exercises/${currentExercise.value.id}/hints`);
        if (response.data.success) {
            currentExercise.value.unlocked_hints = response.data.unlocked_hints;
        }
    } catch (err: any) {
        errorMessage.value = err.response?.data?.message || 'Gagal meminta petunjuk latihan.';
    } finally {
        isRequestingHint.value = false;
    }
};

// Periksa Jawaban (FR-EX-02, VAL-01 s.d. 06)
const checkAnswer = async () => {
    if (!currentExercise.value || isChecking.value) return;

    isChecking.value = true;
    errorMessage.value = null;

    try {
        const response = await axios.post(`/exercises/${currentExercise.value.id}/check`);
        lastCheckResult.value = {
            is_correct: response.data.is_correct,
            score: response.data.score,
            feedback: response.data.feedback,
            checks: response.data.checks || [],
        };

        if (response.data.is_correct) {
            currentExercise.value.has_passed = true;
        }
    } catch (err: any) {
        // Penolakan VAL-06 & D-08 (Tidak ada sesi aktif) atau kegagalan pemeriksaan lainnya
        const data = err.response?.data;
        if (data && typeof data === 'object') {
            lastCheckResult.value = {
                is_correct: false,
                score: 0,
                feedback: data.feedback || data.message || 'Pemeriksaan gagal dilakukan.',
                checks: data.checks || [],
            };
            if (!currentSession.value || currentSession.value.status !== 'active') {
                errorMessage.value = data.feedback || data.message || 'Tidak ada sesi terminal aktif. Silakan mulai sesi praktik terlebih dahulu.';
            }
        } else {
            errorMessage.value = 'Terjadi kesalahan saat memeriksa jawaban. Pastikan koneksi dan sesi terminal aktif.';
        }
    } finally {
        isChecking.value = false;
    }
};

const goToNextExercise = () => {
    if (activeExerciseIndex.value < exercisesList.value.length - 1) {
        selectExercise(activeExerciseIndex.value + 1);
    }
};

// Helper format teks instruksi dengan tag code sederhana
const formatInstruction = (text: string) => {
    if (!text) return '';
    // Format `code` menjadi <code> styled tag
    return text.replace(/`([^`]+)`/g, '<code class="px-1.5 py-0.5 rounded bg-surface-elevated text-brand-primary-light font-mono text-[11px] border border-border/80">$1</code>');
};

onMounted(() => {
    if (currentSession.value && currentSession.value.status === 'active') {
        startTimer();
    } else {
        startSession();
    }
});

onBeforeUnmount(() => {
    stopTimer();
});
</script>

<template>
    <UserLayout>
        <Head :title="`Praktik: ${lesson.title} - LinuxLab`" />

        <div class="py-6 space-y-5 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Breadcrumbs Navigasi -->
            <nav class="flex items-center gap-2 text-xs sm:text-sm text-text-muted font-medium overflow-x-auto whitespace-nowrap py-1">
                <Link :href="route('dashboard')" class="hover:text-brand-primary transition-colors flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Dashboard</span>
                </Link>
                <span class="text-text-dim">/</span>
                <Link :href="route('modules.show', module.slug)" class="hover:text-brand-primary transition-colors">
                    {{ module.title }}
                </Link>
                <span class="text-text-dim">/</span>
                <Link :href="route('lessons.show', [module.slug, lesson.slug])" class="hover:text-brand-primary transition-colors">
                    {{ lesson.title }}
                </Link>
                <span class="text-text-dim">/</span>
                <span class="text-white font-semibold flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-brand-primary inline-block animate-pulse"></span>
                    <span>Praktik Terminal & Latihan</span>
                </span>
            </nav>

            <!-- Error Banner -->
            <div
                v-if="errorMessage"
                class="p-4 rounded-xl bg-red-950/60 border border-red-500/40 text-red-200 text-sm flex items-start justify-between gap-3 shadow-lg"
            >
                <div class="flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>{{ errorMessage }}</span>
                </div>
                <button
                    v-if="!currentSession || currentSession.status !== 'active'"
                    @click="startSession"
                    class="px-3 py-1 bg-red-800 hover:bg-red-700 text-white rounded text-xs font-semibold shrink-0 transition-colors"
                >
                    Mulai Sesi
                </button>
            </div>

            <!-- Workspace 2 Kolom (Split Screen: Kiri 35%, Kanan 65%) Sesuai Wireframe -->
            <div class="flex flex-col lg:flex-row gap-6 items-stretch" :class="{ 'fixed inset-0 z-50 bg-surface-base p-4': isFullscreen }">
                <!-- PANEL KIRI: Latihan, Instruksi, Hint & Tombol Periksa (35%) -->
                <aside class="w-full lg:w-[35%] flex flex-col gap-4" :class="{ 'hidden lg:flex': isFullscreen }">
                    <!-- Tab Pemilih Latihan (Jika ada lebih dari 1 latihan) -->
                    <div v-if="exercisesList.length > 1" class="flex items-center gap-1.5 p-1 bg-surface-card rounded-xl border border-border">
                        <button
                            v-for="(ex, idx) in exercisesList"
                            :key="ex.id"
                            @click="selectExercise(idx)"
                            class="flex-1 py-1.5 px-3 rounded-lg text-xs font-semibold transition-all flex items-center justify-center gap-1.5"
                            :class="[
                                activeExerciseIndex === idx
                                    ? 'bg-brand-primary text-black shadow-sm'
                                    : 'text-text-muted hover:text-white hover:bg-surface-elevated'
                            ]"
                        >
                            <span>Latihan {{ ex.order }}</span>
                            <span v-if="ex.has_passed" class="text-xs" title="Telah Selesai">✓</span>
                        </button>
                    </div>

                    <!-- Kartu Detail Latihan Aktif -->
                    <AppCard v-if="currentExercise" class="p-5 border-border space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="px-2.5 py-0.5 rounded text-xs font-mono font-semibold bg-brand-primary/10 text-brand-primary border border-brand-primary/20">
                                Tingkat: Pemula
                            </span>
                            <div class="flex items-center gap-1 text-amber-400 font-mono text-xs font-semibold">
                                <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" />
                                </svg>
                                <span>+{{ currentExercise.points }} Poin</span>
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center gap-2">
                                <h1 class="text-base sm:text-lg font-bold text-white tracking-tight">
                                    Latihan {{ currentExercise.order }}: {{ currentExercise.title }}
                                </h1>
                            </div>
                            <span v-if="currentExercise.has_passed" class="inline-flex items-center gap-1 text-[11px] text-emerald-400 font-medium mt-0.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Telah Anda selesaikan dengan benar</span>
                            </span>
                        </div>

                        <!-- Kotak Instruksi Tugas (FR-EX-01) -->
                        <div class="p-3.5 rounded-lg bg-surface-elevated border border-border/80 space-y-2">
                            <div class="flex items-center gap-1.5 text-brand-primary font-semibold text-xs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                <span>Instruksi Tugas:</span>
                            </div>
                            <div
                                class="text-xs text-text-muted leading-relaxed"
                                v-html="formatInstruction(currentExercise.instruction)"
                            ></div>
                        </div>

                        <!-- Section Hint Bertahap (FR-EX-03, D-06) -->
                        <div class="p-3.5 rounded-lg bg-surface-card border border-border space-y-2.5">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-1.5 text-amber-300 font-semibold text-xs">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                    </svg>
                                    <span>Petunjuk Bantuan (Hint)</span>
                                </div>
                                <span class="text-[11px] font-mono text-text-dim">
                                    {{ currentExercise.unlocked_hints.length }} / {{ currentExercise.total_hints }} dibuka
                                </span>
                            </div>

                            <!-- Daftar Hint yang Sudah Dibuka -->
                            <div v-if="currentExercise.unlocked_hints.length > 0" class="space-y-2 pt-1">
                                <div
                                    v-for="h in currentExercise.unlocked_hints"
                                    :key="h.id"
                                    class="p-2.5 rounded bg-surface-elevated border border-border text-xs text-text-muted space-y-1"
                                >
                                    <div class="font-bold text-amber-300 text-[11px]">
                                        Petunjuk #{{ h.order }}:
                                    </div>
                                    <div class="leading-relaxed" v-html="formatInstruction(h.content)"></div>
                                </div>
                            </div>
                            <div v-else class="text-[11px] text-text-dim italic">
                                Belum ada petunjuk yang dibuka. Anda dapat meminta petunjuk bila menemui kesulitan.
                            </div>

                            <!-- Tombol Minta Hint Bertahap -->
                            <div class="pt-1 flex items-center justify-between gap-2 border-t border-border/50">
                                <span class="text-[10px] text-text-dim">
                                    Hint tidak mengurangi skor (D-06).
                                </span>
                                <button
                                    v-if="remainingHintsCount > 0"
                                    @click="requestHint"
                                    :disabled="isRequestingHint"
                                    class="px-2.5 py-1 rounded bg-amber-500/10 hover:bg-amber-500/20 text-amber-300 border border-amber-500/30 text-xs font-semibold transition-colors disabled:opacity-50 flex items-center gap-1"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    <span>{{ isRequestingHint ? 'Membuka...' : `Minta Hint (${remainingHintsCount} tersisa)` }}</span>
                                </button>
                                <span v-else class="text-[11px] text-text-dim font-medium">
                                    Semua petunjuk telah dibuka.
                                </span>
                            </div>
                        </div>

                        <!-- Feedback Penilaian Real-Time (VAL-03, VAL-04, UI-03) -->
                        <div
                            v-if="lastCheckResult"
                            class="p-4 rounded-xl border space-y-3 transition-all"
                            :class="[
                                lastCheckResult.is_correct
                                    ? 'bg-emerald-950/40 border-emerald-500/40 text-emerald-200'
                                    : 'bg-red-950/40 border-red-500/40 text-red-200'
                            ]"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="w-6 h-6 rounded-full flex items-center justify-center font-bold text-xs"
                                        :class="[
                                            lastCheckResult.is_correct ? 'bg-emerald-500 text-black' : 'bg-red-500 text-white'
                                        ]"
                                    >
                                        {{ lastCheckResult.is_correct ? '✓' : '✗' }}
                                    </span>
                                    <span class="font-bold text-sm text-white">
                                        {{ lastCheckResult.is_correct ? 'Jawaban Benar! Selesai' : 'Jawaban Belum Sesuai' }}
                                    </span>
                                </div>
                                <span
                                    v-if="lastCheckResult.is_correct"
                                    class="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 font-mono text-xs font-bold border border-emerald-500/30"
                                >
                                    +{{ lastCheckResult.score }} Poin
                                </span>
                            </div>

                            <!-- Rincian Feedback Bahasa Indonesia -->
                            <div class="text-xs space-y-1 font-mono whitespace-pre-line leading-relaxed text-text-muted">
                                {{ lastCheckResult.feedback }}
                            </div>

                            <!-- Tombol Lanjut ke Latihan Berikutnya -->
                            <div v-if="lastCheckResult.is_correct && activeExerciseIndex < exercisesList.length - 1" class="pt-1">
                                <button
                                    @click="goToNextExercise"
                                    class="w-full py-2 px-3 bg-emerald-500 hover:bg-emerald-400 text-black font-semibold rounded-lg text-xs transition-colors flex items-center justify-center gap-1.5 shadow-sm"
                                >
                                    <span>Lanjut ke Latihan {{ exercisesList[activeExerciseIndex + 1].order }}</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Peringatan Berkas Non-Persisten (FR-TERM-05) -->
                        <div class="p-3 rounded-lg bg-amber-950/20 border border-amber-500/20 text-amber-200/80 text-[11px] space-y-1">
                            <div class="flex items-center gap-1 font-bold text-amber-400">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Lingkungan Praktik Non-Persisten</span>
                            </div>
                            <p class="leading-relaxed">
                                Seluruh berkas yang Anda buat di dalam terminal hanya berlaku selama sesi aktif. Saat sesi berakhir, container dihancurkan dan lingkungan dikembalikan bersih.
                            </p>
                        </div>
                    </AppCard>

                    <!-- Empty State Bila Tidak Ada Latihan -->
                    <AppCard v-else class="p-5 border-border text-center text-text-muted text-xs">
                        Belum ada latihan yang tersedia pada pelajaran ini.
                    </AppCard>

                    <!-- Tombol Aksi Utama: Periksa Jawaban (FR-EX-01 s.d. 02) -->
                    <div class="flex flex-col gap-2.5">
                        <AppButton
                            v-if="currentExercise"
                            variant="primary"
                            size="md"
                            class="w-full justify-center font-bold shadow-md"
                            :loading="isChecking"
                            :disabled="isChecking || !currentSession || currentSession.status !== 'active'"
                            @click="checkAnswer"
                        >
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Periksa Jawaban</span>
                        </AppButton>

                        <div v-if="currentSession && currentSession.status === 'active'" class="grid grid-cols-2 gap-2">
                            <AppButton
                                variant="secondary"
                                size="sm"
                                class="w-full justify-center text-xs"
                                :disabled="isLoading"
                                @click="resetSession"
                                title="Reset sesi terminal dan bersihkan file latihan"
                            >
                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                <span>Reset Sesi</span>
                            </AppButton>

                            <AppButton
                                variant="secondary"
                                size="sm"
                                class="w-full justify-center text-xs text-red-400 hover:text-red-300 hover:border-red-500/50"
                                :disabled="isLoading"
                                @click="endSession"
                            >
                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                <span>Akhiri Sesi</span>
                            </AppButton>
                        </div>

                        <AppButton
                            v-else
                            variant="primary"
                            size="md"
                            class="w-full justify-center"
                            :loading="isLoading"
                            @click="startSession"
                        >
                            <span>Mulai Sesi Praktik</span>
                        </AppButton>

                        <Link
                            :href="route('lessons.show', [module.slug, lesson.slug])"
                            class="inline-flex items-center justify-center gap-1.5 py-2 px-3 rounded-button border border-border text-xs text-text-muted hover:text-white hover:bg-surface-elevated transition-colors text-center"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            <span>Kembali ke Materi Pelajaran</span>
                        </Link>
                    </div>
                </aside>

                <!-- PANEL KANAN: Terminal Linux Interaktif (65%) Sesuai Wireframe -->
                <section
                    class="w-full flex flex-col rounded-xl overflow-hidden border border-border bg-[#060e20] shadow-2xl transition-all"
                    :class="[
                        isFullscreen ? 'fixed inset-4 z-50 h-[calc(100vh-2rem)]' : 'lg:w-[65%] min-h-[580px] h-full flex-1'
                    ]"
                >
                    <!-- Header Bar Window Terminal -->
                    <div class="bg-surface-elevated px-4 py-2.5 flex items-center justify-between border-b border-border select-none shrink-0">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-1.5">
                                <button @click="endSession" title="Tutup / Akhiri Sesi" class="w-3 h-3 rounded-full bg-red-500/90 hover:opacity-80 transition-opacity"></button>
                                <button @click="resetSession" title="Reset Sesi" class="w-3 h-3 rounded-full bg-yellow-500/90 hover:opacity-80 transition-opacity"></button>
                                <button @click="toggleFullscreen" title="Layar Penuh" class="w-3 h-3 rounded-full bg-green-500/90 hover:opacity-80 transition-opacity"></button>
                            </div>
                            <div class="h-4 w-px bg-border"></div>
                            <span class="font-mono text-xs text-text-muted flex items-center gap-1.5">
                                <span class="text-brand-primary">&gt;_</span>
                                <span>bash — student@linuxlab: ~ (Ubuntu 22.04 LTS)</span>
                            </span>
                        </div>

                        <div class="flex items-center gap-2 text-text-dim">
                            <button
                                @click="clearTerminal"
                                class="p-1 hover:text-brand-primary hover:bg-surface-card rounded transition-colors text-xs flex items-center gap-1"
                                title="Bersihkan Layar Terminal"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                <span class="hidden sm:inline">Bersihkan</span>
                            </button>

                            <button
                                @click="toggleFullscreen"
                                class="p-1 hover:text-brand-primary hover:bg-surface-card rounded transition-colors"
                                :title="isFullscreen ? 'Keluar Layar Penuh' : 'Layar Penuh'"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path v-if="!isFullscreen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                                    <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Status & Countdown Timer Bar (Sub-header) -->
                    <div class="bg-surface-card/60 px-4 py-2 border-b border-border/80 flex items-center justify-between text-xs font-mono shrink-0">
                        <div class="flex items-center gap-2">
                            <span
                                class="w-2.5 h-2.5 rounded-full"
                                :class="[
                                    isTerminalConnected
                                        ? 'bg-emerald-400 animate-pulse'
                                        : currentSession?.status === 'active'
                                        ? 'bg-amber-400'
                                        : 'bg-red-400'
                                ]"
                            ></span>
                            <span class="text-text-muted">
                                Status:
                                <strong v-if="isTerminalConnected" class="text-emerald-400 font-semibold">Tersambung (Aktif)</strong>
                                <strong v-else-if="currentSession?.status === 'active'" class="text-amber-300">Menghubungkan...</strong>
                                <strong v-else class="text-red-400">Tidak Aktif</strong>
                            </span>
                        </div>

                        <!-- Real-time Countdown Timer (SEC-TERM-05) -->
                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-1.5" :class="[isUrgentTime ? 'text-amber-400 font-bold animate-pulse' : 'text-text-muted']">
                                <svg class="w-3.5 h-3.5 text-text-dim" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Sisa Waktu Sesi:</span>
                                <span class="font-bold text-white bg-surface-elevated px-2 py-0.5 rounded border border-border">
                                    {{ formattedTime }}
                                </span>
                            </div>

                            <button
                                v-if="currentSession?.status === 'active'"
                                @click="resetSession"
                                class="hidden sm:inline-flex items-center gap-1 text-[11px] text-text-dim hover:text-white transition-colors"
                            >
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                <span>Muat Ulang</span>
                            </button>
                        </div>
                    </div>

                    <!-- xterm.js Terminal Canvas Component -->
                    <div class="flex-1 w-full relative min-h-[420px] bg-[#060e20]">
                        <Terminal
                            ref="terminalRef"
                            :wsToken="currentSession?.status === 'active' ? currentSession?.ws_token : null"
                            :wsPort="currentSession?.ws_port || 3001"
                            @connected="isTerminalConnected = true"
                            @disconnected="isTerminalConnected = false"
                            @error="(msg) => { isTerminalConnected = false; errorMessage = msg; }"
                        />
                    </div>

                    <!-- Footer Status Bar Terminal -->
                    <div class="bg-surface-elevated px-4 py-2 border-t border-border flex items-center justify-between text-xs font-mono text-text-dim select-none shrink-0">
                        <div class="flex items-center gap-3">
                            <span class="text-emerald-400/90 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                <span>Sandbox Terisolasi (Ubuntu 22.04 LTS)</span>
                            </span>
                            <span class="hidden sm:inline text-border">|</span>
                            <span class="hidden sm:inline">Shell: /bin/bash</span>
                        </div>

                        <div class="flex items-center gap-4">
                            <span class="hidden md:inline">User: student (non-root)</span>
                            <span class="hidden md:inline text-border">|</span>
                            <span class="text-[11px] text-text-muted">
                                Tips: <kbd class="px-1 py-0.5 bg-surface-card rounded border border-border text-white">Ctrl</kbd> + <kbd class="px-1 py-0.5 bg-surface-card rounded border border-border text-white">L</kbd> untuk bersihkan
                            </span>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </UserLayout>
</template>
