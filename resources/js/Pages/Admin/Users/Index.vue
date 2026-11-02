<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AppCard from '@/Components/AppCard.vue';

interface UserItem {
    id: number;
    name: string;
    username: string;
    email: string;
    role: 'user' | 'admin';
    is_active: boolean;
    created_at: string;
    exercise_attempts_count?: number;
    lesson_progress_count?: number;
    badges_count?: number;
}

interface PaginatedData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
}

interface Props {
    users: PaginatedData<UserItem>;
    filters: {
        search?: string | null;
        role?: string | null;
        status?: string | null;
    };
    currentAdminId: number;
}

const props = defineProps<Props>();

const searchInput = ref(props.filters.search ?? '');
const roleFilter = ref(props.filters.role ?? '');
const statusFilter = ref(props.filters.status ?? '');

const applyFilters = () => {
    router.get(
        '/admin/users',
        {
            search: searchInput.value || undefined,
            role: roleFilter.value || undefined,
            status: statusFilter.value || undefined,
        },
        { preserveState: true, replace: true }
    );
};

watch([roleFilter, statusFilter], () => {
    applyFilters();
});

const toggleUserStatus = (user: UserItem) => {
    if (user.id === props.currentAdminId) {
        alert('Anda tidak dapat menonaktifkan akun Anda sendiri (FR-ADMIN-04).');
        return;
    }

    const nextState = !user.is_active;
    const actionText = nextState ? 'mengaktifkan' : 'menonaktifkan';

    if (confirm(`Apakah Anda yakin ingin ${actionText} akun "${user.name}" (@${user.username})?`)) {
        router.patch(`/admin/users/${user.id}`, {
            is_active: nextState,
        }, {
            preserveScroll: true,
        });
    }
};

const formatDate = (dateStr: string) => {
    try {
        return new Intl.DateTimeFormat('id-ID', {
            dateStyle: 'medium',
        }).format(new Date(dateStr));
    } catch {
        return dateStr;
    }
};
</script>

<template>
    <AdminLayout>
        <Head title="Kelola Pengguna - Admin LinuxLab" />

        <div class="space-y-6">
            <!-- Header Halaman -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-surface-card p-6 rounded-card border border-border">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <svg class="w-6 h-6 text-brand-primary-light" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <h1 class="text-xl sm:text-2xl font-bold text-text-primary tracking-tight">
                            Manajemen Pengguna
                        </h1>
                    </div>
                    <p class="text-sm text-text-muted">
                        Kelola akun pengguna terdaftar, pantau keaktifan belajar, dan atur status akses login (FR-ADMIN-04).
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
                        placeholder="Cari nama, username, atau email..."
                        class="w-full bg-surface-elevated pl-8 pr-3 py-1.5 rounded-input border border-border text-xs text-text-primary placeholder:text-text-muted focus:border-brand-primary outline-none"
                    />
                </form>

                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <select
                        v-model="roleFilter"
                        class="bg-surface-elevated border border-border text-xs rounded-input px-3 py-1.5 text-text-primary focus:border-brand-primary outline-none"
                    >
                        <option value="">Semua Peran</option>
                        <option value="user">User Biasa</option>
                        <option value="admin">Administrator</option>
                    </select>

                    <select
                        v-model="statusFilter"
                        class="bg-surface-elevated border border-border text-xs rounded-input px-3 py-1.5 text-text-primary focus:border-brand-primary outline-none"
                    >
                        <option value="">Semua Status</option>
                        <option value="active">Aktif</option>
                        <option value="inactive">Nonaktif</option>
                    </select>
                </div>
            </div>

            <!-- Tabel Pengguna -->
            <AppCard>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-surface-high text-text-dim text-[11px] font-mono uppercase tracking-wider border-b border-border">
                            <tr>
                                <th class="py-3 px-4">PENGGUNA</th>
                                <th class="py-3 px-4">EMAIL</th>
                                <th class="py-3 px-4">PERAN</th>
                                <th class="py-3 px-4">STATUS</th>
                                <th class="py-3 px-4">TERDAFTAR</th>
                                <th class="py-3 px-4 text-right">AKSI KONTROL</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr
                                v-for="u in users.data"
                                :key="u.id"
                                class="hover:bg-surface-elevated/40 transition-colors"
                            >
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-surface-elevated border border-border flex items-center justify-center font-mono text-xs font-bold text-brand-primary-light shrink-0">
                                            {{ u.name.charAt(0).toUpperCase() }}
                                        </div>
                                        <div>
                                            <div class="font-semibold text-text-primary flex items-center gap-1.5">
                                                <span>{{ u.name }}</span>
                                                <span v-if="u.id === currentAdminId" class="text-[10px] px-1.5 py-0.2 rounded bg-brand-primary/10 text-brand-primary-light font-mono font-normal">
                                                    (Anda)
                                                </span>
                                            </div>
                                            <div class="text-xs text-text-dim font-mono">@{{ u.username }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-xs font-mono text-text-muted">
                                    {{ u.email }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <span
                                        class="px-2 py-0.5 rounded text-[11px] font-mono uppercase font-semibold"
                                        :class="u.role === 'admin' ? 'bg-amber-100 text-amber-800 border border-amber-300' : 'bg-surface-elevated text-text-muted border border-border'"
                                    >
                                        {{ u.role }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-mono font-semibold"
                                        :class="u.is_active ? 'bg-brand-primary/10 text-brand-primary-light border border-brand-primary/30' : 'bg-brand-danger/10 text-brand-danger-light border border-brand-danger/30'"
                                    >
                                        <span class="w-1.5 h-1.5 rounded-full" :class="u.is_active ? 'bg-brand-primary' : 'bg-brand-danger'"></span>
                                        {{ u.is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-xs text-text-dim font-mono">
                                    {{ formatDate(u.created_at) }}
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <!-- Jika akun sendiri: tombol dinonaktifkan sesuai FR-ADMIN-04 -->
                                    <button
                                        v-if="u.id === currentAdminId"
                                        type="button"
                                        disabled
                                        class="px-3 py-1 rounded text-xs font-mono bg-surface-elevated text-text-dim border border-border opacity-50 cursor-not-allowed"
                                        title="Administrator tidak dapat menonaktifkan akun sendiri (FR-ADMIN-04)"
                                    >
                                        Akun Sendiri (Terkunci)
                                    </button>

                                    <!-- Akun pengguna lain -->
                                    <button
                                        v-else
                                        type="button"
                                        @click="toggleUserStatus(u)"
                                        class="px-3 py-1 rounded text-xs font-semibold font-mono transition-colors border"
                                        :class="u.is_active
                                            ? 'bg-brand-danger/10 hover:bg-brand-danger/20 text-brand-danger-light border-brand-danger/30'
                                            : 'bg-brand-primary/10 hover:bg-brand-primary/20 text-brand-primary-light border-brand-primary/30'"
                                    >
                                        {{ u.is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="users.data.length === 0">
                                <td colspan="6" class="py-8 text-center text-xs text-text-muted">
                                    Tidak ada data pengguna yang sesuai.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </AppCard>
        </div>
    </AdminLayout>
</template>
