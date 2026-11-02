<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AppCard from '@/Components/AppCard.vue';

interface AnswerItem {
    id: number;
    question_id: number;
    body: string;
    is_best_answer: boolean;
    created_at: string;
    votes_count: number;
    user?: { id: number; name: string; username: string };
}

interface QuestionItem {
    id: number;
    lesson_id: number;
    title: string;
    body: string;
    is_resolved: boolean;
    created_at: string;
    answers_count: number;
    user?: { id: number; name: string; username: string };
    lesson?: { id: number; title: string; slug: string };
    answers?: AnswerItem[];
}

interface PaginatedData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
}

interface Props {
    questions: PaginatedData<QuestionItem>;
    filters: {
        status?: string | null;
        search?: string | null;
    };
}

const props = defineProps<Props>();

const searchInput = ref(props.filters.search ?? '');
const statusFilter = ref(props.filters.status ?? '');
const expandedQuestionIds = ref<number[]>([]);

const toggleExpand = (id: number) => {
    const idx = expandedQuestionIds.value.indexOf(id);
    if (idx > -1) {
        expandedQuestionIds.value.splice(idx, 1);
    } else {
        expandedQuestionIds.value.push(id);
    }
};

const applyFilters = () => {
    router.get(
        '/admin/forum',
        {
            search: searchInput.value || undefined,
            status: statusFilter.value || undefined,
        },
        { preserveState: true, replace: true }
    );
};

watch(statusFilter, () => {
    applyFilters();
});

const deleteQuestion = (q: QuestionItem) => {
    if (confirm(`Hapus pertanyaan "${q.title}"? Seluruh jawaban dan upvote terkait akan ikut terhapus secara permanen.`)) {
        router.delete(`/admin/forum/questions/${q.id}`, {
            preserveScroll: true,
        });
    }
};

const deleteAnswer = (ans: AnswerItem) => {
    if (confirm('Hapus jawaban forum ini? Seluruh upvote terkait akan ikut terhapus.')) {
        router.delete(`/admin/forum/answers/${ans.id}`, {
            preserveScroll: true,
        });
    }
};

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
        <Head title="Moderasi Forum - Admin LinuxLab" />

        <div class="space-y-6">
            <!-- Header Halaman -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-surface-card p-6 rounded-card border border-border">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <svg class="w-6 h-6 text-brand-primary-light" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        <h1 class="text-xl sm:text-2xl font-bold text-text-primary tracking-tight">
                            Moderasi Forum Komunitas
                        </h1>
                    </div>
                    <p class="text-sm text-text-muted">
                        Tinjau dan moderasi diskusi pelajaran. Hapus pertanyaan atau jawaban yang tidak pantas beserta data ikutannya (FR-ADMIN-03).
                    </p>
                </div>
            </div>

            <!-- Toolbar Pencarian & Filter -->
            <div class="bg-surface-card rounded-card border border-border p-4 flex flex-col sm:flex-row gap-3 items-center justify-between">
                <form @submit.prevent="applyFilters" class="relative w-full sm:w-80">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-text-dim pointer-events-none">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input
                        v-model="searchInput"
                        type="text"
                        placeholder="Cari kata kunci judul/isi/penulis..."
                        class="w-full bg-surface-elevated pl-8 pr-3 py-1.5 rounded-input border border-border text-xs text-text-primary placeholder:text-text-muted focus:border-brand-primary outline-none"
                    />
                </form>

                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <select
                        v-model="statusFilter"
                        class="bg-surface-elevated border border-border text-xs rounded-input px-3 py-1.5 text-text-primary focus:border-brand-primary outline-none"
                    >
                        <option value="">Semua Status Pertanyaan</option>
                        <option value="resolved">Terselesaikan (Ada Jawaban Terbaik)</option>
                        <option value="unresolved">Terbuka / Belum Terjawab</option>
                    </select>
                </div>
            </div>

            <!-- Daftar Pertanyaan Forum -->
            <div class="space-y-4">
                <div
                    v-for="question in questions.data"
                    :key="question.id"
                    class="bg-surface-card rounded-card border border-border p-5 space-y-4 hover:border-border-focus transition-colors"
                >
                    <!-- Header Pertanyaan -->
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                        <div class="space-y-1 flex-1">
                            <div class="flex items-center gap-2 flex-wrap text-xs">
                                <span
                                    class="px-2 py-0.5 rounded text-[10px] font-mono font-semibold"
                                    :class="question.is_resolved ? 'bg-brand-primary/15 text-brand-primary-light border border-brand-primary/30' : 'bg-amber-400/15 text-amber-700 border border-amber-400/30'"
                                >
                                    {{ question.is_resolved ? '✓ Selesai' : '⏳ Terbuka' }}
                                </span>
                                <span class="text-text-muted">
                                    Pelajaran: <strong class="text-text-primary">{{ question.lesson?.title || 'Pelajaran' }}</strong>
                                </span>
                                <span class="text-text-dim">•</span>
                                <span class="text-text-dim">{{ formatDate(question.created_at) }}</span>
                            </div>

                            <h3 class="text-base font-bold text-text-primary">
                                {{ question.title }}
                            </h3>
                            <p class="text-xs text-text-muted leading-relaxed whitespace-pre-line">
                                {{ question.body }}
                            </p>

                            <div class="text-xs text-text-dim pt-1 flex items-center gap-2">
                                <span>Penanya: <strong class="text-text-primary">{{ question.user?.name || 'Pengguna nonaktif' }}</strong> (@{{ question.user?.username || 'nonaktif' }})</span>
                            </div>
                        </div>

                        <!-- Aksi Pertanyaan -->
                        <div class="flex items-center gap-2 shrink-0">
                            <button
                                type="button"
                                @click="toggleExpand(question.id)"
                                class="px-2.5 py-1 text-xs font-mono rounded bg-surface-elevated hover:bg-surface-high border border-border text-brand-tertiary-light"
                            >
                                {{ expandedQuestionIds.includes(question.id) ? 'Tutup Jawaban' : `Lihat Jawaban (${question.answers_count})` }}
                            </button>
                            <button
                                type="button"
                                @click="deleteQuestion(question)"
                                class="px-2.5 py-1 text-xs font-semibold rounded bg-brand-danger/10 hover:bg-brand-danger/20 text-brand-danger-light border border-brand-danger/30 inline-flex items-center gap-1"
                                title="Hapus Pertanyaan Beserta Jawaban"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                <span>Hapus Pertanyaan</span>
                            </button>
                        </div>
                    </div>

                    <!-- Panel Jawaban Terkait (Expanded) -->
                    <div
                        v-if="expandedQuestionIds.includes(question.id)"
                        class="pt-3 border-t border-border space-y-3 bg-surface-elevated/40 p-4 rounded-card"
                    >
                        <h4 class="text-xs font-mono uppercase tracking-wider text-text-muted">
                            Daftar Jawaban ({{ question.answers?.length ?? 0 }})
                        </h4>

                        <div v-if="question.answers && question.answers.length > 0" class="space-y-3">
                            <div
                                v-for="ans in question.answers"
                                :key="ans.id"
                                class="p-3 rounded-card bg-surface-card border border-border flex items-start justify-between gap-3 text-xs"
                                :class="{ 'border-brand-primary/40 bg-brand-primary/5': ans.is_best_answer }"
                            >
                                <div class="space-y-1 flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-text-primary">
                                            {{ ans.user?.name || 'Pengguna nonaktif' }}
                                        </span>
                                        <span class="text-text-dim">•</span>
                                        <span class="text-text-dim">{{ formatDate(ans.created_at) }}</span>
                                        <span
                                            v-if="ans.is_best_answer"
                                            class="px-1.5 py-0.5 rounded text-[10px] bg-brand-primary/15 text-brand-primary-light font-mono font-bold border border-brand-primary/30"
                                        >
                                            ★ Jawaban Terbaik
                                        </span>
                                    </div>
                                    <p class="text-text-primary leading-relaxed whitespace-pre-line">{{ ans.body }}</p>
                                    <div class="text-[11px] font-mono text-text-muted">
                                        ▲ {{ ans.votes_count }} upvote
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    @click="deleteAnswer(ans)"
                                    class="p-1 rounded text-brand-danger-light hover:bg-surface-elevated shrink-0"
                                    title="Hapus Jawaban"
                                >
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div v-else class="text-center py-4 text-xs text-text-muted italic">
                            Belum ada jawaban pada pertanyaan ini.
                        </div>
                    </div>
                </div>

                <div v-if="questions.data.length === 0" class="text-center py-12 bg-surface-card rounded-card border border-border text-xs text-text-muted">
                    Tidak ada pertanyaan forum yang cocok.
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
