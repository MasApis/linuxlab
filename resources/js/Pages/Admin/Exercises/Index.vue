<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AppCard from '@/Components/AppCard.vue';
import AppButton from '@/Components/AppButton.vue';

interface HintItem {
    id: number;
    exercise_id: number;
    content: string;
    order: number;
}

interface LessonOption {
    id: number;
    module_id: number;
    title: string;
    slug: string;
    order: number;
}

interface ModuleOption {
    id: number;
    title: string;
    slug: string;
    order: number;
    lessons: LessonOption[];
}

interface ExerciseItem {
    id: number;
    lesson_id: number;
    title: string;
    instruction: string;
    validation_type: 'system_state' | 'output' | 'combined';
    validation_config: Record<string, unknown> | Array<unknown>;
    points: number;
    order: number;
    hints_count?: number;
    attempts_count?: number;
    lesson?: {
        id: number;
        module_id: number;
        title: string;
        slug: string;
        module?: { id: number; title: string; slug: string };
    };
    hints?: HintItem[];
}

interface Props {
    exercises: ExerciseItem[];
    modules: ModuleOption[];
    selectedLessonId: number | null;
    selectedModuleId: number | null;
}

const props = defineProps<Props>();

const filterModuleId = ref<number | ''>(props.selectedModuleId ?? '');
const filterLessonId = ref<number | ''>(props.selectedLessonId ?? '');
const searchQuery = ref('');
const sortBy = ref<'order' | 'latest'>('order');

// Filtered lesson options based on selected module
const availableLessons = computed(() => {
    if (!filterModuleId.value) {
        return props.modules.flatMap(m => m.lessons);
    }
    const foundModule = props.modules.find(m => m.id === Number(filterModuleId.value));
    return foundModule ? foundModule.lessons : [];
});

// All lessons flat for form selection
const allLessons = computed(() => props.modules.flatMap(m => m.lessons));

// Filter exercises by search, dropdowns, and sort
const filteredExercises = computed(() => {
    const list = props.exercises.filter(ex => {
        if (filterModuleId.value && ex.lesson?.module_id !== Number(filterModuleId.value)) {
            return false;
        }
        if (filterLessonId.value && ex.lesson_id !== Number(filterLessonId.value)) {
            return false;
        }
        if (searchQuery.value.trim() !== '') {
            const query = searchQuery.value.toLowerCase();
            const matchTitle = ex.title.toLowerCase().includes(query);
            const matchLesson = ex.lesson?.title?.toLowerCase().includes(query);
            const matchInstruction = ex.instruction.toLowerCase().includes(query);
            return matchTitle || matchLesson || matchInstruction;
        }
        return true;
    });

    if (sortBy.value === 'latest') {
        return [...list].sort((a, b) => b.id - a.id);
    }
    return list;
});

// Modal state for Exercise Create/Edit
const isModalOpen = ref(false);
const editingExercise = ref<ExerciseItem | null>(null);
const validationConfigText = ref('{\n  "checks": [\n    {\n      "type": "file_exists",\n      "path": "/home/student/latihan/test.txt"\n    }\n  ]\n}');

// Validation JSON syntax status
const jsonSyntaxStatus = computed<{ valid: boolean; error: string | null }>(() => {
    try {
        const parsed = JSON.parse(validationConfigText.value);
        if (typeof parsed !== 'object' || parsed === null) {
            return { valid: false, error: 'JSON harus berupa objek atau array.' };
        }
        return { valid: true, error: null };
    } catch (e: unknown) {
        return { valid: false, error: (e as Error).message };
    }
});

const exerciseForm = useForm({
    lesson_id: allLessons.value[0]?.id ?? 1,
    title: '',
    instruction: '',
    validation_type: 'system_state' as 'system_state' | 'output' | 'combined',
    validation_config: '',
    points: 100,
    order: 1,
});

const openCreateModal = () => {
    editingExercise.value = null;
    exerciseForm.reset();
    exerciseForm.clearErrors();
    exerciseForm.lesson_id = (filterLessonId.value !== '' ? filterLessonId.value : allLessons.value[0]?.id) ?? 1;
    exerciseForm.points = 100;
    exerciseForm.order = props.exercises.length > 0 ? Math.max(...props.exercises.map(e => e.order)) + 1 : 1;
    validationConfigText.value = JSON.stringify({
        checks: [
            { type: 'file_exists', path: '/home/student/latihan/hello.txt' },
        ],
    }, null, 2);
    isModalOpen.value = true;
};

const openEditModal = (ex: ExerciseItem) => {
    editingExercise.value = ex;
    exerciseForm.clearErrors();
    exerciseForm.lesson_id = ex.lesson_id;
    exerciseForm.title = ex.title;
    exerciseForm.instruction = ex.instruction;
    exerciseForm.validation_type = ex.validation_type;
    exerciseForm.points = ex.points;
    exerciseForm.order = ex.order;
    validationConfigText.value = JSON.stringify(ex.validation_config, null, 2);
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    editingExercise.value = null;
    exerciseForm.reset();
};

const applyJsonTemplate = (templateType: string) => {
    switch (templateType) {
        case 'system_state':
            exerciseForm.validation_type = 'system_state';
            validationConfigText.value = JSON.stringify({
                checks: [
                    { type: 'file_exists', path: '/home/student/latihan/test.txt' },
                    { type: 'file_permission', path: '/home/student/latihan/script.sh', mode: '755' },
                ],
            }, null, 2);
            break;
        case 'output':
            exerciseForm.validation_type = 'output';
            validationConfigText.value = JSON.stringify({
                checks: [
                    { type: 'command_output_equals', command: 'grep -c "linux" /home/student/kata.txt', text: '3' },
                ],
            }, null, 2);
            break;
        case 'combined':
            exerciseForm.validation_type = 'combined';
            validationConfigText.value = JSON.stringify({
                checks: [
                    { type: 'directory_exists', path: '/home/student/latihan/data' },
                    { type: 'file_exists', path: '/home/student/latihan/data/hasil.txt' },
                    { type: 'command_output_contains', command: 'cat /home/student/latihan/data/hasil.txt', text: 'sukses' },
                ],
            }, null, 2);
            break;
    }
};

const submitExercise = () => {
    exerciseForm.validation_config = validationConfigText.value;

    if (editingExercise.value) {
        exerciseForm.patch(`/admin/exercises/${editingExercise.value.id}`, {
            onSuccess: () => closeModal(),
        });
    } else {
        exerciseForm.post('/admin/exercises', {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteExercise = (ex: ExerciseItem) => {
    if (confirm(`Apakah Anda yakin ingin menghapus latihan "${ex.title}"? Seluruh petunjuk (hint) dan riwayat penilaian terkait akan ikut dihapus.`)) {
        router.delete(`/admin/exercises/${ex.id}`);
    }
};

// Hint Management Drawer / Modal (Fully Reactive)
const activeHintExerciseId = ref<number | null>(null);
const activeHintExercise = computed(() => {
    if (!activeHintExerciseId.value) return null;
    return props.exercises.find(e => e.id === activeHintExerciseId.value) || null;
});
const isHintModalOpen = ref(false);
const editingHint = ref<HintItem | null>(null);

const hintForm = useForm({
    content: '',
    order: 1,
});

const openHintsManager = (ex: ExerciseItem) => {
    activeHintExerciseId.value = ex.id;
    isHintModalOpen.value = true;
    editingHint.value = null;
    hintForm.reset();
    hintForm.order = (ex.hints?.length ? Math.max(...ex.hints.map(h => h.order)) + 1 : 1);
};

const closeHintsManager = () => {
    isHintModalOpen.value = false;
    activeHintExerciseId.value = null;
    editingHint.value = null;
    hintForm.reset();
};

const startEditHint = (hint: HintItem) => {
    editingHint.value = hint;
    hintForm.content = hint.content;
    hintForm.order = hint.order;
};

const cancelEditHint = () => {
    editingHint.value = null;
    hintForm.reset();
    if (activeHintExercise.value?.hints?.length) {
        hintForm.order = Math.max(...activeHintExercise.value.hints.map(h => h.order)) + 1;
    } else {
        hintForm.order = 1;
    }
};

const submitHint = () => {
    if (!activeHintExercise.value) return;

    if (editingHint.value) {
        hintForm.patch(`/admin/hints/${editingHint.value.id}`, {
            preserveScroll: true,
            onSuccess: () => cancelEditHint(),
        });
    } else {
        const targetExerciseId = activeHintExercise.value.id;
        hintForm.post(`/admin/exercises/${targetExerciseId}/hints`, {
            preserveScroll: true,
            onSuccess: () => {
                hintForm.reset();
                setTimeout(() => {
                    const currentEx = props.exercises.find(e => e.id === targetExerciseId);
                    if (currentEx?.hints?.length) {
                        hintForm.order = Math.max(...currentEx.hints.map(h => h.order)) + 1;
                    } else {
                        hintForm.order = 1;
                    }
                }, 50);
            },
        });
    }
};

const deleteHint = (hint: HintItem) => {
    if (confirm('Hapus petunjuk (hint) ini?')) {
        router.delete(`/admin/hints/${hint.id}`, {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <AdminLayout>
        <Head title="Kelola Latihan & Validator - Admin LinuxLab" />

        <div class="space-y-6">
            <!-- Header Halaman sesuai Wireframe 23_wf_admin -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-surface-card p-6 rounded-card border border-border shadow-sm">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <svg class="w-6 h-6 text-brand-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <h1 class="text-xl sm:text-2xl font-bold text-text-primary tracking-tight">
                            Manajemen Latihan Interaktif
                        </h1>
                    </div>
                    <p class="text-sm text-text-muted">
                        Kelola kurikulum praktik shell, parameter pengujian otomatis autograder, dan petunjuk lipat (FR-ADMIN-01).
                    </p>
                </div>

                <div class="flex items-center gap-3 shrink-0">
                    <button
                        type="button"
                        @click="openCreateModal"
                        class="px-4 py-2.5 text-sm font-bold rounded-button bg-brand-primary text-brand-primary-dark hover:bg-brand-primary-hover transition-all shadow-sm inline-flex items-center gap-2 active:scale-95"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>+ Buat Latihan Baru</span>
                    </button>
                </div>
            </div>

            <!-- Bento Stats Cepat Latihan -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-surface-card rounded-card p-4 border border-border flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-surface-elevated border border-border flex items-center justify-center text-brand-primary">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xl font-bold font-mono text-text-primary">{{ exercises.length }}</div>
                        <div class="text-xs text-text-muted">Total Latihan</div>
                    </div>
                </div>

                <div class="bg-surface-card rounded-card p-4 border border-border flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-surface-elevated border border-border flex items-center justify-center text-brand-secondary">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xl font-bold font-mono text-text-primary">
                            {{ exercises.filter(e => e.validation_type === 'system_state').length }}
                        </div>
                        <div class="text-xs text-text-muted">Tipe system_state</div>
                    </div>
                </div>

                <div class="bg-surface-card rounded-card p-4 border border-border flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-surface-elevated border border-border flex items-center justify-center text-brand-tertiary">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xl font-bold font-mono text-text-primary">
                            {{ exercises.filter(e => e.validation_type === 'output').length }}
                        </div>
                        <div class="text-xs text-text-muted">Tipe output (shell)</div>
                    </div>
                </div>

                <div class="bg-surface-card rounded-card p-4 border border-border flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-surface-elevated border border-border flex items-center justify-center text-purple-400">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xl font-bold font-mono text-text-primary">
                            {{ exercises.filter(e => e.validation_type === 'combined').length }}
                        </div>
                        <div class="text-xs text-text-muted">Tipe combined</div>
                    </div>
                </div>
            </div>

            <!-- Filter & Toolbar -->
            <div class="bg-surface-card rounded-card border border-border overflow-hidden">
                <div class="p-4 border-b border-border flex flex-col md:flex-row gap-3 items-center justify-between bg-surface-card/60">
                    <div class="relative w-full md:w-80">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-text-muted text-xs font-mono">🔍</span>
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari latihan berdasarkan judul / instruksi..."
                            class="w-full bg-surface-elevated pl-8 pr-3 py-1.5 rounded-input border border-border text-xs text-text-primary placeholder:text-text-muted focus:border-brand-primary outline-none"
                        />
                    </div>

                    <div class="flex items-center gap-2.5 w-full md:w-auto overflow-x-auto">
                        <select
                            v-model="filterModuleId"
                            @change="filterLessonId = ''"
                            class="bg-surface-elevated border border-border text-xs rounded-input px-3 py-1.5 text-text-primary focus:border-brand-primary outline-none"
                        >
                            <option value="">Semua Modul Induk</option>
                            <option v-for="m in modules" :key="m.id" :value="m.id">
                                {{ m.title }}
                            </option>
                        </select>

                        <select
                            v-model="filterLessonId"
                            class="bg-surface-elevated border border-border text-xs rounded-input px-3 py-1.5 text-text-primary focus:border-brand-primary outline-none"
                        >
                            <option value="">Semua Pelajaran</option>
                            <option v-for="l in availableLessons" :key="l.id" :value="l.id">
                                {{ l.title }}
                            </option>
                        </select>

                        <select
                            v-model="sortBy"
                            class="bg-surface-elevated border border-border text-xs rounded-input px-3 py-1.5 text-text-primary focus:border-brand-primary outline-none font-medium"
                        >
                            <option value="order">Urutan: Kurikulum (#Order)</option>
                            <option value="latest">Urutan: Terbaru Ditambahkan</option>
                        </select>
                    </div>
                </div>

                <!-- Table Responsive Container -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-surface-elevated/70 text-text-dim text-[11px] font-mono uppercase tracking-wider border-b border-border">
                            <tr>
                                <th class="py-3 px-4">KODE / URUTAN</th>
                                <th class="py-3 px-4">JUDUL LATIHAN</th>
                                <th class="py-3 px-4">PELAJARAN & MODUL</th>
                                <th class="py-3 px-4">TIPE VALIDASI</th>
                                <th class="py-3 px-4">XP / POIN</th>
                                <th class="py-3 px-4">HINT</th>
                                <th class="py-3 px-4 text-right">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr
                                v-for="ex in filteredExercises"
                                :key="ex.id"
                                class="hover:bg-surface-elevated/40 transition-colors"
                            >
                                <td class="py-3.5 px-4 font-mono text-brand-primary font-semibold text-xs">
                                    EX-{{ String(ex.id).padStart(3, '0') }}
                                    <span class="text-text-muted font-normal ml-1">#{{ ex.order }}</span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-semibold text-text-primary">{{ ex.title }}</div>
                                    <div class="text-xs text-text-dim line-clamp-1 max-w-sm">{{ ex.instruction }}</div>
                                </td>
                                <td class="py-3.5 px-4 text-xs">
                                    <div class="text-text-primary font-medium">{{ ex.lesson?.title || 'Pelajaran' }}</div>
                                    <div class="text-text-dim">{{ ex.lesson?.module?.title || 'Modul' }}</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-mono border"
                                        :class="{
                                            'bg-brand-secondary/15 text-brand-secondary border-brand-secondary/30': ex.validation_type === 'system_state',
                                            'bg-brand-tertiary/15 text-brand-tertiary border-brand-tertiary/30': ex.validation_type === 'output',
                                            'bg-purple-400/15 text-purple-400 border-purple-400/30': ex.validation_type === 'combined',
                                        }"
                                    >
                                        {{ ex.validation_type }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 font-mono text-xs text-brand-secondary font-bold">
                                    +{{ ex.points }} XP
                                </td>
                                <td class="py-3.5 px-4 text-xs font-mono">
                                    <button
                                        type="button"
                                        @click="openHintsManager(ex)"
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-surface-elevated hover:bg-surface-high border border-border text-brand-primary"
                                    >
                                        <span>💡 {{ ex.hints?.length ?? 0 }} Hint</span>
                                    </button>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button
                                            type="button"
                                            @click="openHintsManager(ex)"
                                            class="p-1.5 rounded hover:bg-surface-elevated text-brand-primary"
                                            title="Kelola Hint"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                            </svg>
                                        </button>
                                        <button
                                            type="button"
                                            @click="openEditModal(ex)"
                                            class="p-1.5 rounded hover:bg-surface-elevated text-text-muted hover:text-text-primary"
                                            title="Ubah Latihan"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button
                                            type="button"
                                            @click="deleteExercise(ex)"
                                            class="p-1.5 rounded hover:bg-surface-elevated text-brand-danger/70 hover:text-brand-danger"
                                            title="Hapus Latihan"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="filteredExercises.length === 0">
                                <td colspan="7" class="py-8 text-center text-xs text-text-muted">
                                    Tidak ada latihan yang cocok dengan kriteria pencarian.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- SLIDE-OVER / MODAL: FORMULIR TAMBAH / UBAH LATIHAN -->
            <div v-if="isModalOpen" class="fixed inset-0 z-50 overflow-hidden flex items-center justify-center p-4">
                <div class="fixed inset-0 bg-black/75 backdrop-blur-xs" @click="closeModal"></div>

                <div class="relative w-full max-w-3xl bg-surface-card border border-border rounded-card shadow-2xl p-6 z-10 space-y-5 max-h-[92vh] overflow-y-auto">
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between border-b border-border pb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-surface-elevated border border-brand-primary/30 flex items-center justify-center text-brand-primary font-mono text-sm font-bold">
                                &gt;_
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-text-primary">
                                    {{ editingExercise ? 'Ubah Konfigurasi Latihan' : 'Formulir Tambah Latihan Baru' }}
                                </h3>
                                <p class="text-xs text-text-muted">
                                    Konfigurasi materi praktik shell, instruksi, dan aturan autograder sandbox (FR-ADMIN-01).
                                </p>
                            </div>
                        </div>
                        <button
                            type="button"
                            @click="closeModal"
                            class="text-text-muted hover:text-text-primary p-1.5 rounded hover:bg-surface-elevated"
                        >
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form @submit.prevent="submitExercise" class="space-y-4">
                        <!-- Judul & Pelajaran Induk -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-text-primary mb-1">Judul Latihan</label>
                                <input
                                    v-model="exerciseForm.title"
                                    type="text"
                                    placeholder="Contoh: Membuat File dan Memeriksa Isi"
                                    class="w-full bg-surface-elevated px-3 py-2 rounded-input border border-border text-sm text-text-primary focus:border-brand-primary outline-none"
                                    required
                                />
                                <div v-if="exerciseForm.errors.title" class="text-xs text-brand-danger mt-1">{{ exerciseForm.errors.title }}</div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-text-primary mb-1">Pelajaran Tertaut</label>
                                <select
                                    v-model="exerciseForm.lesson_id"
                                    class="w-full bg-surface-elevated px-3 py-2 rounded-input border border-border text-sm text-text-primary focus:border-brand-primary outline-none"
                                    required
                                >
                                    <option v-for="l in allLessons" :key="l.id" :value="l.id">
                                        {{ l.title }}
                                    </option>
                                </select>
                                <div v-if="exerciseForm.errors.lesson_id" class="text-xs text-brand-danger mt-1">{{ exerciseForm.errors.lesson_id }}</div>
                            </div>
                        </div>

                        <!-- Poin & Urutan -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-text-primary mb-1">Reward Skor (Points / XP)</label>
                                <input
                                    v-model.number="exerciseForm.points"
                                    type="number"
                                    min="1"
                                    class="w-full bg-surface-elevated px-3 py-2 rounded-input border border-border text-sm font-mono text-brand-secondary font-bold focus:border-brand-primary outline-none"
                                    required
                                />
                                <div v-if="exerciseForm.errors.points" class="text-xs text-brand-danger mt-1">{{ exerciseForm.errors.points }}</div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-text-primary mb-1">Urutan Latihan</label>
                                <input
                                    v-model.number="exerciseForm.order"
                                    type="number"
                                    min="1"
                                    class="w-full bg-surface-elevated px-3 py-2 rounded-input border border-border text-sm font-mono text-text-primary focus:border-brand-primary outline-none"
                                    required
                                />
                                <div v-if="exerciseForm.errors.order" class="text-xs text-brand-danger mt-1">{{ exerciseForm.errors.order }}</div>
                            </div>
                        </div>

                        <!-- Instruksi Latihan -->
                        <div>
                            <label class="block text-xs font-semibold text-text-primary mb-1">Tujuan & Instruksi Latihan (Markdown)</label>
                            <textarea
                                v-model="exerciseForm.instruction"
                                rows="3"
                                placeholder="Jelaskan perintah atau tugas yang harus dikerjakan pengguna di terminal..."
                                class="w-full bg-surface-elevated px-3 py-2 rounded-input border border-border text-sm text-text-primary font-mono focus:border-brand-primary outline-none"
                                required
                            ></textarea>
                            <div v-if="exerciseForm.errors.instruction" class="text-xs text-brand-danger mt-1">{{ exerciseForm.errors.instruction }}</div>
                        </div>

                        <!-- Tipe Validasi & Template Preset -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="text-xs font-semibold text-text-primary">Metode Validasi Autograder (validation_type)</label>
                                <div class="flex items-center gap-1.5 text-[11px] font-mono">
                                    <span class="text-text-muted">Template Cepat:</span>
                                    <button
                                        type="button"
                                        @click="applyJsonTemplate('system_state')"
                                        class="px-1.5 py-0.5 rounded bg-surface-elevated hover:bg-surface-high border border-border text-brand-secondary"
                                    >
                                        file_state
                                    </button>
                                    <button
                                        type="button"
                                        @click="applyJsonTemplate('output')"
                                        class="px-1.5 py-0.5 rounded bg-surface-elevated hover:bg-surface-high border border-border text-brand-tertiary"
                                    >
                                        output
                                    </button>
                                    <button
                                        type="button"
                                        @click="applyJsonTemplate('combined')"
                                        class="px-1.5 py-0.5 rounded bg-surface-elevated hover:bg-surface-high border border-border text-purple-400"
                                    >
                                        combined
                                    </button>
                                </div>
                            </div>

                            <select
                                v-model="exerciseForm.validation_type"
                                class="w-full bg-surface-elevated px-3 py-2 rounded-input border border-border text-sm font-semibold text-brand-primary focus:border-brand-primary outline-none"
                                required
                            >
                                <option value="system_state">system_state (Pemeriksaan Kondisi Filesystem / Permission)</option>
                                <option value="output">output (Pemeriksaan Output Eksekusi Perintah)</option>
                                <option value="combined">combined (Kombinasi Kondisi Filesystem & Output Perintah)</option>
                            </select>
                            <div v-if="exerciseForm.errors.validation_type" class="text-xs text-brand-danger mt-1">{{ exerciseForm.errors.validation_type }}</div>
                        </div>

                        <!-- Editor validation_config Terminal Style (JSON) -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="text-xs font-semibold text-text-primary">
                                    Konfigurasi Validator JSON (validation_config)
                                </label>
                                <div class="flex items-center gap-2">
                                    <span
                                        class="text-[11px] font-mono px-2 py-0.5 rounded font-semibold inline-flex items-center gap-1"
                                        :class="jsonSyntaxStatus.valid ? 'bg-brand-primary/15 text-brand-primary border border-brand-primary/30' : 'bg-brand-danger/15 text-brand-danger border border-brand-danger/30'"
                                    >
                                        <span class="w-1.5 h-1.5 rounded-full" :class="jsonSyntaxStatus.valid ? 'bg-brand-primary' : 'bg-brand-danger'"></span>
                                        {{ jsonSyntaxStatus.valid ? 'Sintaks JSON Valid' : 'Format JSON Rusak' }}
                                    </span>
                                </div>
                            </div>

                            <!-- Terminal Frame -->
                            <div class="rounded-lg border border-border overflow-hidden bg-black/80 font-mono text-xs">
                                <div class="bg-surface-elevated px-3 py-1.5 border-b border-border flex items-center justify-between text-text-dim text-[11px]">
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-2.5 h-2.5 rounded-full bg-brand-danger"></span>
                                        <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                                        <span class="w-2.5 h-2.5 rounded-full bg-brand-primary"></span>
                                        <span class="ml-2 text-text-primary font-mono">validation_config.json</span>
                                    </div>
                                    <span class="text-text-dim text-[10px]">JSON Specification 2.0</span>
                                </div>

                                <textarea
                                    v-model="validationConfigText"
                                    rows="9"
                                    class="w-full bg-transparent p-3 text-text-primary font-mono text-xs leading-relaxed outline-none resize-y border-none focus:ring-0"
                                    spellcheck="false"
                                    placeholder="Masukkan JSON aturan pemeriksaan validator..."
                                ></textarea>
                            </div>

                            <!-- Syntax Error Feedback Warning -->
                            <div v-if="!jsonSyntaxStatus.valid" class="mt-2 p-2.5 rounded bg-brand-danger/10 border border-brand-danger/30 text-brand-danger-light text-xs font-mono flex items-center gap-2">
                                <svg class="w-4 h-4 shrink-0 text-brand-danger" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <span>Peringatan Format: {{ jsonSyntaxStatus.error }}</span>
                            </div>

                            <!-- Backend Validation Error (jika ditolak oleh server) -->
                            <div v-if="exerciseForm.errors.validation_config" class="mt-2 p-2.5 rounded bg-brand-danger/15 border border-brand-danger text-brand-danger font-semibold text-xs font-mono">
                                {{ exerciseForm.errors.validation_config }}
                            </div>

                            <p class="text-[11px] text-text-dim mt-1.5">
                                Catatan: Server akan menolak penyimpanan jika format JSON rusak atau tidak valid.
                            </p>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-border">
                            <button
                                type="button"
                                @click="closeModal"
                                class="px-4 py-2 text-xs font-semibold rounded-button bg-surface-elevated text-text-muted hover:text-text-primary border border-border"
                            >
                                Batal
                            </button>
                            <AppButton
                                type="submit"
                                variant="primary"
                                size="sm"
                                :loading="exerciseForm.processing"
                                :disabled="!jsonSyntaxStatus.valid"
                            >
                                {{ editingExercise ? 'Perbarui Latihan' : 'Simpan & Publikasikan' }}
                            </AppButton>
                        </div>
                    </form>
                </div>
            </div>

            <!-- MODAL: KELOLA HINT UNTUK LATIHAN -->
            <div v-if="isHintModalOpen && activeHintExercise" class="fixed inset-0 z-50 overflow-hidden flex items-center justify-center p-4">
                <div class="fixed inset-0 bg-black/75 backdrop-blur-xs" @click="closeHintsManager"></div>

                <div class="relative w-full max-w-2xl bg-surface-card border border-border rounded-card shadow-2xl p-6 z-10 space-y-5 max-h-[90vh] overflow-y-auto">
                    <!-- Header Hint Modal -->
                    <div class="flex items-center justify-between border-b border-border pb-3">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-sm">💡</span>
                                <h3 class="text-base font-bold text-text-primary">
                                    Kelola Petunjuk (Hint) Latihan
                                </h3>
                            </div>
                            <p class="text-xs text-text-muted mt-0.5">
                                Latihan: <strong class="text-brand-primary">{{ activeHintExercise.title }}</strong>
                            </p>
                        </div>
                        <button
                            type="button"
                            @click="closeHintsManager"
                            class="text-text-muted hover:text-text-primary p-1 rounded"
                        >
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Form Tambah / Ubah Hint -->
                    <form @submit.prevent="submitHint" class="bg-surface-elevated p-4 rounded-card border border-border space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-text-primary">
                                {{ editingHint ? 'Ubah Petunjuk' : '+ Tambah Petunjuk Baru' }}
                            </span>
                            <button
                                v-if="editingHint"
                                type="button"
                                @click="cancelEditHint"
                                class="text-xs text-text-dim hover:text-text-primary underline"
                            >
                                Batal Ubah
                            </button>
                        </div>

                        <div class="grid grid-cols-4 gap-3">
                            <div class="col-span-3">
                                <label class="block text-[11px] font-semibold text-text-muted mb-1">Isi Petunjuk (Hint Content)</label>
                                <textarea
                                    v-model="hintForm.content"
                                    rows="2"
                                    placeholder="Contoh: Gunakan opsi -l pada perintah ls untuk melihat izin berkas..."
                                    class="w-full bg-surface-card px-3 py-2 rounded-input border border-border text-xs text-text-primary focus:border-brand-primary outline-none"
                                    required
                                ></textarea>
                                <div v-if="hintForm.errors.content" class="text-xs text-brand-danger mt-1">{{ hintForm.errors.content }}</div>
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold text-text-muted mb-1">Urutan</label>
                                <input
                                    v-model.number="hintForm.order"
                                    type="number"
                                    min="1"
                                    class="w-full bg-surface-card px-3 py-2 rounded-input border border-border text-xs font-mono text-text-primary focus:border-brand-primary outline-none"
                                    required
                                />
                                <div v-if="hintForm.errors.order" class="text-xs text-brand-danger mt-1">{{ hintForm.errors.order }}</div>
                            </div>
                        </div>

                        <div class="flex justify-end pt-1">
                            <AppButton
                                type="submit"
                                variant="primary"
                                size="sm"
                                :loading="hintForm.processing"
                            >
                                {{ editingHint ? 'Perbarui Hint' : 'Tambah Hint' }}
                            </AppButton>
                        </div>
                    </form>

                    <!-- Daftar Hint yang Ada -->
                    <div class="space-y-2">
                        <h4 class="text-xs font-mono uppercase text-text-muted tracking-wider">
                            Daftar Petunjuk Tersedia ({{ activeHintExercise.hints?.length ?? 0 }})
                        </h4>

                        <div v-if="activeHintExercise.hints && activeHintExercise.hints.length > 0" class="space-y-2">
                            <div
                                v-for="hint in activeHintExercise.hints"
                                :key="hint.id"
                                class="p-3 rounded-card bg-surface-elevated border border-border flex items-start justify-between gap-3 text-xs"
                            >
                                <div class="flex items-start gap-2.5">
                                    <span class="px-1.5 py-0.5 rounded bg-surface-card border border-border font-mono text-[11px] text-brand-primary font-bold">
                                        #{{ hint.order }}
                                    </span>
                                    <p class="text-text-primary leading-relaxed">{{ hint.content }}</p>
                                </div>

                                <div class="flex items-center gap-1 shrink-0">
                                    <button
                                        type="button"
                                        @click="startEditHint(hint)"
                                        class="p-1 rounded hover:bg-surface-card text-text-muted hover:text-text-primary"
                                        title="Ubah"
                                    >
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button
                                        type="button"
                                        @click="deleteHint(hint)"
                                        class="p-1 rounded hover:bg-surface-card text-brand-danger/70 hover:text-brand-danger"
                                        title="Hapus"
                                    >
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-6 text-xs text-text-muted bg-surface-elevated/40 rounded-card border border-border">
                            Latihan ini belum memiliki petunjuk lipat (hint).
                        </div>
                    </div>

                    <div class="flex justify-end pt-3 border-t border-border">
                        <button
                            type="button"
                            @click="closeHintsManager"
                            class="px-4 py-2 text-xs font-semibold rounded-button bg-surface-elevated text-text-primary border border-border hover:bg-surface-high"
                        >
                            Selesai
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
