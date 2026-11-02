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
const isFullscreen = ref(false);

// Status koneksi WebSocket terminal: 'disconnected' | 'connecting' | 'connected' | 'error'
const connectionStatus = ref<'disconnected' | 'connecting' | 'connected' | 'error'>('disconnected');
const connectionErrorDetail = ref<string | null>(null);

// State Latihan & Penilaian (Fase 4: FR-EX-01 s.d. 04)
const exercisesList = ref<ExerciseItem[]>([...props.exercises]);
const activeExerciseIndex = ref(0);
const isChecking = ref(false);
const isRequestingHint = ref(false);
const isHintAccordionOpen = ref(true);

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
    connectionStatus.value = 'disconnected';
    errorMessage.value = 'Masa berlaku sesi terminal Anda telah habis (kedaluwarsa). Silakan mulai sesi baru.';
};

// Mulai sesi baru (FR-TERM-01)
const startSession = async () => {
    isLoading.value = true;
    errorMessage.value = null;
    connectionErrorDetail.value = null;

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

    if (!confirm('Apakah Anda yakin ingin mengatur ulang (reset) sesi ini? Seluruh berkas latihan sementara akan dibuang.')) {
        return;
    }

    isLoading.value = true;
    errorMessage.value = null;

    try {
        await axios.delete(`/terminal/sessions/${currentSession.value.id}`);
        stopTimer();
        currentSession.value = null;
        remainingSeconds.value = 0;
        connectionStatus.value = 'disconnected';

        await startSession();
    } catch (err: any) {
        errorMessage.value = 'Gagal mengakhiri sesi terminal.';
    } finally {
        isLoading.value = false;
    }
};

const endSession = async () => {
    if (!currentSession.value) return;

    if (!confirm('Akhiri sesi terminal ini? Berkas sementara yang dibuat akan dibuang secara permanen.')) {
        return;
    }

    isLoading.value = true;
    try {
        await axios.delete(`/terminal/sessions/${currentSession.value.id}`);
        stopTimer();
        currentSession.value = null;
        remainingSeconds.value = 0;
        connectionStatus.value = 'disconnected';
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

const retryTerminalConnection = () => {
    connectionErrorDetail.value = null;
    terminalRef.value?.reconnect();
};

// Handler event koneksi Terminal.vue
const onTerminalConnected = () => {
    connectionStatus.value = 'connected';
    connectionErrorDetail.value = null;
};

const onTerminalConnecting = () => {
    connectionStatus.value = 'connecting';
};

const onTerminalDisconnected = () => {
    connectionStatus.value = 'disconnected';
};

const onTerminalError = (msg: string) => {
    connectionStatus.value = 'error';
    connectionErrorDetail.value = msg;
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
            isHintAccordionOpen.value = true;
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

// Helper format teks instruksi dengan tag code sederhana (escape dulu agar aman dari XSS)
const escapeHtml = (str: string) =>
    str
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

const formatInstruction = (text: string) => {
    if (!text) return '';
    const escaped = escapeHtml(text);
    return escaped.replace(/`([^`]+)`/g, '<code class="px-1.5 py-0.5 rounded bg-surface-card text-brand-primary-light font-mono text-xs border border-border/70 font-semibold">$1</code>');
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

        <!-- Container Utama: Lebih lega, ergonomis, dan tidak mepet -->
        <div class="py-5 lg:py-7 space-y-6 max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Breadcrumbs Navigasi yang Elegan -->
            <nav class="flex items-center justify-between gap-3 text-xs sm:text-sm text-text-muted font-medium pb-1 border-b border-border/40">
                <div class="flex items-center gap-2 overflow-x-auto whitespace-nowrap py-0.5">
                    <Link :href="route('dashboard')" class="hover:text-brand-primary-light transition-colors flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-text-dim" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        <span>Dashboard</span>
                    </Link>
                    <span class="text-text-dim">/</span>
                    <Link :href="route('modules.show', module.slug)" class="hover:text-brand-primary-light transition-colors">
                        {{ module.title }}
                    </Link>
                    <span class="text-text-dim">/</span>
                    <Link :href="route('lessons.show', [module.slug, lesson.slug])" class="hover:text-brand-primary-light transition-colors">
                        {{ lesson.title }}
                    </Link>
                    <span class="text-text-dim">/</span>
                    <span class="text-ink font-semibold flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-brand-primary inline-block"></span>
                        <span>Ruang Praktik & Latihan</span>
                    </span>
                </div>

                <div class="hidden md:flex items-center gap-3">
                    <Link
                        :href="route('lessons.show', [module.slug, lesson.slug])"
                        class="text-xs text-text-muted hover:text-text-primary transition-colors flex items-center gap-1.5"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        <span>Baca Materi Pelajaran</span>
                    </Link>
                </div>
            </nav>

            <!-- Error Banner Global -->
            <div
                v-if="errorMessage"
                class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm flex items-start justify-between gap-4 shadow-sm"
            >
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span class="leading-relaxed">{{ errorMessage }}</span>
                </div>
                <button
                    v-if="!currentSession || currentSession.status !== 'active'"
                    @click="startSession"
                    class="px-3.5 py-1.5 bg-red-800 hover:bg-red-700 text-white rounded-lg text-xs font-semibold shrink-0 transition-colors shadow-sm"
                >
                    Mulai Sesi Baru
                </button>
            </div>

            <!-- Warning Gateway Offline Banner Jika Koneksi WebSocket Gagal -->
            <div
                v-if="connectionStatus === 'error' && currentSession?.status === 'active'"
                class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs sm:text-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-sm"
            >
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="space-y-1">
                        <div class="font-bold text-amber-800">Layanan Terminal Gateway (WebSocket Port 3001) Belum Aktif</div>
                        <p class="text-text-muted text-xs leading-relaxed">
                            Container sudah aktif di latar belakang, namun shell belum terhubung ke browser. Jalankan perintah berikut pada terminal server Anda:
                            <code class="px-2 py-0.5 ml-1 bg-white text-brand-primary-light font-mono rounded border border-amber-200 font-bold">npm run terminal-gateway</code>
                        </p>
                    </div>
                </div>
                <button
                    @click="retryTerminalConnection"
                    class="px-3.5 py-1.5 bg-amber-100 hover:bg-amber-200 text-amber-800 border border-amber-300 rounded-lg text-xs font-semibold shrink-0 transition-colors flex items-center justify-center gap-1.5"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>Coba Sambungkan Lagi</span>
                </button>
            </div>

            <!-- Workspace 2 Kolom (Split Screen: Kiri 38%, Kanan 62%) Sesuai Wireframe Ergonomis -->
            <div class="flex flex-col lg:flex-row gap-6 lg:gap-8 items-stretch" :class="{ 'fixed inset-0 z-50 bg-surface-base p-4': isFullscreen }">
                <!-- PANEL KIRI: Latihan, Instruksi, Hint & Tombol Periksa (Ergonomis & Bernapas) -->
                <aside class="w-full lg:w-[40%] xl:w-[37%] flex flex-col gap-5 justify-between" :class="{ 'hidden lg:flex': isFullscreen }">
                    <div class="flex flex-col gap-5">
                        <!-- Tab Pemilih Latihan (Jika ada lebih dari 1 latihan) -->
                        <div v-if="exercisesList.length > 1" class="flex items-center gap-2 p-1.5 bg-surface-card rounded-xl border border-border">
                            <button
                                v-for="(ex, idx) in exercisesList"
                                :key="ex.id"
                                @click="selectExercise(idx)"
                                class="flex-1 py-2 px-3 rounded-lg text-xs font-semibold transition-all flex items-center justify-center gap-2"
                                :class="[
                                    activeExerciseIndex === idx
                                        ? 'bg-brand-primary text-white shadow-md font-bold'
                                        : 'text-text-muted hover:text-text-primary hover:bg-surface-high'
                                ]"
                            >
                                <span>Latihan {{ ex.order }}</span>
                                <span
                                    v-if="ex.has_passed"
                                    class="w-4 h-4 rounded-full flex items-center justify-center text-[10px]"
                                    :class="activeExerciseIndex === idx ? 'bg-white text-brand-primary-light' : 'bg-brand-primary/15 text-brand-primary-light border border-brand-primary/30'"
                                    title="Latihan ini telah selesai"
                                >
                                    ✓
                                </span>
                            </button>
                        </div>

                        <!-- Kartu Header Latihan Aktif -->
                        <AppCard v-if="currentExercise" class="p-6 border-border space-y-5">
                            <!-- Baris Tag Tingkat & XP/Poin -->
                            <div class="flex items-center justify-between">
                                <span class="px-2.5 py-1 rounded-md text-xs font-semibold bg-brand-primary/10 text-brand-primary-light border border-brand-primary/20">
                                    Tingkat: Pemula
                                </span>
                                <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-amber-100 border border-amber-200 text-amber-800 text-xs font-semibold">
                                    <svg class="w-4 h-4 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" />
                                    </svg>
                                    <span>+{{ currentExercise.points }} Poin</span>
                                </div>
                            </div>

                            <!-- Judul & Status Selesai -->
                            <div class="space-y-1.5">
                                <h1 class="font-display text-lg sm:text-xl font-bold text-ink tracking-tight leading-snug">
                                    Latihan {{ currentExercise.order }}: {{ currentExercise.title }}
                                </h1>
                                <div v-if="currentExercise.has_passed" class="inline-flex items-center gap-1.5 text-xs text-brand-primary-light font-medium">
                                    <span class="w-1.5 h-1.5 rounded-full bg-brand-primary"></span>
                                    <span>Latihan ini telah Anda selesaikan dengan benar</span>
                                </div>
                            </div>

                            <!-- Kotak Tujuan & Instruksi Tugas (FR-EX-01) -->
                            <div class="p-4 sm:p-5 rounded-xl bg-surface-elevated border border-border/80 space-y-3">
                                <div class="flex items-center gap-2 text-brand-primary-light font-semibold text-xs tracking-wide uppercase">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                    <span>Tujuan & Instruksi Praktik:</span>
                                </div>
                                <div
                                    class="text-xs sm:text-sm text-text-primary/90 leading-relaxed font-sans"
                                    v-html="formatInstruction(currentExercise.instruction)"
                                ></div>
                            </div>

                            <!-- Accordion Petunjuk (Hint) Bertahap Sesuai Wireframe -->
                            <div class="rounded-xl bg-surface-card border border-border overflow-hidden transition-all">
                                <!-- Accordion Header -->
                                <button
                                    type="button"
                                    @click="isHintAccordionOpen = !isHintAccordionOpen"
                                    class="w-full flex items-center justify-between p-4 hover:bg-surface-elevated/70 transition-colors text-left"
                                >
                                    <div class="flex items-center gap-2.5 text-amber-700 font-semibold text-xs sm:text-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                        </svg>
                                        <span>Petunjuk Latihan (Hint)</span>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <span class="text-xs font-mono text-text-dim px-2 py-0.5 rounded bg-surface-base border border-border/60">
                                            {{ currentExercise.unlocked_hints.length }} / {{ currentExercise.total_hints }} dibuka
                                        </span>
                                        <svg
                                            class="w-4 h-4 text-text-dim transition-transform duration-200"
                                            :class="{ 'rotate-180': isHintAccordionOpen }"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </button>

                                <!-- Accordion Body -->
                                <div v-if="isHintAccordionOpen" class="px-4 pb-4 pt-1 space-y-3 border-t border-border/50">
                                    <!-- Daftar Hint yang Telah Dibuka -->
                                    <div v-if="currentExercise.unlocked_hints.length > 0" class="space-y-2.5 pt-1">
                                        <div
                                            v-for="h in currentExercise.unlocked_hints"
                                            :key="h.id"
                                            class="p-3.5 rounded-lg bg-amber-50 border border-amber-200 text-xs sm:text-sm text-text-secondary space-y-1.5"
                                        >
                                            <div class="font-bold text-amber-800 text-xs flex items-center gap-1.5">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                <span>Petunjuk #{{ h.order }}</span>
                                            </div>
                                            <div class="leading-relaxed pl-3" v-html="formatInstruction(h.content)"></div>
                                        </div>
                                    </div>
                                    <div v-else class="text-xs text-text-dim italic py-1">
                                        Belum ada petunjuk yang dibuka. Tekan tombol di bawah apabila Anda membutuhkan bantuan.
                                    </div>

                                    <!-- Footer Baris Minta Hint -->
                                    <div class="pt-2 flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-t border-border/40">
                                        <span class="text-[11px] text-text-dim">
                                            Petunjuk tidak mengurangi perolehan skor nilai Anda.
                                        </span>
                                        <button
                                            v-if="remainingHintsCount > 0"
                                            @click="requestHint"
                                            :disabled="isRequestingHint"
                                            class="px-3 py-1.5 rounded-lg bg-amber-100 hover:bg-amber-200 text-amber-800 border border-amber-300 text-xs font-semibold transition-all disabled:opacity-50 flex items-center justify-center gap-1.5 shrink-0 shadow-sm"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                            </svg>
                                            <span>{{ isRequestingHint ? 'Membuka...' : `Buka Petunjuk (${remainingHintsCount} tersisa)` }}</span>
                                        </button>
                                        <span v-else class="text-xs text-text-dim font-medium text-right">
                                            Seluruh petunjuk telah dibuka
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Feedback Penilaian Real-Time (VAL-03, VAL-04, UI-03) -->
                            <div
                                v-if="lastCheckResult"
                                class="p-5 rounded-xl border space-y-3.5 transition-all shadow-md"
                                :class="[
                                    lastCheckResult.is_correct
                                        ? 'bg-brand-primary/10 border-brand-primary/40 text-text-primary'
                                        : 'bg-red-50 border-red-200 text-red-800'
                                ]"
                            >
                                <div class="flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-2.5">
                                        <span
                                            class="w-7 h-7 rounded-full flex items-center justify-center font-bold text-sm shadow-sm"
                                            :class="[
                                                lastCheckResult.is_correct ? 'bg-brand-primary-dark text-white' : 'bg-brand-danger text-white'
                                            ]"
                                        >
                                            {{ lastCheckResult.is_correct ? '✓' : '✗' }}
                                        </span>
                                        <span class="font-bold text-sm sm:text-base text-ink">
                                            {{ lastCheckResult.is_correct ? 'Jawaban Benar! Latihan Selesai' : 'Jawaban Belum Sesuai' }}
                                        </span>
                                    </div>
                                    <span
                                        v-if="lastCheckResult.is_correct"
                                        class="px-2.5 py-1 rounded-full bg-brand-primary/15 text-brand-primary-light text-xs font-bold border border-brand-primary/30 shrink-0"
                                    >
                                        +{{ lastCheckResult.score }} Poin
                                    </span>
                                </div>

                                <!-- Rincian Feedback Bahasa Indonesia -->
                                <div class="text-xs sm:text-sm space-y-1 font-mono whitespace-pre-line leading-relaxed text-text-muted pl-1">
                                    {{ lastCheckResult.feedback }}
                                </div>

                                <!-- Tombol Lanjut ke Latihan Berikutnya -->
                                <div v-if="lastCheckResult.is_correct && activeExerciseIndex < exercisesList.length - 1" class="pt-2">
                                    <button
                                        @click="goToNextExercise"
                                        class="w-full py-2.5 px-4 bg-brand-primary hover:bg-brand-primary-hover text-white font-bold rounded-button text-xs sm:text-sm transition-all flex items-center justify-center gap-2 shadow-[0_4px_0_0_#245700] active:translate-y-[4px] active:shadow-none"
                                    >
                                        <span>Lanjut ke Latihan {{ exercisesList[activeExerciseIndex + 1].order }}</span>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </AppCard>

                        <!-- Empty State Bila Tidak Ada Latihan -->
                        <AppCard v-else class="p-8 border-border text-center text-text-muted text-sm">
                            Belum ada latihan yang tersedia pada pelajaran ini.
                        </AppCard>

                        <!-- Peringatan Berkas Non-Persisten (FR-TERM-05) yang Bersahabat -->
                        <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs space-y-1">
                            <div class="flex items-center gap-1.5 font-bold text-amber-700">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Lingkungan Praktik Terisolasi & Non-Persisten</span>
                            </div>
                            <p class="leading-relaxed text-[11px] text-text-muted">
                                Berkas yang Anda buat di dalam terminal tersimpan selama sesi aktif. Saat sesi di-reset atau berakhir, container dihancurkan dan lingkungan dikembalikan bersih.
                            </p>
                        </div>
                    </div>

                    <!-- Panel Aksi Utama: Tombol Periksa Jawaban & Reset Sesi -->
                    <div class="flex flex-col gap-3 pt-2">
                        <!-- Primary CTA Button (Tegas dan Menarik) -->
                        <AppButton
                            v-if="currentExercise"
                            variant="primary"
                            size="lg"
                            class="w-full justify-center font-bold text-sm sm:text-base py-3.5 shadow-xl hover:shadow-brand-primary/20 active:scale-98"
                            :loading="isChecking"
                            :disabled="isChecking || !currentSession || currentSession.status !== 'active'"
                            @click="checkAnswer"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Periksa Jawaban Saya</span>
                        </AppButton>

                        <!-- Baris Tombol Sekunder -->
                        <div v-if="currentSession && currentSession.status === 'active'" class="grid grid-cols-2 gap-3">
                            <AppButton
                                variant="secondary"
                                size="md"
                                class="w-full justify-center text-xs"
                                :disabled="isLoading"
                                @click="resetSession"
                                title="Reset sesi terminal dan bersihkan berkas latihan"
                            >
                                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                <span>Reset Sesi</span>
                            </AppButton>

                            <AppButton
                                variant="secondary"
                                size="md"
                                class="w-full justify-center text-xs text-brand-danger hover:text-brand-danger-hover hover:border-brand-danger/50"
                                :disabled="isLoading"
                                @click="endSession"
                            >
                                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                <span>Akhiri Sesi</span>
                            </AppButton>
                        </div>

                        <AppButton
                            v-else
                            variant="primary"
                            size="lg"
                            class="w-full justify-center font-bold text-sm py-3.5"
                            :loading="isLoading"
                            @click="startSession"
                        >
                            <span>Mulai Sesi Praktik Baru</span>
                        </AppButton>
                    </div>
                </aside>

                <!-- PANEL KANAN: Terminal Linux Interaktif (60%) Sesuai Wireframe -->
                <section
                    class="w-full flex flex-col rounded-2xl overflow-hidden border border-border bg-[#060e20] shadow-2xl transition-all"
                    :class="[
                        isFullscreen ? 'fixed inset-4 z-50 h-[calc(100vh-2rem)]' : 'lg:w-[60%] xl:w-[63%] min-h-[600px] h-full flex-1'
                    ]"
                >
                    <!-- macOS/Linux Window Header Bar -->
                    <div class="bg-white/5 px-4 py-3 flex items-center justify-between border-b border-white/10 select-none shrink-0">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-1.5">
                                <button @click="endSession" title="Tutup / Akhiri Sesi" class="w-3 h-3 rounded-full bg-red-500/90 hover:opacity-80 transition-opacity cursor-pointer"></button>
                                <button @click="resetSession" title="Reset Sesi" class="w-3 h-3 rounded-full bg-yellow-500/90 hover:opacity-80 transition-opacity cursor-pointer"></button>
                                <button @click="toggleFullscreen" title="Layar Penuh" class="w-3 h-3 rounded-full bg-green-500/90 hover:opacity-80 transition-opacity cursor-pointer"></button>
                            </div>
                            <div class="h-4 w-px bg-white/10"></div>
                            <span class="font-mono text-xs text-white/60 flex items-center gap-1.5">
                                <span class="text-[#58cc02]">&gt;_</span>
                                <span>bash — student@linuxlab: ~ (Ubuntu 22.04 LTS)</span>
                            </span>
                        </div>

                        <div class="flex items-center gap-2 text-white/40">
                            <button
                                @click="clearTerminal"
                                class="p-1.5 hover:text-[#58cc02] hover:bg-white/10 rounded-md transition-colors text-xs flex items-center gap-1.5"
                                title="Bersihkan Layar Terminal (Ctrl + L)"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                <span class="hidden sm:inline">Bersihkan</span>
                            </button>

                            <button
                                @click="toggleFullscreen"
                                class="p-1.5 hover:text-[#58cc02] hover:bg-white/10 rounded-md transition-colors"
                                :title="isFullscreen ? 'Keluar Layar Penuh' : 'Layar Penuh'"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path v-if="!isFullscreen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                                    <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Sub-Header: Indikator Status Koneksi Real-Time & Countdown Timer -->
                    <div class="bg-black/30 px-4 py-2.5 border-b border-white/10 flex items-center justify-between text-xs font-mono shrink-0">
                        <!-- Indikator Status Akurat (Tidak Macet di 'Menghubungkan') -->
                        <div class="flex items-center gap-2.5">
                            <span
                                class="w-2.5 h-2.5 rounded-full"
                                :class="[
                                    connectionStatus === 'connected'
                                        ? 'bg-emerald-400'
                                        : connectionStatus === 'connecting'
                                        ? 'bg-amber-400 animate-pulse'
                                        : connectionStatus === 'error'
                                        ? 'bg-red-400'
                                        : 'bg-white/30'
                                ]"
                            ></span>
                            <span class="text-white/60">
                                Status Terminal:
                                <strong v-if="connectionStatus === 'connected'" class="text-emerald-400 font-semibold">Tersambung (Aktif)</strong>
                                <strong v-else-if="connectionStatus === 'connecting'" class="text-amber-300">Menghubungkan...</strong>
                                <strong v-else-if="connectionStatus === 'error'" class="text-red-400">Gateway Offline (Port 3001)</strong>
                                <strong v-else class="text-text-dim">Tidak Aktif</strong>
                            </span>

                            <button
                                v-if="connectionStatus === 'error'"
                                @click="retryTerminalConnection"
                                class="text-[11px] text-amber-300 hover:text-white underline ml-1 cursor-pointer"
                                title="Klik untuk mencoba sambungkan kembali"
                            >
                                Hubungkan Ulang
                            </button>
                        </div>

                        <!-- Real-time Countdown Timer (SEC-TERM-05) -->
                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-1.5" :class="[isUrgentTime ? 'text-amber-400 font-bold animate-pulse' : 'text-white/60']">
                                <svg class="w-3.5 h-3.5 text-text-dim" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Sisa Waktu:</span>
                                <span class="font-bold text-white bg-white/10 px-2 py-0.5 rounded border border-white/10">
                                    {{ formattedTime }}
                                </span>
                            </div>

                            <button
                                v-if="currentSession?.status === 'active'"
                                @click="resetSession"
                                class="hidden sm:inline-flex items-center gap-1 text-[11px] text-white/50 hover:text-white transition-colors"
                            >
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                <span>Muat Ulang</span>
                            </button>
                        </div>
                    </div>

                    <!-- xterm.js Terminal Canvas Component -->
                    <div class="flex-1 w-full relative min-h-[440px] bg-[#060e20]">
                        <Terminal
                            ref="terminalRef"
                            :wsToken="currentSession?.status === 'active' ? currentSession?.ws_token : null"
                            :wsPort="currentSession?.ws_port || 3001"
                            @connected="onTerminalConnected"
                            @connecting="onTerminalConnecting"
                            @disconnected="onTerminalDisconnected"
                            @error="onTerminalError"
                        />
                    </div>

                    <!-- Footer Status Bar Terminal Sesuai Wireframe -->
                    <div class="bg-white/5 px-4 py-2.5 border-t border-white/10 flex items-center justify-between text-xs font-mono text-white/50 select-none shrink-0">
                        <div class="flex items-center gap-3">
                            <span class="text-emerald-400/90 flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                <span>Container Sandbox Terisolasi (Ubuntu 22.04 LTS)</span>
                            </span>
                            <span class="hidden sm:inline text-white/20">|</span>
                            <span class="hidden sm:inline">Shell: /bin/bash</span>
                        </div>

                        <div class="flex items-center gap-4">
                            <span class="hidden md:inline">User: student (non-root)</span>
                            <span class="hidden md:inline text-white/20">|</span>
                            <span class="text-[11px] text-white/60">
                                Tips: <kbd class="px-1.5 py-0.5 bg-white/10 rounded border border-white/10 text-white font-sans font-semibold">Ctrl</kbd> + <kbd class="px-1.5 py-0.5 bg-white/10 rounded border border-white/10 text-white font-sans font-semibold">L</kbd> untuk bersihkan
                            </span>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </UserLayout>
</template>
