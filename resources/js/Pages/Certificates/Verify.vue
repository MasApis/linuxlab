<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import AppCard from '@/Components/AppCard.vue';
import AppButton from '@/Components/AppButton.vue';

interface VerificationResult {
    status: 'valid' | 'not_found';
    recipient_name?: string;
    issued_at?: string;
    certificate_code?: string;
    completed_modules?: string[];
}

interface Props {
    code?: string | null;
    result?: VerificationResult | null;
}

const props = defineProps<Props>();

const searchInput = ref(props.code || '');

const handleSearch = () => {
    if (!searchInput.value.trim()) return;
    router.get(route('certificates.verify', { code: searchInput.value.trim() }));
};
</script>

<template>
    <PublicLayout>
        <Head title="Verifikasi Sertifikat - LinuxLab" />

        <div class="py-12 max-w-3xl mx-auto space-y-8">
            <!-- Header Section -->
            <div class="text-center space-y-3">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-brand-primary/10 text-brand-primary text-xs font-mono font-medium border border-brand-primary/20">
                    <span>VERIFIKASI PUBLIK</span>
                </div>
                <h1 class="text-3xl sm:text-4xl font-extrabold text-text-primary tracking-tight">
                    Verifikasi Keaslian Sertifikat LinuxLab
                </h1>
                <p class="text-sm text-text-muted max-w-xl mx-auto leading-relaxed">
                    Masukkan kode unik sertifikat untuk memastikan keabsahan kelulusan dan daftar kurikulum yang telah diselesaikan.
                </p>
            </div>

            <!-- Search Form Card -->
            <AppCard class="p-6 bg-surface-card border-border shadow-lg">
                <form @submit.prevent="handleSearch" class="flex flex-col sm:flex-row items-center gap-3">
                    <div class="relative flex-1 w-full">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-text-dim font-mono text-sm">
                            &gt;_
                        </div>
                        <input
                            v-model="searchInput"
                            type="text"
                            placeholder="Contoh: LLAB-2026-X8K9M2"
                            class="w-full pl-10 pr-4 py-2.5 bg-surface-base border border-border rounded-button text-sm font-mono text-text-primary placeholder:text-text-dim focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary uppercase"
                            required
                        />
                    </div>
                    <AppButton type="submit" variant="primary" size="md" class="w-full sm:w-auto justify-center px-6">
                        Verifikasi Kode
                    </AppButton>
                </form>
            </AppCard>

            <!-- Verification Result Display (FR-CERT-04) -->
            <div v-if="result" class="animate-in fade-in duration-200">
                <!-- Result: VALID (SAH) -->
                <AppCard
                    v-if="result.status === 'valid'"
                    class="p-8 border-brand-primary/40 bg-surface-card/90 space-y-6 relative overflow-hidden shadow-xl"
                >
                    <div class="flex items-center justify-between border-b border-border/80 pb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-brand-primary/15 text-brand-primary flex items-center justify-center font-bold text-lg border border-brand-primary/30">
                                ✓
                            </div>
                            <div>
                                <span class="text-xs font-mono uppercase tracking-wider text-text-dim">Hasil Verifikasi</span>
                                <h2 class="text-xl font-bold text-brand-primary">Sertifikat Sah & Terdaftar</h2>
                            </div>
                        </div>

                        <span class="px-3 py-1 rounded-full text-xs font-mono font-bold bg-brand-primary/15 text-brand-primary border border-brand-primary/30">
                            TERVERIFIKASI
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                        <div class="space-y-1">
                            <span class="text-xs text-text-dim block">Nama Penerima:</span>
                            <span class="text-lg font-bold text-text-primary block">{{ result.recipient_name }}</span>
                        </div>

                        <div class="space-y-1">
                            <span class="text-xs text-text-dim block">Kode Sertifikat:</span>
                            <span class="text-base font-mono font-bold text-brand-primary block">{{ result.certificate_code }}</span>
                        </div>

                        <div class="space-y-1">
                            <span class="text-xs text-text-dim block">Tanggal Penerbitan:</span>
                            <span class="text-sm font-medium text-text-primary block">{{ result.issued_at }}</span>
                        </div>

                        <div class="space-y-1">
                            <span class="text-xs text-text-dim block">Institusi Penerbit:</span>
                            <span class="text-sm font-medium text-text-primary block">LinuxLab Interactive Learning Platform</span>
                        </div>
                    </div>

                    <!-- Completed Modules -->
                    <div class="pt-4 border-t border-border/80 space-y-2">
                        <span class="text-xs font-semibold text-text-dim uppercase tracking-wider block">
                            Modul Kurikulum yang Telah Diselesaikan:
                        </span>
                        <div class="flex flex-wrap gap-2">
                            <span
                                v-for="mod in result.completed_modules"
                                :key="mod"
                                class="text-xs font-medium px-3 py-1 rounded bg-surface-elevated border border-border text-text-primary flex items-center gap-1.5"
                            >
                                <span class="w-1.5 h-1.5 rounded-full bg-brand-primary"></span>
                                <span>{{ mod }}</span>
                            </span>
                        </div>
                    </div>
                </AppCard>

                <!-- Result: NOT FOUND (TIDAK DITEMUKAN) -->
                <AppCard
                    v-else
                    class="p-8 border-red-500/40 bg-surface-card/90 space-y-4 shadow-xl text-center"
                >
                    <div class="w-12 h-12 rounded-full bg-red-500/15 text-red-400 mx-auto flex items-center justify-center font-bold text-xl border border-red-500/30">
                        ✕
                    </div>
                    <div class="space-y-1">
                        <h2 class="text-xl font-bold text-red-400">Sertifikat Tidak Ditemukan</h2>
                        <p class="text-xs sm:text-sm text-text-muted max-w-md mx-auto leading-relaxed">
                            Kode sertifikat <code class="font-mono text-text-primary bg-surface-elevated px-1.5 py-0.5 rounded">{{ code }}</code> tidak terdaftar dalam basis data LinuxLab. Pastikan format kode sudah benar atau hubungi pihak terkait.
                        </p>
                    </div>
                </AppCard>
            </div>
        </div>
    </PublicLayout>
</template>
