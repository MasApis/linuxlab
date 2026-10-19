<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AppCard from '@/Components/AppCard.vue';
import AppButton from '@/Components/AppButton.vue';

interface ModuleItem {
    id: number;
    title: string;
    slug: string;
    description: string;
    order: number;
    is_published: boolean;
    lessons_count: number;
    exercises_count: number;
}

interface Props {
    modules: ModuleItem[];
}

const props = defineProps<Props>();

const sortBy = ref<'order' | 'latest'>('order');

const displayedModules = computed(() => {
    if (sortBy.value === 'latest') {
        return [...props.modules].sort((a, b) => b.id - a.id);
    }
    return [...props.modules].sort((a, b) => a.order - b.order);
});

const isModalOpen = ref(false);
const editingModule = ref<ModuleItem | null>(null);

const form = useForm({
    title: '',
    slug: '',
    description: '',
    order: 1,
    is_published: true,
});

const openCreateModal = () => {
    editingModule.value = null;
    form.reset();
    form.clearErrors();
    form.order = (props.modules.length > 0 ? Math.max(...props.modules.map(m => m.order)) + 1 : 1);
    form.is_published = true;
    isModalOpen.value = true;
};

const openEditModal = (mod: ModuleItem) => {
    editingModule.value = mod;
    form.clearErrors();
    form.title = mod.title;
    form.slug = mod.slug;
    form.description = mod.description;
    form.order = mod.order;
    form.is_published = Boolean(mod.is_published);
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    editingModule.value = null;
    form.reset();
};

const submitForm = () => {
    if (editingModule.value) {
        form.patch(`/admin/modules/${editingModule.value.slug}`, {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post('/admin/modules', {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteModule = (mod: ModuleItem) => {
    if (confirm(`Apakah Anda yakin ingin menghapus modul "${mod.title}"? Seluruh pelajaran, latihan, dan hint di dalamnya akan ikut terhapus.`)) {
        router.delete(`/admin/modules/${mod.slug}`);
    }
};
</script>

<template>
    <AdminLayout>
        <Head title="Kelola Modul - Admin LinuxLab" />

        <div class="space-y-6">
            <!-- Header Halaman -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-surface-card p-6 rounded-card border border-border">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <svg class="w-6 h-6 text-brand-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        <h1 class="text-xl sm:text-2xl font-bold text-text-primary tracking-tight">
                            Manajemen Modul Belajar
                        </h1>
                    </div>
                    <p class="text-sm text-text-muted">
                        Kelola kurikulum tingkat tema besar pembelajaran LinuxLab (FR-ADMIN-01).
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <select
                        v-model="sortBy"
                        class="bg-surface-elevated border border-border text-xs rounded-input px-3 py-2 text-text-primary focus:border-brand-primary outline-none font-medium"
                    >
                        <option value="order">Urutan: Kurikulum (#Order)</option>
                        <option value="latest">Urutan: Terbaru Ditambahkan</option>
                    </select>

                    <button
                        type="button"
                        @click="openCreateModal"
                        class="px-4 py-2.5 text-sm font-semibold rounded-button bg-brand-primary text-brand-primary-dark hover:bg-brand-primary-hover transition-colors shadow-sm inline-flex items-center gap-2"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>+ Tambah Modul Baru</span>
                    </button>
                </div>
            </div>

            <!-- Tabel Modul -->
            <AppCard>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-surface-elevated/70 text-text-dim text-[11px] font-mono uppercase tracking-wider border-b border-border">
                            <tr>
                                <th class="py-3 px-4">Urutan</th>
                                <th class="py-3 px-4">Judul Modul</th>
                                <th class="py-3 px-4">Slug</th>
                                <th class="py-3 px-4">Pelajaran</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr
                                v-for="mod in displayedModules"
                                :key="mod.id"
                                class="hover:bg-surface-elevated/40 transition-colors"
                            >
                                <td class="py-3.5 px-4 font-mono text-text-dim font-bold">#{{ mod.order }}</td>
                                <td class="py-3.5 px-4">
                                    <div class="font-semibold text-text-primary">{{ mod.title }}</div>
                                    <div class="text-xs text-text-muted line-clamp-1 max-w-md">{{ mod.description }}</div>
                                </td>
                                <td class="py-3.5 px-4 font-mono text-xs text-brand-primary">{{ mod.slug }}</td>
                                <td class="py-3.5 px-4 text-xs text-text-muted">
                                    <span class="font-mono text-text-primary">{{ mod.lessons_count }}</span> pelajaran
                                </td>
                                <td class="py-3.5 px-4">
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-mono font-semibold"
                                        :class="mod.is_published ? 'bg-brand-primary/10 text-brand-primary border border-brand-primary/30' : 'bg-surface-elevated text-text-dim border border-border'"
                                    >
                                        {{ mod.is_published ? 'Diterbitkan' : 'Draf' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link
                                            :href="`/admin/lessons?module_id=${mod.id}`"
                                            class="px-2.5 py-1 text-xs font-mono rounded bg-surface-elevated hover:bg-surface-high text-brand-tertiary border border-border"
                                            title="Kelola Pelajaran"
                                        >
                                            Pelajaran →
                                        </Link>
                                        <button
                                            type="button"
                                            @click="openEditModal(mod)"
                                            class="p-1.5 rounded hover:bg-surface-elevated text-text-muted hover:text-text-primary"
                                            title="Ubah Modul"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button
                                            type="button"
                                            @click="deleteModule(mod)"
                                            class="p-1.5 rounded hover:bg-surface-elevated text-brand-danger/70 hover:text-brand-danger"
                                            title="Hapus Modul"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="modules.length === 0">
                                <td colspan="6" class="py-8 text-center text-xs text-text-muted">
                                    Belum ada modul. Klik tombol "+ Tambah Modul Baru" untuk membuat modul pembelajaran pertama.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </AppCard>

            <!-- Modal Slide-over: Tambah / Ubah Modul -->
            <div v-if="isModalOpen" class="fixed inset-0 z-50 overflow-hidden flex items-center justify-center p-4">
                <div class="fixed inset-0 bg-black/70 backdrop-blur-xs" @click="closeModal"></div>

                <div class="relative w-full max-w-xl bg-surface-card border border-border rounded-card shadow-2xl p-6 z-10 space-y-5">
                    <div class="flex items-center justify-between border-b border-border pb-4">
                        <h3 class="text-lg font-bold text-text-primary">
                            {{ editingModule ? 'Ubah Modul' : 'Tambah Modul Baru' }}
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
                        <div>
                            <label class="block text-xs font-semibold text-text-primary mb-1">Judul Modul</label>
                            <input
                                v-model="form.title"
                                type="text"
                                placeholder="Contoh: Modul 1 — File System"
                                class="w-full bg-surface-elevated px-3 py-2 rounded-input border border-border text-sm text-text-primary focus:border-brand-primary outline-none"
                                required
                            />
                            <div v-if="form.errors.title" class="text-xs text-brand-danger mt-1">{{ form.errors.title }}</div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-text-primary mb-1">Slug URL (Opsional, otomatis dari judul)</label>
                            <input
                                v-model="form.slug"
                                type="text"
                                placeholder="contoh: file-system"
                                class="w-full bg-surface-elevated px-3 py-2 rounded-input border border-border font-mono text-sm text-brand-primary focus:border-brand-primary outline-none"
                            />
                            <div v-if="form.errors.slug" class="text-xs text-brand-danger mt-1">{{ form.errors.slug }}</div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-text-primary mb-1">Deskripsi Modul</label>
                            <textarea
                                v-model="form.description"
                                rows="3"
                                placeholder="Penjelasan ringkas mengenai cakupan topik materi pada modul ini..."
                                class="w-full bg-surface-elevated px-3 py-2 rounded-input border border-border text-sm text-text-primary focus:border-brand-primary outline-none"
                                required
                            ></textarea>
                            <div v-if="form.errors.description" class="text-xs text-brand-danger mt-1">{{ form.errors.description }}</div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-text-primary mb-1">Urutan Tampilan</label>
                                <input
                                    v-model.number="form.order"
                                    type="number"
                                    min="1"
                                    class="w-full bg-surface-elevated px-3 py-2 rounded-input border border-border text-sm font-mono text-text-primary focus:border-brand-primary outline-none"
                                    required
                                />
                                <p class="text-[11px] text-text-dim mt-1">💡 Isi 1 jika ingin modul ini tampil pertama di kurikulum siswa.</p>
                                <div v-if="form.errors.order" class="text-xs text-brand-danger mt-1">{{ form.errors.order }}</div>
                            </div>

                            <div class="flex items-center pt-6">
                                <label class="flex items-center gap-2 cursor-pointer select-none">
                                    <input
                                        v-model="form.is_published"
                                        type="checkbox"
                                        class="rounded bg-surface-elevated border-border text-brand-primary focus:ring-brand-primary"
                                    />
                                    <span class="text-xs font-semibold text-text-primary">Terbitkan Modul</span>
                                </label>
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
                                {{ editingModule ? 'Perbarui Modul' : 'Simpan Modul' }}
                            </AppButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
