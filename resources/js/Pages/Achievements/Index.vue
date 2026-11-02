<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';
import AppCard from '@/Components/AppCard.vue';
import AppButton from '@/Components/AppButton.vue';
import BadgeCard from '@/Components/BadgeCard.vue';

interface BadgeItem {
    id: number;
    name: string;
    description: string;
    icon: string;
    criteria_type: string;
    criteria_value?: any;
    is_earned: boolean;
    awarded_at?: string | null;
}

interface CertificateData {
    recipient_name: string;
    certificate_code: string;
    issued_at: string;
    download_url: string;
    verify_url: string;
}

interface Props {
    badges: BadgeItem[];
    certificate?: CertificateData | null;
    isEligible: boolean;
    uncompletedModules: string[];
}

const props = defineProps<Props>();

const filter = ref<'all' | 'earned' | 'locked'>('all');

const earnedCount = computed(() => props.badges.filter((b) => b.is_earned).length);
const totalBadges = computed(() => props.badges.length);

const filteredBadges = computed(() => {
    if (filter.value === 'earned') {
        return props.badges.filter((b) => b.is_earned);
    }
    if (filter.value === 'locked') {
        return props.badges.filter((b) => !b.is_earned);
    }
    return props.badges;
});

const getCriteriaText = (badge: BadgeItem): string => {
    switch (badge.criteria_type) {
        case 'first_exercise':
            return 'Selesaikan 1 latihan dengan benar';
        case 'lesson_completed':
            return 'Selesaikan seluruh latihan pada pelajaran terkait';
        case 'module_completed':
            return 'Selesaikan seluruh materi & latihan modul hingga 100%';
        case 'exercises_count':
            return `Selesaikan ${badge.criteria_value?.count ?? 10} latihan berbeda`;
        case 'first_question':
            return 'Buat pertanyaan pertama di forum diskusi';
        case 'best_answer':
            return `Raih ${badge.criteria_value?.count ?? 1} jawaban terbaik di forum`;
        default:
            return badge.description;
    }
};
</script>

<template>
    <UserLayout>
        <Head title="Pencapaian & Sertifikat - LinuxLab" />

        <div class="py-8 space-y-8">
            <!-- Header Banner -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-2 border-b border-border">
                <div>
                    <h1 class="font-display text-2xl sm:text-3xl font-bold text-ink tracking-tight">
                        Pencapaian & Sertifikasi
                    </h1>
                    <p class="text-sm text-text-muted mt-1">
                        Koleksi lencana penguasaan Linux dan sertifikat kelulusan Anda.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs px-3 py-1.5 rounded-full bg-surface-card border border-border text-brand-secondary-light font-semibold">
                        {{ earnedCount }} / {{ totalBadges }} Lencana Diraih
                    </span>
                </div>
            </div>

            <!-- Kartu Sertifikat Kelulusan (FR-CERT-03 & FR-CERT-05) -->
            <div class="p-6 sm:p-8 rounded-card bg-[#2f6e00] text-white relative overflow-hidden shadow-md">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    <div class="space-y-3 max-w-2xl">
                        <div class="flex items-center gap-2">
                            <span
                                class="text-xs font-bold px-2.5 py-1 rounded bg-white/20 text-white border border-white/30"
                            >
                                {{ certificate ? 'SERTIFIKAT TERBIT' : 'SERTIFIKAT KELULUSAN TERKUNCI' }}
                            </span>
                            <span v-if="certificate" class="text-xs text-white/80">
                                Kode: {{ certificate.certificate_code }}
                            </span>
                        </div>

                        <h2 class="font-display text-2xl font-bold text-white">
                            Sertifikat Penyelesaian Kurikulum LinuxLab
                        </h2>

                        <p class="text-xs sm:text-sm text-white/85 leading-relaxed">
                            <template v-if="certificate">
                                Selamat! Anda telah menyelesaikan seluruh modul pembelajaran LinuxLab. Sertifikat Anda telah diterbitkan atas nama <strong>{{ certificate.recipient_name }}</strong> pada tanggal <strong>{{ certificate.issued_at }}</strong>.
                            </template>
                            <template v-else>
                                Sertifikat penyelesaian diterbitkan otomatis setelah Anda menyelesaikan 100% seluruh modul pembelajaran LinuxLab (Dasar File System, Permission, dan Text Processing).
                            </template>
                        </p>

                        <!-- Module completion status when locked (FR-CERT-05) -->
                        <div v-if="!certificate" class="pt-2">
                            <p class="text-xs font-semibold text-white/90 mb-1.5">
                                Modul yang belum diselesaikan:
                            </p>
                            <div class="flex flex-wrap gap-2">
                                <span
                                    v-for="uncompleted in uncompletedModules"
                                    :key="uncompleted"
                                    class="text-xs px-2.5 py-1 rounded bg-white/15 border border-white/25 text-white flex items-center gap-1.5"
                                >
                                    <svg class="w-3.5 h-3.5 text-white/80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    <span>{{ uncompleted }}</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Certificate Actions -->
                    <div class="flex flex-col sm:flex-row lg:flex-col items-stretch sm:items-center lg:items-end gap-3 flex-shrink-0">
                        <template v-if="certificate">
                            <a :href="certificate.download_url" class="inline-block">
                                <AppButton variant="secondary" size="md" class="w-full justify-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    <span>Unduh PDF Sertifikat</span>
                                </AppButton>
                            </a>
                            <Link :href="certificate.verify_url" class="text-xs text-white/90 hover:text-white hover:underline flex items-center gap-1 justify-center">
                                <span>Verifikasi Keabsahan</span>
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                            </Link>
                        </template>
                        <template v-else>
                            <div class="px-4 py-2.5 rounded bg-white/15 border border-white/25 text-center text-xs text-white/90 flex items-center gap-2">
                                <svg class="w-4 h-4 text-white/80 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                <span>Selesaikan semua modul untuk membuka unduh PDF</span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Badges Grid Section (FR-PROG-05) -->
            <div class="space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h2 class="font-display text-xl font-bold text-ink">
                            Daftar Lencana Keahlian
                        </h2>
                        <p class="text-xs sm:text-sm text-text-muted">
                            Koleksi lencana pencapaian otomatis berdasarkan penyelesaian tugas dan kontribusi Anda.
                        </p>
                    </div>

                    <!-- Filter Tabs -->
                    <div class="flex items-center gap-1 p-1 rounded-card bg-surface-card border border-border self-start">
                        <button
                            type="button"
                            @click="filter = 'all'"
                            class="px-3 py-1 text-xs font-medium rounded transition-colors"
                            :class="filter === 'all' ? 'bg-brand-primary text-white font-bold shadow-sm' : 'text-text-muted hover:text-text-primary'"
                        >
                            Semua ({{ totalBadges }})
                        </button>
                        <button
                            type="button"
                            @click="filter = 'earned'"
                            class="px-3 py-1 text-xs font-medium rounded transition-colors"
                            :class="filter === 'earned' ? 'bg-brand-primary text-white font-bold shadow-sm' : 'text-text-muted hover:text-text-primary'"
                        >
                            Diperoleh ({{ earnedCount }})
                        </button>
                        <button
                            type="button"
                            @click="filter = 'locked'"
                            class="px-3 py-1 text-xs font-medium rounded transition-colors"
                            :class="filter === 'locked' ? 'bg-brand-primary text-white font-bold shadow-sm' : 'text-text-muted hover:text-text-primary'"
                        >
                            Terkunci ({{ totalBadges - earnedCount }})
                        </button>
                    </div>
                </div>

                <!-- Badges Grid -->
                <div v-if="filteredBadges.length > 0" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <BadgeCard
                        v-for="badge in filteredBadges"
                        :key="badge.id"
                        :name="badge.name"
                        :description="badge.description"
                        :is-unlocked="badge.is_earned"
                        :unlocked-at="badge.awarded_at || undefined"
                        :criteria="getCriteriaText(badge)"
                        :icon="badge.icon"
                    />
                </div>

                <!-- Empty State (UI-02) -->
                <div v-else class="rounded-card border border-dashed border-border p-12 text-center space-y-3">
                    <div class="w-12 h-12 rounded-full bg-surface-high text-text-dim mx-auto flex items-center justify-center text-xl">
                        ★
                    </div>
                    <h3 class="text-base font-bold text-ink">Tidak Ada Lencana yang Sesuai</h3>
                    <p class="text-xs text-text-muted max-w-sm mx-auto">
                        {{ filter === 'earned' ? 'Anda belum memperoleh lencana. Selesaikan latihan pertama Anda untuk mulai mengoleksi lencana!' : 'Semua lencana telah berhasil Anda peroleh!' }}
                    </p>
                </div>
            </div>
        </div>
    </UserLayout>
</template>
