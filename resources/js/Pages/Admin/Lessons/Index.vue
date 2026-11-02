<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AppCard from '@/Components/AppCard.vue';
import AppButton from '@/Components/AppButton.vue';

interface ModuleOption {
    id: number;
    title: string;
    slug: string;
    order: number;
}

interface LessonItem {
    id: number;
    module_id: number;
    title: string;
    slug: string;
    content: string;
    example_commands: string | null;
    cheat_sheet: string | null;
    order: number;
    exercises_count: number;
    module?: { id: number; title: string; slug: string };
}

interface Props {
    lessons: LessonItem[];
    modules: ModuleOption[];
    selectedModuleId: number | null;
}

const props = defineProps<Props>();

const filterModuleId = ref<number | ''>(props.selectedModuleId ?? '');
const sortBy = ref<'order' | 'latest'>('order');

const displayedLessons = computed(() => {
    if (sortBy.value === 'latest') {
        return [...props.lessons].sort((a, b) => b.id - a.id);
    }
    return [...props.lessons].sort((a, b) => {
        if (a.module_id !== b.module_id) return a.module_id - b.module_id;
        return a.order - b.order;
    });
});

const isModalOpen = ref(false);
const editingLesson = ref<LessonItem | null>(null);

const form = useForm({
    module_id: props.modules[0]?.id ?? 1,
    title: '',
    slug: '',
    content: '',
    example_commands: '',
    cheat_sheet: '',
    order: 1,
});

watch(filterModuleId, (newId) => {
    router.get(
        '/admin/lessons',
        newId ? { module_id: newId } : {},
        { preserveState: true, replace: true }
    );
});

const openCreateModal = () => {
    editingLesson.value = null;
    form.reset();
    form.clearErrors();
    form.module_id = (filterModuleId.value !== '' ? filterModuleId.value : props.modules[0]?.id) ?? 1;
    form.order = (props.lessons.length > 0 ? Math.max(...props.lessons.map(l => l.order)) + 1 : 1);
    isModalOpen.value = true;
};

const openEditModal = (lesson: LessonItem) => {
    editingLesson.value = lesson;
    form.clearErrors();
    form.module_id = lesson.module_id;
    form.title = lesson.title;
    form.slug = lesson.slug;
    form.content = lesson.content;
    form.example_commands = lesson.example_commands || '';
    form.cheat_sheet = lesson.cheat_sheet || '';
    form.order = lesson.order;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    editingLesson.value = null;
    form.reset();
};

const submitForm = () => {
    if (editingLesson.value) {
        form.patch(`/admin/lessons/${editingLesson.value.slug}`, {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post('/admin/lessons', {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteLesson = (lesson: LessonItem) => {
    if (confirm(`Apakah Anda yakin ingin menghapus pelajaran "${lesson.title}"? Seluruh latihan dan hint di dalamnya akan ikut terhapus.`)) {
        router.delete(`/admin/lessons/${lesson.slug}`);
    }
};
</script>

<template>
    <AdminLayout>
        <Head title="Kelola Pelajaran - Admin LinuxLab" />

        <div class="space-y-6">
            <!-- Header Halaman -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-surface-card p-6 rounded-card border border-border">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <svg class="w-6 h-6 text-brand-primary-light" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        <h1 class="text-xl sm:text-2xl font-bold text-text-primary tracking-tight">
                            Manajemen Pelajaran & Materi
                        </h1>
                    </div>
                    <p class="text-sm text-text-muted">
                        Kelola unit materi, contoh perintah, dan cheat sheet pada setiap modul (FR-ADMIN-01).
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <select
                        v-model="filterModuleId"
                        class="bg-surface-elevated border border-border text-xs rounded-input px-3 py-2 text-text-primary focus:border-brand-primary outline-none"
                    >
                        <option value="">Semua Modul Induk</option>
                        <option v-for="m in modules" :key="m.id" :value="m.id">
                            {{ m.title }}
                        </option>
                    </select>

                    <select
                        v-model="sortBy"
                        class="bg-surface-elevated border border-border text-xs rounded-input px-3 py-2 text-text-primary focus:border-brand-primary outline-none"
                    >
                        <option value="order">Urutan: Kurikulum (#Order)</option>
                        <option value="latest">Urutan: Terbaru Ditambahkan</option>
                    </select>

                    <button
                        type="button"
                        @click="openCreateModal"
                        class="px-4 py-2.5 text-sm font-semibold rounded-button bg-brand-primary text-white hover:bg-brand-primary-hover transition-colors shadow-sm inline-flex items-center gap-2"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>+ Tambah Pelajaran Baru</span>
                    </button>
                </div>
            </div>

            <!-- Tabel Pelajaran -->
            <AppCard>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-surface-high text-text-dim text-[11px] font-mono uppercase tracking-wider border-b border-border">
                            <tr>
                                <th class="py-3 px-4">Urutan</th>
                                <th class="py-3 px-4">Modul Induk</th>
                                <th class="py-3 px-4">Judul Pelajaran</th>
                                <th class="py-3 px-4">Slug</th>
                                <th class="py-3 px-4">Latihan</th>
                                <th class="py-3 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr
                                v-for="lesson in displayedLessons"
                                :key="lesson.id"
                                class="hover:bg-surface-elevated/40 transition-colors"
                            >
                                <td class="py-3.5 px-4 font-mono text-text-dim font-bold">#{{ lesson.order }}</td>
                                <td class="py-3.5 px-4 text-xs text-text-muted">
                                    <span class="px-2 py-0.5 rounded bg-surface-elevated border border-border text-text-primary">
                                        {{ lesson.module?.title || 'Modul' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-semibold text-text-primary">{{ lesson.title }}</div>
                                    <div class="text-xs text-text-dim line-clamp-1 max-w-sm">{{ lesson.content }}</div>
                                </td>
                                <td class="py-3.5 px-4 font-mono text-xs text-brand-primary-light">{{ lesson.slug }}</td>
                                <td class="py-3.5 px-4 text-xs text-text-muted">
                                    <span class="font-mono text-text-primary">{{ lesson.exercises_count }}</span> latihan
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link
                                            :href="`/admin/exercises?lesson_id=${lesson.id}`"
                                            class="px-2.5 py-1 text-xs font-mono rounded bg-surface-elevated hover:bg-surface-high text-brand-tertiary-light border border-border"
                                            title="Kelola Latihan"
                                        >
                                            Latihan →
                                        </Link>
                                        <button
                                            type="button"
                                            @click="openEditModal(lesson)"
                                            class="p-1.5 rounded hover:bg-surface-elevated text-text-muted hover:text-text-primary"
                                            title="Ubah Pelajaran"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button
                                            type="button"
                                            @click="deleteLesson(lesson)"
                                            class="p-1.5 rounded hover:bg-surface-elevated text-brand-danger-light/70 hover:text-brand-danger-light"
                                            title="Hapus Pelajaran"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="lessons.length === 0">
                                <td colspan="6" class="py-8 text-center text-xs text-text-muted">
                                    Belum ada pelajaran pada modul ini. Klik "+ Tambah Pelajaran Baru" untuk menambahkan materi.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </AppCard>

            <!-- Modal Slide-over: Tambah / Ubah Pelajaran -->
            <div v-if="isModalOpen" class="fixed inset-0 z-50 overflow-hidden flex items-center justify-center p-4">
                <div class="fixed inset-0 bg-black/70 backdrop-blur-xs" @click="closeModal"></div>

                <div class="relative w-full max-w-2xl bg-surface-card border border-border rounded-card shadow-2xl p-6 z-10 space-y-4 max-h-[90vh] overflow-y-auto">
                    <div class="flex items-center justify-between border-b border-border pb-3">
                        <h3 class="text-lg font-bold text-text-primary">
                            {{ editingLesson ? 'Ubah Pelajaran' : 'Tambah Pelajaran Baru' }}
                        </h3>
                        <button
                            type="button"
                            @click="closeModal"
                            class="text-text-muted hover:text-text-primary p-1 rounded"
                        >
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form @submit.prevent="submitForm" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-text-primary mb-1">Modul Induk</label>
                                <select
                                    v-model="form.module_id"
                                    class="w-full bg-surface-elevated px-3 py-2 rounded-input border border-border text-sm text-text-primary focus:border-brand-primary outline-none"
                                    required
                                >
                                    <option v-for="m in modules" :key="m.id" :value="m.id">
                                        {{ m.title }}
                                    </option>
                                </select>
                                <div v-if="form.errors.module_id" class="text-xs text-brand-danger-light mt-1">{{ form.errors.module_id }}</div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-text-primary mb-1">Urutan Pelajaran</label>
                                <input
                                    v-model.number="form.order"
                                    type="number"
                                    min="1"
                                    class="w-full bg-surface-elevated px-3 py-2 rounded-input border border-border text-sm font-mono text-text-primary focus:border-brand-primary outline-none"
                                    required
                                />
                                <p class="text-[11px] text-text-dim mt-1">💡 Isi 1 jika ingin pelajaran ini menjadi materi pertama di modulnya.</p>
                                <div v-if="form.errors.order" class="text-xs text-brand-danger-light mt-1">{{ form.errors.order }}</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-text-primary mb-1">Judul Pelajaran</label>
                                <input
                                    v-model="form.title"
                                    type="text"
                                    placeholder="Contoh: Navigasi Direktori"
                                    class="w-full bg-surface-elevated px-3 py-2 rounded-input border border-border text-sm text-text-primary focus:border-brand-primary outline-none"
                                    required
                                />
                                <div v-if="form.errors.title" class="text-xs text-brand-danger-light mt-1">{{ form.errors.title }}</div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-text-primary mb-1">Slug URL (Opsional, otomatis dari judul)</label>
                                <input
                                    v-model="form.slug"
                                    type="text"
                                    placeholder="contoh: navigasi-direktori"
                                    class="w-full bg-surface-elevated px-3 py-2 rounded-input border border-border font-mono text-sm text-brand-primary-light focus:border-brand-primary outline-none"
                                />
                                <div v-if="form.errors.slug" class="text-xs text-brand-danger-light mt-1">{{ form.errors.slug }}</div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-text-primary mb-1">Konten Materi (Markdown / Teks Panjang)</label>
                            <textarea
                                v-model="form.content"
                                rows="5"
                                placeholder="Tuliskan materi pembelajaran Linux lengkap di sini..."
                                class="w-full bg-surface-elevated px-3 py-2 rounded-input border border-border text-sm text-text-primary focus:border-brand-primary outline-none font-mono"
                                required
                            ></textarea>
                            <div v-if="form.errors.content" class="text-xs text-brand-danger-light mt-1">{{ form.errors.content }}</div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-text-primary mb-1">Contoh Perintah (example_commands)</label>
                                <textarea
                                    v-model="form.example_commands"
                                    rows="3"
                                    placeholder="Contoh: ls -la&#10;pwd&#10;cd /var/log"
                                    class="w-full bg-surface-elevated px-3 py-2 rounded-input border border-border text-xs font-mono text-text-primary focus:border-brand-primary outline-none"
                                ></textarea>
                                <div v-if="form.errors.example_commands" class="text-xs text-brand-danger-light mt-1">{{ form.errors.example_commands }}</div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-text-primary mb-1">Lembar Contekan (cheat_sheet)</label>
                                <textarea
                                    v-model="form.cheat_sheet"
                                    rows="3"
                                    placeholder="Rangkuman cepat opsi perintah Linux..."
                                    class="w-full bg-surface-elevated px-3 py-2 rounded-input border border-border text-xs font-mono text-text-primary focus:border-brand-primary outline-none"
                                ></textarea>
                                <div v-if="form.errors.cheat_sheet" class="text-xs text-brand-danger-light mt-1">{{ form.errors.cheat_sheet }}</div>
                            </div>
                        </div>

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
                                :loading="form.processing"
                            >
                                {{ editingLesson ? 'Perbarui Pelajaran' : 'Simpan Pelajaran' }}
                            </AppButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
