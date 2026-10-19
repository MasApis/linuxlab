<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';
import AppCard from '@/Components/AppCard.vue';
import AppButton from '@/Components/AppButton.vue';
import FormInput from '@/Components/FormInput.vue';
import EmptyState from '@/Components/EmptyState.vue';
import type { Question } from '@/types';

interface LessonContext {
    id: number;
    title: string;
    slug: string;
    order: number;
    module: {
        id: number;
        title: string;
        slug: string;
        order: number;
    };
}

interface Props {
    lesson: LessonContext;
    questions: Question[];
}

const props = defineProps<Props>();
const page = usePage();

const isAsking = ref(false);
const searchQuery = ref('');
const activeFilter = ref<'all' | 'resolved' | 'unresolved'>('all');

const form = useForm({
    title: '',
    body: '',
});

const submitQuestion = () => {
    form.post(route('forum.store', props.lesson.slug), {
        onSuccess: () => {
            form.reset();
            isAsking.value = false;
        },
    });
};

const resolvedCount = computed(() => props.questions.filter((q) => q.is_resolved).length);
const unresolvedCount = computed(() => props.questions.filter((q) => !q.is_resolved).length);

const filteredQuestions = computed(() => {
    return props.questions.filter((q) => {
        const matchesSearch =
            searchQuery.value.trim() === '' ||
            q.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            q.body.toLowerCase().includes(searchQuery.value.toLowerCase());

        if (!matchesSearch) return false;

        if (activeFilter.value === 'resolved') {
            return q.is_resolved;
        }
        if (activeFilter.value === 'unresolved') {
            return !q.is_resolved;
        }
        return true;
    });
});
</script>

<template>
    <UserLayout>
        <Head :title="`Forum: ${lesson.title} - LinuxLab`" />

        <div class="py-8 space-y-6">
            <!-- Breadcrumbs Navigation -->
            <nav class="flex items-center flex-wrap gap-2 text-xs sm:text-sm text-text-muted font-medium">
                <Link :href="route('dashboard')" class="hover:text-brand-primary transition-colors flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Beranda</span>
                </Link>
                <span class="text-text-dim">/</span>
                <Link :href="route('modules.show', lesson.module.slug)" class="hover:text-brand-primary transition-colors">
                    Modul 0{{ lesson.module.order }}: {{ lesson.module.title }}
                </Link>
                <span class="text-text-dim">/</span>
                <Link :href="route('lessons.show', [lesson.module.slug, lesson.slug])" class="hover:text-brand-primary transition-colors">
                    Pelajaran 0{{ lesson.order }}: {{ lesson.title }}
                </Link>
                <span class="text-text-dim">/</span>
                <span class="text-white font-semibold flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-brand-primary"></span>
                    Forum Diskusi
                </span>
            </nav>

            <!-- Forum Header Card -->
            <AppCard class="p-6 sm:p-8 bg-surface-card border-border">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-mono font-bold px-2.5 py-0.5 rounded bg-brand-primary/10 text-brand-primary-light border border-brand-primary/30">
                                PELAJARAN 0{{ lesson.order }}
                            </span>
                            <span class="text-xs font-mono px-2 py-0.5 rounded bg-surface-elevated text-text-muted border border-border">
                                {{ lesson.module.title }}
                            </span>
                        </div>
                        <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                            Forum Diskusi: {{ lesson.title }}
                        </h1>
                        <p class="text-sm text-text-muted max-w-2xl leading-relaxed">
                            Diskusikan kendala, tanyakan konsep yang membingungkan, atau bagikan temuan menarik seputar materi dan latihan pada pelajaran ini bersama komunitas LinuxLab.
                        </p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-3 shrink-0">
                        <Link :href="route('lessons.practice', lesson.slug)">
                            <AppButton variant="secondary" size="md">
                                <svg class="w-4 h-4 mr-2 text-brand-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Buka Praktik Terminal
                            </AppButton>
                        </Link>
                        <AppButton
                            v-if="!isAsking"
                            variant="primary"
                            size="md"
                            @click="isAsking = true"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Tanya Pertanyaan
                        </AppButton>
                    </div>
                </div>

                <!-- Flash Message Notification -->
                <div
                    v-if="page.props.flash?.success"
                    class="mt-4 p-4 rounded-xl bg-brand-primary/10 border border-brand-primary/30 text-brand-primary-light text-sm font-medium flex items-center gap-2"
                >
                    <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ page.props.flash.success }}</span>
                </div>
            </AppCard>

            <!-- Form Buat Pertanyaan Baru (FR-FORUM-01) -->
            <transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="transform opacity-0 -translate-y-2"
                enter-to-class="transform opacity-100 translate-y-0"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="transform opacity-100 translate-y-0"
                leave-to-class="transform opacity-0 -translate-y-2"
            >
                <AppCard v-if="isAsking" class="p-6 sm:p-8 bg-surface-card border-brand-primary/40 shadow-lg shadow-brand-primary/5">
                    <div class="flex items-center justify-between pb-4 mb-4 border-b border-border">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-brand-primary"></span>
                            <h2 class="text-lg font-bold text-white">Ajukan Pertanyaan Baru</h2>
                        </div>
                        <button
                            type="button"
                            @click="isAsking = false"
                            class="text-xs text-text-muted hover:text-white transition-colors"
                        >
                            Batal
                        </button>
                    </div>

                    <form @submit.prevent="submitQuestion" class="space-y-4">
                        <!-- Judul Pertanyaan -->
                        <div>
                            <label class="block text-xs font-semibold text-text-muted uppercase tracking-wider mb-1.5">
                                Judul Pertanyaan <span class="text-accent-danger">*</span>
                            </label>
                            <input
                                v-model="form.title"
                                type="text"
                                placeholder="Contoh: Mengapa perintah cd .. membawa saya ke root bukannya folder sebelumnya?"
                                class="w-full px-4 py-2.5 rounded-xl bg-surface-base border border-border text-white text-sm placeholder:text-text-dim focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary transition-all"
                                required
                            />
                            <p v-if="form.errors.title" class="mt-1 text-xs text-accent-danger">
                                {{ form.errors.title }}
                            </p>
                        </div>

                        <!-- Isi Pertanyaan -->
                        <div>
                            <label class="block text-xs font-semibold text-text-muted uppercase tracking-wider mb-1.5">
                                Rincian Pertanyaan & Konteks <span class="text-accent-danger">*</span>
                            </label>
                            <textarea
                                v-model="form.body"
                                rows="5"
                                placeholder="Jelaskan apa yang Anda coba jalankan, hasil yang diharapkan, pesan kesalahan yang muncul, atau letak kebingungan Anda..."
                                class="w-full px-4 py-3 rounded-xl bg-surface-base border border-border text-white text-sm placeholder:text-text-dim focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary transition-all font-sans leading-relaxed"
                                required
                            ></textarea>
                            <p v-if="form.errors.body" class="mt-1 text-xs text-accent-danger">
                                {{ form.errors.body }}
                            </p>
                        </div>

                        <div class="flex items-center justify-between pt-2">
                            <span class="text-xs text-text-muted flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-brand-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Pertanyaan akan terhubung secara otomatis ke pelajaran ini.
                            </span>

                            <div class="flex items-center gap-3">
                                <AppButton
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    @click="isAsking = false"
                                >
                                    Tutup
                                </AppButton>
                                <AppButton
                                    type="submit"
                                    variant="primary"
                                    size="md"
                                    :disabled="form.processing"
                                >
                                    {{ form.processing ? 'Mengirim...' : 'Kirim Pertanyaan' }}
                                </AppButton>
                            </div>
                        </div>
                    </form>
                </AppCard>
            </transition>

            <!-- Search & Filter Controls -->
            <div class="bg-surface-card p-4 rounded-xl border border-border flex flex-col sm:flex-row gap-3 sm:items-center justify-between">
                <!-- Search Box -->
                <div class="relative flex-1 max-w-md">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-text-muted">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Cari pertanyaan diskusi..."
                        class="w-full pl-9 pr-4 py-2 rounded-lg bg-surface-base border border-border text-sm text-white placeholder:text-text-dim focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary transition-colors"
                    />
                </div>

                <!-- Filter Chips -->
                <div class="flex items-center gap-1.5 p-1 bg-surface-base rounded-lg border border-border">
                    <button
                        type="button"
                        @click="activeFilter = 'all'"
                        class="px-3 py-1 text-xs font-semibold rounded transition-colors"
                        :class="[
                            activeFilter === 'all'
                                ? 'bg-brand-primary text-surface-base'
                                : 'text-text-muted hover:text-white',
                        ]"
                    >
                        Semua ({{ questions.length }})
                    </button>
                    <button
                        type="button"
                        @click="activeFilter = 'resolved'"
                        class="px-3 py-1 text-xs font-semibold rounded transition-colors"
                        :class="[
                            activeFilter === 'resolved'
                                ? 'bg-brand-primary text-surface-base'
                                : 'text-text-muted hover:text-white',
                        ]"
                    >
                        Terjawab ({{ resolvedCount }})
                    </button>
                    <button
                        type="button"
                        @click="activeFilter = 'unresolved'"
                        class="px-3 py-1 text-xs font-semibold rounded transition-colors"
                        :class="[
                            activeFilter === 'unresolved'
                                ? 'bg-brand-primary text-surface-base'
                                : 'text-text-muted hover:text-white',
                        ]"
                    >
                        Menunggu ({{ unresolvedCount }})
                    </button>
                </div>
            </div>

            <!-- Daftar Pertanyaan (FR-FORUM-05: Terurut dari yang terbaru) -->
            <div v-if="filteredQuestions.length > 0" class="space-y-3">
                <Link
                    v-for="q in filteredQuestions"
                    :key="q.id"
                    :href="route('forum.show', q.id)"
                    class="block group"
                >
                    <AppCard class="p-5 sm:p-6 bg-surface-card border-border hover:border-brand-primary/50 transition-all duration-150">
                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                            <div class="space-y-2 flex-1">
                                <!-- Status Badge & Metadata -->
                                <div class="flex items-center gap-2 flex-wrap text-xs">
                                    <span
                                        v-if="q.is_resolved"
                                        class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full font-mono text-[11px] font-semibold bg-brand-primary/10 text-brand-primary-light border border-brand-primary/30"
                                    >
                                        <svg class="w-3.5 h-3.5 text-brand-primary" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        Terjawab
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full font-mono text-[11px] font-semibold bg-brand-secondary/10 text-brand-secondary border border-brand-secondary/30"
                                    >
                                        <svg class="w-3.5 h-3.5 text-brand-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Menunggu Jawaban
                                    </span>

                                    <span class="text-text-dim">•</span>
                                    <span class="text-text-muted">{{ q.created_at_human || q.created_at }}</span>
                                    <span class="text-text-dim">•</span>
                                    <span class="text-text-muted">oleh <strong class="text-text-primary">{{ q.user.name }}</strong></span>
                                </div>

                                <!-- Judul Pertanyaan (di-escape via {{ }}) -->
                                <h3 class="text-base sm:text-lg font-bold text-white group-hover:text-brand-primary transition-colors leading-snug">
                                    {{ q.title }}
                                </h3>

                                <!-- Cuplikan Isi -->
                                <p class="text-xs sm:text-sm text-text-muted line-clamp-2 leading-relaxed">
                                    {{ q.body }}
                                </p>
                            </div>

                            <!-- Jawaban Counter Pill -->
                            <div class="sm:text-right shrink-0 flex sm:flex-col items-center sm:items-end gap-1.5 sm:gap-1">
                                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-surface-elevated border border-border text-xs font-mono font-medium text-text-primary">
                                    <svg class="w-4 h-4 text-brand-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                    </svg>
                                    <span>{{ q.answers_count }} Jawaban</span>
                                </div>
                            </div>
                        </div>
                    </AppCard>
                </Link>
            </div>

            <!-- Empty State jika belum ada pertanyaan sama sekali atau hasil pencarian nihil (UI-02) -->
            <EmptyState
                v-else
                icon="forum"
                :title="searchQuery ? 'Pertanyaan Tidak Ditemukan' : 'Belum Ada Diskusi Pada Pelajaran Ini'"
                :description="searchQuery ? 'Coba gunakan kata kunci pencarian yang lain atau bersihkan filter.' : 'Jadilah yang pertama mengajukan pertanyaan atau memulai diskusi untuk membantu proses belajar teman-teman lainnya!'"
            >
                <template #action v-if="!searchQuery && !isAsking">
                    <AppButton variant="primary" size="md" @click="isAsking = true">
                        + Ajukan Pertanyaan Pertama
                    </AppButton>
                </template>
            </EmptyState>
        </div>
    </UserLayout>
</template>
