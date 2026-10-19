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
}

const props = defineProps<Props>();

const currentSession = ref<SessionData | null>(props.initialSession || null);
const remainingSeconds = ref<number>(props.initialSession?.remaining_seconds || 0);
const terminalRef = ref<InstanceType<typeof Terminal> | null>(null);
const isLoading = ref(false);
const errorMessage = ref<string | null>(null);
const isTerminalConnected = ref(false);
const isFullscreen = ref(false);

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
            exercise_id: null,
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

// Reset / Akhiri sesi saat ini (FR-TERM-04, SEC-TERM-06)
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

        // Otomatis buat sesi baru yang bersih
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

onMounted(() => {
    if (currentSession.value && currentSession.value.status === 'active') {
        startTimer();
    } else {
        // Otomatis mulai sesi jika belum ada
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
                    <span>Praktik Terminal</span>
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
                    Coba Lagi
                </button>
            </div>

            <!-- Workspace 2 Kolom (Split Screen: Kiri 35%, Kanan 65%) Sesuai Wireframe -->
            <div class="flex flex-col lg:flex-row gap-6 items-stretch" :class="{ 'fixed inset-0 z-50 bg-surface-base p-4': isFullscreen }">
                <!-- Panel Kiri: Panduan & Peringatan Non-Persisten (35%) -->
                <aside class="w-full lg:w-[35%] flex flex-col gap-4" :class="{ 'hidden lg:flex': isFullscreen }">
                    <!-- Kartu Info Pelajaran & Tujuan Praktik -->
                    <AppCard class="p-5 border-border space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="px-2.5 py-0.5 rounded text-xs font-mono font-semibold bg-brand-primary/10 text-brand-primary border border-brand-primary/20">
                                Pelajaran {{ lesson.order }}
                            </span>
                            <span class="text-xs font-mono text-text-dim">
                                Modul {{ module.title }}
                            </span>
                        </div>

                        <div>
                            <h1 class="text-lg sm:text-xl font-bold text-white tracking-tight">
                                {{ lesson.title }}
                            </h1>
                            <p class="text-xs text-text-muted mt-1 leading-relaxed">
                                Gunakan terminal interaktif di samping untuk mempraktikkan langsung materi Linux.
                            </p>
                        </div>

                        <!-- Tujuan Praktik -->
                        <div class="p-3.5 rounded-lg bg-surface-elevated border border-border/80 space-y-2">
                            <div class="flex items-center gap-2 text-brand-primary font-semibold text-xs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Lingkungan Praktik Nyata</span>
                            </div>
                            <p class="text-xs text-text-muted leading-relaxed">
                                Anda terhubung ke sistem operasi <strong class="text-white">Ubuntu 22.04 LTS</strong> di dalam container terisolasi yang aman. Cobalah perintah-perintah dasar Linux seperti <code class="px-1 py-0.5 bg-surface-card rounded text-brand-primary-light font-mono text-[11px]">pwd</code>, <code class="px-1 py-0.5 bg-surface-card rounded text-brand-primary-light font-mono text-[11px]">ls -la</code>, dan <code class="px-1 py-0.5 bg-surface-card rounded text-brand-primary-light font-mono text-[11px]">whoami</code>.
                            </p>
                        </div>

                        <!-- FR-TERM-05 & SEC-TERM-06 Keterangan Non-Persisten -->
                        <div class="p-3.5 rounded-lg bg-amber-950/30 border border-amber-500/30 text-amber-200/90 text-xs space-y-1.5">
                            <div class="flex items-center gap-1.5 font-bold text-amber-400">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <span>Perhatian: Berkas Tidak Persisten</span>
                            </div>
                            <p class="leading-relaxed text-[11px] text-amber-200/80">
                                File dan direktori yang Anda buat di dalam terminal ini hanya tersimpan selama sesi aktif berlangsung. Saat sesi berakhir atau di-reset, container akan dihancurkan dan lingkungan kembali bersih (FR-TERM-05).
                            </p>
                        </div>
                    </AppCard>

                    <!-- Aksi & Navigasi -->
                    <div class="flex flex-col gap-2.5">
                        <AppButton
                            v-if="!currentSession || currentSession.status !== 'active'"
                            variant="primary"
                            size="md"
                            class="w-full justify-center"
                            :loading="isLoading"
                            @click="startSession"
                        >
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Mulai Sesi Praktik</span>
                        </AppButton>

                        <div v-else class="grid grid-cols-2 gap-2">
                            <AppButton
                                variant="secondary"
                                size="sm"
                                class="w-full justify-center text-xs"
                                :disabled="isLoading"
                                @click="resetSession"
                                title="Hancurkan container dan mulai ulang dengan lingkungan bersih"
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

                <!-- Panel Kanan: Jendela Terminal Interaktif (65%) Sesuai Wireframe -->
                <section
                    class="w-full flex flex-col rounded-xl overflow-hidden border border-border bg-[#060e20] shadow-2xl transition-all"
                    :class="[
                        isFullscreen ? 'fixed inset-4 z-50 h-[calc(100vh-2rem)]' : 'lg:w-[65%] min-h-[580px] h-full flex-1'
                    ]"
                >
                    <!-- macOS/Linux Window Header Bar -->
                    <div class="bg-surface-elevated px-4 py-2.5 flex items-center justify-between border-b border-border select-none shrink-0">
                        <!-- Traffic light buttons & label -->
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

                        <!-- Window Actions -->
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

                    <!-- Terminal Status & Countdown Bar (Sub-header) -->
                    <div class="bg-surface-card/60 px-4 py-2 border-b border-border/80 flex items-center justify-between text-xs font-mono shrink-0">
                        <!-- Indikator Status Koneksi -->
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

                    <!-- Terminal Footer Status Bar Sesuai Wireframe -->
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
