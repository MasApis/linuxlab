<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AppCard from '@/Components/AppCard.vue';
import AppButton from '@/Components/AppButton.vue';
import BadgeIcon from '@/Components/BadgeIcon.vue';

interface BadgeItem {
    id: number;
    name: string;
    description: string;
    icon: string;
    criteria_type: string;
    criteria_value: Record<string, unknown> | null;
    user_badges_count?: number;
}

interface Props {
    badges: BadgeItem[];
}

const props = defineProps<Props>();

const isModalOpen = ref(false);
const editingBadge = ref<BadgeItem | null>(null);
const criteriaValueText = ref('');

const criteriaOptions = [
    { value: 'first_exercise', label: 'first_exercise (Menyelesaikan latihan pertama)', placeholder: '' },
    { value: 'lesson_completed', label: 'lesson_completed (Menyelesaikan satu pelajaran)', placeholder: '{"lesson_id": 1}' },
    { value: 'module_completed', label: 'module_completed (Menyelesaikan satu modul 100%)', placeholder: '{"module_id": 1}' },
    { value: 'exercises_count', label: 'exercises_count (Menyelesaikan n latihan)', placeholder: '{"count": 10}' },
    { value: 'first_question', label: 'first_question (Pertanyaan pertama di forum)', placeholder: '' },
    { value: 'best_answer', label: 'best_answer (Mendapatkan n jawaban terbaik)', placeholder: '{"count": 1}' },
];

const badgeForm = useForm({
    name: '',
    description: '',
    icon: '🏆',
    criteria_type: 'first_exercise',
    criteria_value: '',
});

const openCreateModal = () => {
    editingBadge.value = null;
    badgeForm.reset();
    badgeForm.clearErrors();
    badgeForm.icon = '🏆';
    badgeForm.criteria_type = 'first_exercise';
    criteriaValueText.value = '';
    isModalOpen.value = true;
};

const openEditModal = (badge: BadgeItem) => {
    editingBadge.value = badge;
    badgeForm.clearErrors();
    badgeForm.name = badge.name;
    badgeForm.description = badge.description;
    badgeForm.icon = badge.icon;
    badgeForm.criteria_type = badge.criteria_type;
    criteriaValueText.value = badge.criteria_value ? JSON.stringify(badge.criteria_value, null, 2) : '';
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    editingBadge.value = null;
    badgeForm.reset();
};

const submitBadge = () => {
    badgeForm.criteria_value = criteriaValueText.value;

    if (editingBadge.value) {
        badgeForm.patch(`/admin/badges/${editingBadge.value.id}`, {
            onSuccess: () => closeModal(),
        });
    } else {
        badgeForm.post('/admin/badges', {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteBadge = (badge: BadgeItem) => {
    if (confirm(`Apakah Anda yakin ingin menghapus lencana "${badge.name}"? Lencana yang sudah diraih pengguna akan dihapus.`)) {
        router.delete(`/admin/badges/${badge.id}`);
    }
};
</script>

<template>
    <AdminLayout>
        <Head title="Kelola Master Lencana - Admin LinuxLab" />

        <div class="space-y-6">
            <!-- Header Halaman -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-surface-card p-6 rounded-card border border-border">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <svg class="w-6 h-6 text-brand-primary-light" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                        </svg>
                        <h1 class="text-xl sm:text-2xl font-bold text-text-primary tracking-tight">
                            Manajemen Master Lencana
                        </h1>
                    </div>
                    <p class="text-sm text-text-muted">
                        Kelola pencapaian, kriteria otomatisasi, dan ikon lencana penghargaan belajar (FR-ADMIN-02).
                    </p>
                </div>

                <button
                    type="button"
                    @click="openCreateModal"
                    class="px-4 py-2.5 text-sm font-semibold rounded-button bg-brand-primary text-white hover:bg-brand-primary-hover transition-colors shadow-sm inline-flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>+ Buat Lencana Baru</span>
                </button>
            </div>

            <!-- Tabel Lencana -->
            <AppCard>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-surface-high text-text-dim text-[11px] font-mono uppercase tracking-wider border-b border-border">
                            <tr>
                                <th class="py-3 px-4">IKON</th>
                                <th class="py-3 px-4">NAMA LENCANA</th>
                                <th class="py-3 px-4">TIPE KRITERIA</th>
                                <th class="py-3 px-4">NILAI KRITERIA (JSON)</th>
                                <th class="py-3 px-4">PENERIMA</th>
                                <th class="py-3 px-4 text-right">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr
                                v-for="badge in badges"
                                :key="badge.id"
                                class="hover:bg-surface-elevated/40 transition-colors"
                            >
                                <td class="py-3.5 px-4">
                                    <div class="w-10 h-10 rounded-lg bg-surface-elevated border border-border flex items-center justify-center shrink-0">
                                        <BadgeIcon :icon="badge.icon" size="lg" />
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-semibold text-text-primary">{{ badge.name }}</div>
                                    <div class="text-xs text-text-muted max-w-sm">{{ badge.description }}</div>
                                </td>
                                <td class="py-3.5 px-4 font-mono text-xs">
                                    <span class="px-2 py-0.5 rounded bg-brand-primary/10 text-brand-primary-light border border-brand-primary/30">
                                        {{ badge.criteria_type }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 font-mono text-xs text-text-dim">
                                    <span v-if="badge.criteria_value" class="px-1.5 py-0.5 rounded bg-surface-elevated border border-border">
                                        {{ JSON.stringify(badge.criteria_value) }}
                                    </span>
                                    <span v-else class="text-text-muted italic">—</span>
                                </td>
                                <td class="py-3.5 px-4 text-xs font-mono text-text-muted">
                                    <span class="text-text-primary font-bold">{{ badge.user_badges_count ?? 0 }}</span> siswa
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button
                                            type="button"
                                            @click="openEditModal(badge)"
                                            class="p-1.5 rounded hover:bg-surface-elevated text-text-muted hover:text-text-primary"
                                            title="Ubah Lencana"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button
                                            type="button"
                                            @click="deleteBadge(badge)"
                                            class="p-1.5 rounded hover:bg-surface-elevated text-brand-danger-light/70 hover:text-brand-danger-light"
                                            title="Hapus Lencana"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="badges.length === 0">
                                <td colspan="6" class="py-8 text-center text-xs text-text-muted">
                                    Belum ada lencana yang dibuat.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </AppCard>

            <!-- Modal: Tambah / Ubah Lencana -->
            <div v-if="isModalOpen" class="fixed inset-0 z-50 overflow-hidden flex items-center justify-center p-4">
                <div class="fixed inset-0 bg-black/75 backdrop-blur-xs" @click="closeModal"></div>

                <div class="relative w-full max-w-xl bg-surface-card border border-border rounded-card shadow-2xl p-6 z-10 space-y-4 max-h-[90vh] overflow-y-auto">
                    <div class="flex items-center justify-between border-b border-border pb-3">
                        <h3 class="text-lg font-bold text-text-primary">
                            {{ editingBadge ? 'Ubah Lencana' : 'Tambah Lencana Baru' }}
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

                    <form @submit.prevent="submitBadge" class="space-y-4">
                        <div class="grid grid-cols-4 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-text-primary mb-1">Ikon / Simbol</label>
                                <div class="flex items-center gap-2">
                                    <div class="w-10 h-10 rounded-input bg-surface-card border border-border flex items-center justify-center shrink-0">
                                        <BadgeIcon :icon="badgeForm.icon" size="md" />
                                    </div>
                                    <input
                                        v-model="badgeForm.icon"
                                        type="text"
                                        placeholder="🏆 / rocket_launch"
                                        class="w-full bg-surface-elevated px-2 py-2 rounded-input border border-border text-center text-xs font-mono text-text-primary focus:border-brand-primary outline-none"
                                        required
                                    />
                                </div>
                                <div v-if="badgeForm.errors.icon" class="text-xs text-brand-danger-light mt-1">{{ badgeForm.errors.icon }}</div>
                                <div class="flex items-center gap-1 mt-1.5 flex-wrap">
                                    <button
                                        v-for="sym in ['🏆', '🚀', '📁', '🛡️', '💻', '🎓', '💡', '⭐']"
                                        :key="sym"
                                        type="button"
                                        @click="badgeForm.icon = sym"
                                        class="w-6 h-6 rounded bg-surface-elevated hover:bg-surface-high border border-border text-xs flex items-center justify-center"
                                        :title="sym"
                                    >
                                        {{ sym }}
                                    </button>
                                </div>
                            </div>

                            <div class="col-span-3">
                                <label class="block text-xs font-semibold text-text-primary mb-1">Nama Lencana</label>
                                <input
                                    v-model="badgeForm.name"
                                    type="text"
                                    placeholder="Contoh: Penjelajah File System"
                                    class="w-full bg-surface-elevated px-3 py-2 rounded-input border border-border text-sm text-text-primary focus:border-brand-primary outline-none"
                                    required
                                />
                                <div v-if="badgeForm.errors.name" class="text-xs text-brand-danger-light mt-1">{{ badgeForm.errors.name }}</div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-text-primary mb-1">Deskripsi Pencapaian</label>
                            <textarea
                                v-model="badgeForm.description"
                                rows="2"
                                placeholder="Jelaskan cara siswa mendapatkan lencana ini..."
                                class="w-full bg-surface-elevated px-3 py-2 rounded-input border border-border text-sm text-text-primary focus:border-brand-primary outline-none"
                                required
                            ></textarea>
                            <div v-if="badgeForm.errors.description" class="text-xs text-brand-danger-light mt-1">{{ badgeForm.errors.description }}</div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-text-primary mb-1">Tipe Kriteria Evaluasi Otomatis</label>
                            <select
                                v-model="badgeForm.criteria_type"
                                class="w-full bg-surface-elevated px-3 py-2 rounded-input border border-border text-sm font-mono text-brand-primary-light focus:border-brand-primary outline-none"
                                required
                            >
                                <option v-for="opt in criteriaOptions" :key="opt.value" :value="opt.value">
                                    {{ opt.label }}
                                </option>
                            </select>
                            <div v-if="badgeForm.errors.criteria_type" class="text-xs text-brand-danger-light mt-1">{{ badgeForm.errors.criteria_type }}</div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-text-primary mb-1">Nilai Kriteria (JSON, kosongkan jika tidak diperlukan)</label>
                            <input
                                v-model="criteriaValueText"
                                type="text"
                                placeholder='Contoh: {"module_id": 1} atau {"count": 10}'
                                class="w-full bg-surface-elevated px-3 py-2 rounded-input border border-border font-mono text-xs text-text-primary focus:border-brand-primary outline-none"
                            />
                            <div v-if="badgeForm.errors.criteria_value" class="text-xs text-brand-danger-light mt-1">{{ badgeForm.errors.criteria_value }}</div>
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
                                :loading="badgeForm.processing"
                            >
                                {{ editingBadge ? 'Perbarui Lencana' : 'Simpan Lencana' }}
                            </AppButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
