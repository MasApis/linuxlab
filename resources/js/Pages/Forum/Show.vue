<script setup lang="ts">
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';
import AppCard from '@/Components/AppCard.vue';
import AppButton from '@/Components/AppButton.vue';
import type { Question, Answer } from '@/types';

interface LessonContext {
    id: number;
    title: string;
    slug: string;
    module: {
        id: number;
        title: string;
        slug: string;
    };
}

interface Props {
    question: Question;
    lesson: LessonContext;
    answers: Answer[];
}

const props = defineProps<Props>();
const page = usePage();

const answerForm = useForm({
    body: '',
});

const submitAnswer = () => {
    answerForm.post(route('forum.answers.store', props.question.id), {
        preserveScroll: true,
        onSuccess: () => {
            answerForm.reset();
        },
    });
};

const upvoteAnswer = (answer: Answer) => {
    if (answer.is_own_answer || answer.has_voted) {
        return;
    }

    router.post(route('answers.vote', answer.id), {}, {
        preserveScroll: true,
    });
};

const markBestAnswer = (answer: Answer) => {
    if (!props.question.is_owner || answer.is_best_answer) {
        return;
    }

    router.post(route('answers.best', answer.id), {}, {
        preserveScroll: true,
    });
};
</script>

<template>
    <UserLayout>
        <Head :title="`${question.title} - Forum LinuxLab`" />

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
                    {{ lesson.module.title }}
                </Link>
                <span class="text-text-dim">/</span>
                <Link :href="route('lessons.show', [lesson.module.slug, lesson.slug])" class="hover:text-brand-primary transition-colors">
                    {{ lesson.title }}
                </Link>
                <span class="text-text-dim">/</span>
                <Link :href="route('forum.index', lesson.slug)" class="hover:text-brand-primary transition-colors">
                    Forum
                </Link>
                <span class="text-text-dim">/</span>
                <span class="text-white font-semibold truncate max-w-xs sm:max-w-md">
                    {{ question.title }}
                </span>
            </nav>

            <!-- Flash Notification -->
            <div
                v-if="page.props.flash?.success"
                class="p-4 rounded-xl bg-brand-primary/10 border border-brand-primary/30 text-brand-primary-light text-sm font-medium flex items-center gap-2"
            >
                <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span>{{ page.props.flash.success }}</span>
            </div>

            <!-- Kartu Pertanyaan Utama -->
            <AppCard class="p-6 sm:p-8 bg-surface-card border-border shadow-md">
                <div class="space-y-4">
                    <!-- Metadata Header -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-border">
                        <div class="flex items-center gap-3">
                            <!-- Avatar inisial -->
                            <div class="w-10 h-10 rounded-full bg-surface-elevated border border-border flex items-center justify-center font-bold font-mono text-sm text-brand-primary">
                                {{ question.user.name.charAt(0).toUpperCase() }}
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-white text-sm sm:text-base">{{ question.user.name }}</span>
                                    <span class="text-xs font-mono text-text-dim">@{{ question.user.username }}</span>
                                    <span
                                        v-if="question.is_owner"
                                        class="px-2 py-0.5 rounded text-[10px] font-mono bg-brand-primary/10 text-brand-primary-light border border-brand-primary/30"
                                    >
                                        Anda (Penanya)
                                    </span>
                                </div>
                                <div class="text-xs text-text-muted mt-0.5">
                                    Ditanyakan {{ question.created_at_human || question.created_at }}
                                </div>
                            </div>
                        </div>

                        <!-- Status Pill -->
                        <div>
                            <span
                                v-if="question.is_resolved"
                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-mono font-semibold bg-brand-primary/10 text-brand-primary-light border border-brand-primary/30"
                            >
                                <svg class="w-4 h-4 text-brand-primary" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                Terselesaikan
                            </span>
                            <span
                                v-else
                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-mono font-semibold bg-brand-secondary/10 text-brand-secondary border border-brand-secondary/30"
                            >
                                <svg class="w-4 h-4 text-brand-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Menunggu Jawaban
                            </span>
                        </div>
                    </div>

                    <!-- Judul Pertanyaan (di-escape via {{ }}) -->
                    <h1 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight leading-snug">
                        {{ question.title }}
                    </h1>

                    <!-- Isi Pertanyaan (di-escape via {{ }}, pre-wrap untuk format baris) -->
                    <div class="text-sm sm:text-base text-text-primary leading-relaxed whitespace-pre-wrap font-sans bg-surface-base/50 p-4 rounded-xl border border-border/60">
                        {{ question.body }}
                    </div>

                    <!-- Footer Kartu Pertanyaan -->
                    <div class="flex items-center justify-between pt-2 text-xs text-text-muted">
                        <span class="flex items-center gap-1">
                            <span class="font-mono text-brand-primary-light">Pelajaran:</span>
                            <span>{{ lesson.title }}</span>
                        </span>
                        <Link
                            :href="route('forum.index', lesson.slug)"
                            class="text-brand-primary hover:underline flex items-center gap-1"
                        >
                            <span>&larr; Kembali ke daftar diskusi</span>
                        </Link>
                    </div>
                </div>
            </AppCard>

            <!-- Seksi Jawaban -->
            <div class="space-y-4">
                <!-- Header Daftar Jawaban -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <h2 class="text-lg sm:text-xl font-bold text-white">
                            {{ answers.length }} Jawaban
                        </h2>
                        <span class="text-xs text-text-muted">
                            (Jawaban terbaik di teratas, diurutkan menurut upvote terbanyak)
                        </span>
                    </div>
                </div>

                <!-- List Jawaban (FR-FORUM-05: Best answer teratas, lalu upvotes terbanyak) -->
                <div v-if="answers.length > 0" class="space-y-4">
                    <AppCard
                        v-for="ans in answers"
                        :key="ans.id"
                        class="p-6 bg-surface-card transition-all"
                        :class="[
                            ans.is_best_answer
                                ? 'border-2 border-brand-primary shadow-lg shadow-brand-primary/5 relative'
                                : 'border-border',
                        ]"
                    >
                        <!-- Banner Jawaban Terbaik -->
                        <div
                            v-if="ans.is_best_answer"
                            class="mb-4 pb-3 border-b border-border flex items-center justify-between"
                        >
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-mono font-bold bg-brand-primary/10 text-brand-primary-light border border-brand-primary/30">
                                <svg class="w-4 h-4 text-brand-primary" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                Jawaban Terbaik
                            </span>
                            <span class="text-xs text-brand-primary-light font-medium">
                                Terpilih oleh penanya
                            </span>
                        </div>

                        <!-- Info Penulis Jawaban -->
                        <div class="flex items-center justify-between gap-3 mb-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-full bg-surface-elevated border border-border flex items-center justify-center font-bold font-mono text-xs text-brand-primary">
                                    {{ ans.user.name.charAt(0).toUpperCase() }}
                                </div>
                                <div>
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-bold text-white text-xs sm:text-sm">{{ ans.user.name }}</span>
                                        <span class="text-[11px] font-mono text-text-dim">@{{ ans.user.username }}</span>
                                        <span
                                            v-if="ans.is_own_answer"
                                            class="px-1.5 py-0.2 rounded text-[10px] font-mono bg-surface-elevated text-text-muted border border-border"
                                        >
                                            Jawaban Anda
                                        </span>
                                    </div>
                                    <div class="text-[11px] text-text-muted">
                                        {{ ans.created_at_human || ans.created_at }}
                                    </div>
                                </div>
                            </div>

                            <!-- Tombol Tandai Jawaban Terbaik (Hanya Penanya, FR-FORUM-04) -->
                            <div v-if="question.is_owner && !ans.is_best_answer">
                                <AppButton
                                    variant="secondary"
                                    size="sm"
                                    @click="markBestAnswer(ans)"
                                >
                                    <svg class="w-3.5 h-3.5 mr-1 text-brand-primary" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    Tandai Terbaik
                                </AppButton>
                            </div>
                        </div>

                        <!-- Isi Jawaban (SEC-GEN-01: di-escape dengan {{ }}) -->
                        <div class="text-sm text-text-primary leading-relaxed whitespace-pre-wrap font-sans my-3">
                            {{ ans.body }}
                        </div>

                        <!-- Aksi Jawaban (Upvote, FR-FORUM-03) -->
                        <div class="flex items-center justify-between pt-3 border-t border-border mt-3">
                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    @click="upvoteAnswer(ans)"
                                    :disabled="ans.is_own_answer || ans.has_voted"
                                    :title="ans.is_own_answer ? 'Tidak dapat meng-upvote jawaban sendiri' : (ans.has_voted ? 'Anda sudah meng-upvote jawaban ini' : 'Beri upvote')"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all"
                                    :class="[
                                        ans.has_voted
                                            ? 'bg-brand-primary text-surface-base shadow-sm'
                                            : ans.is_own_answer
                                                ? 'bg-surface-elevated text-text-dim cursor-not-allowed border border-border'
                                                : 'bg-surface-elevated hover:bg-brand-primary/10 hover:text-brand-primary-light text-text-muted border border-border active:scale-95',
                                    ]"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5" />
                                    </svg>
                                    <span>{{ ans.has_voted ? 'Di-upvote' : 'Upvote' }}</span>
                                    <span class="ml-1 px-1.5 py-0.2 rounded-full font-mono text-[11px] bg-black/20">
                                        {{ ans.votes_count }}
                                    </span>
                                </button>
                                <span v-if="ans.is_own_answer" class="text-[11px] text-text-dim italic">
                                    (Jawaban milik Anda)
                                </span>
                            </div>

                            <span class="text-xs text-text-dim font-mono">
                                #{{ ans.id }}
                            </span>
                        </div>
                    </AppCard>
                </div>

                <!-- Empty State jika belum ada jawaban -->
                <AppCard v-else class="p-8 text-center bg-surface-card/60 border-dashed border-border">
                    <p class="text-sm text-text-muted">
                        Belum ada jawaban untuk pertanyaan ini. Jadilah yang pertama memberikan solusi atau saran bantuan!
                    </p>
                </AppCard>

                <!-- Form Tulis Jawaban Baru (FR-FORUM-02) -->
                <AppCard class="p-6 bg-surface-card border-border space-y-4">
                    <div class="flex items-center gap-2 pb-2 border-b border-border">
                        <svg class="w-5 h-5 text-brand-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        <h3 class="text-base font-bold text-white">Tulis Jawaban Anda</h3>
                    </div>

                    <form @submit.prevent="submitAnswer" class="space-y-4">
                        <div>
                            <textarea
                                v-model="answerForm.body"
                                rows="4"
                                placeholder="Tuliskan solusi, penjelasan langkah demi langkah, atau perintah alternatif yang dapat membantu penanya..."
                                class="w-full px-4 py-3 rounded-xl bg-surface-base border border-border text-white text-sm placeholder:text-text-dim focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary transition-all font-sans leading-relaxed"
                                required
                            ></textarea>
                            <p v-if="answerForm.errors.body" class="mt-1 text-xs text-accent-danger">
                                {{ answerForm.errors.body }}
                            </p>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-xs text-text-muted">
                                Hargai sesama pembelajar dan berikan penjelasan yang ramah pemula.
                            </span>
                            <AppButton
                                type="submit"
                                variant="primary"
                                size="md"
                                :disabled="answerForm.processing || answerForm.body.trim().length === 0"
                            >
                                {{ answerForm.processing ? 'Mengirim...' : 'Kirim Jawaban' }}
                            </AppButton>
                        </div>
                    </form>
                </AppCard>
            </div>
        </div>
    </UserLayout>
</template>
