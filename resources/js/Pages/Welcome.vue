<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import AppButton from '@/Components/AppButton.vue';
import AppCard from '@/Components/AppCard.vue';
import EmptyState from '@/Components/EmptyState.vue';
import ModuleIcon from '@/Components/ModuleIcon.vue';
import type { Module } from '@/types';

interface Props {
    modules?: Module[];
}

const props = defineProps<Props>();

const totalLessons = computed(() =>
    (props.modules ?? []).reduce((sum, m) => sum + (m.lessons_count ?? 0), 0),
);
</script>

<template>
    <PublicLayout>
        <Head title="LinuxLab - Belajar Linux Interaktif untuk Pemula" />

        <div class="py-10 sm:py-14 space-y-14 sm:space-y-16">
            <section class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-12 items-center py-4 sm:py-8">
                <div class="space-y-6 max-w-2xl">
                    <p class="inline-flex items-center rounded-md bg-brand-primary/10 px-3 py-1.5 text-xs font-semibold text-brand-primary-light">
                        Belajar Linux langsung dari browser
                    </p>
                    <h1 class="font-display text-4xl sm:text-5xl lg:text-[3.5rem] font-bold text-ink tracking-tight leading-tight">
                        Belajar Linux praktis, langsung dari browser.
                    </h1>
                    <p class="text-text-muted text-base sm:text-lg max-w-xl leading-relaxed">
                        Pelajari dasar Linux melalui materi terstruktur dan latihan dengan penilaian otomatis.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-3 pt-1">
                        <Link :href="route('register')" class="block w-full sm:w-auto">
                            <AppButton variant="primary" size="lg" class="w-full justify-center">
                                Daftar akun gratis
                            </AppButton>
                        </Link>
                        <Link :href="route('login')" class="block w-full sm:w-auto">
                            <AppButton variant="secondary" size="lg" class="w-full justify-center">
                                Masuk ke akun
                            </AppButton>
                        </Link>
                    </div>
                    <div class="flex flex-wrap items-center gap-4 text-sm text-text-muted">
                        <span>Materi terstruktur</span>
                        <span>Latihan dengan penilaian otomatis</span>
                        <span>Praktik melalui browser</span>
                    </div>
                </div>

                <div class="rounded-panel overflow-hidden bg-ink border border-ink shadow-xl">
                    <div class="flex items-center gap-2 px-5 h-[52px]">
                        <span class="w-3 h-3 rounded-full bg-[#FF5F57]"></span>
                        <span class="w-3 h-3 rounded-full bg-[#FEBC2E]"></span>
                        <span class="w-3 h-3 rounded-full bg-[#28C840]"></span>
                        <span class="flex-1"></span>
                        <span class="font-mono text-xs text-white/50">bash — student@linuxlab: ~</span>
                    </div>
                    <div class="px-6 pt-5 pb-6 space-y-2.5 font-mono text-[13px] leading-relaxed">
                        <p class="text-[#58cc02]">$ curl -s https://linuxlab.id/init.sh | bash</p>
                        <p class="text-white/90">[✓] Mengalokasikan container Linux terisolasi...</p>
                        <p class="text-white/90">[✓] Menyiapkan modul &amp; lab interaktif</p>
                        <p class="text-white/50">[✓] Lingkungan aman: tidak memengaruhi OS lokal</p>
                        <p class="text-white/90">$ linuxlab --status</p>
                        <p class="text-[#58cc02]">Sandbox Aktif • {{ modules?.length ?? 0 }} modul • {{ totalLessons }} pelajaran</p>
                    </div>
                </div>
            </section>

            <section class="space-y-6" aria-labelledby="curriculum-heading">
                <div class="max-w-2xl space-y-2">
                    <h2 id="curriculum-heading" class="font-display text-2xl sm:text-3xl font-bold text-ink">
                        Kurikulum pembelajaran awal
                    </h2>
                    <p class="text-sm text-text-muted">
                        Tiga modul untuk membangun fondasi penggunaan Linux.
                    </p>
                </div>

                <div v-if="modules && modules.length > 0" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                    <AppCard
                        v-for="module in modules.slice(0, 3)"
                        :key="module.id"
                        class="flex flex-col justify-between min-h-64"
                    >
                        <div class="space-y-4">
                            <ModuleIcon :order="module.order" />
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-xs font-semibold px-2.5 py-1 rounded bg-brand-primary/10 text-brand-primary-light">
                                    <span>Modul {{ module.order }}</span>
                                </span>
                                <span class="text-xs text-text-muted">
                                    {{ module.lessons_count }} pelajaran
                                </span>
                            </div>
                            <h3 class="font-display text-xl font-bold text-ink">{{ module.title }}</h3>
                            <p class="text-sm text-text-muted leading-relaxed line-clamp-3">
                                {{ module.description }}
                            </p>
                        </div>
                        <div class="pt-5 mt-5 border-t border-border/60">
                            <Link :href="route('modules.show', module.slug)" class="block">
                                <AppButton variant="secondary" size="sm" class="w-full justify-center">
                                    Pelajari modul
                                </AppButton>
                            </Link>
                        </div>
                    </AppCard>
                </div>
                <EmptyState
                    v-else
                    title="Modul belum tersedia"
                    description="Materi pembelajaran belum ditambahkan. Silakan kembali lagi nanti."
                />
            </section>
        </div>
    </PublicLayout>
</template>
