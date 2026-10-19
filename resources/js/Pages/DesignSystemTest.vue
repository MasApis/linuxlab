<script setup lang="ts">
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import UserLayout from '@/Layouts/UserLayout.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AppButton from '@/Components/AppButton.vue';
import FormInput from '@/Components/FormInput.vue';
import AppCard from '@/Components/AppCard.vue';
import ProgressBar from '@/Components/ProgressBar.vue';
import BadgeCard from '@/Components/BadgeCard.vue';
import EmptyState from '@/Components/EmptyState.vue';

// Active Layout Switcher for testing
type LayoutType = 'user' | 'public' | 'admin';
const activeLayout = ref<LayoutType>('user');

// Interactive test state
const testInputText = ref('');
const testCommandInput = ref('ls -la /var/log');
const testErrorInput = ref('rm -rf /');
const dynamicProgress = ref(65);
const isButtonLoading = ref(false);

const toggleButtonLoading = () => {
    isButtonLoading.value = true;
    setTimeout(() => {
        isButtonLoading.value = false;
    }, 2000);
};
</script>

<template>
    <Head title="Fondasi Visual & Komponen Bersama - LinuxLab" />

    <!-- Dynamic Layout Wrapper -->
    <component
        :is="activeLayout === 'public' ? PublicLayout : activeLayout === 'admin' ? AdminLayout : UserLayout"
    >
        <div class="space-y-12 max-w-6xl mx-auto">
            <!-- Layout Switcher Floating Control -->
            <div class="p-4 rounded-card border border-border bg-surface-elevated/90 backdrop-blur-md flex flex-col sm:flex-row items-center justify-between gap-4 sticky top-20 z-20 shadow-xl">
                <div>
                    <h2 class="text-sm font-bold text-text-primary flex items-center gap-2">
                        <span class="inline-block w-2.5 h-2.5 rounded-full bg-brand-primary animate-pulse"></span>
                        Pengujian Fondasi Visual LinuxLab
                    </h2>
                    <p class="text-xs text-text-muted">
                        Pilih tata letak untuk melihat tampilan di dalam Layout Publik, Pengguna, atau Admin:
                    </p>
                </div>

                <div class="flex items-center gap-1.5 bg-surface-card p-1 rounded-button border border-border">
                    <button
                        type="button"
                        @click="activeLayout = 'public'"
                        class="px-3 py-1.5 text-xs font-medium rounded-button transition-colors"
                        :class="activeLayout === 'public' ? 'bg-brand-primary text-brand-primary-dark font-bold' : 'text-text-muted hover:text-text-primary'"
                    >
                        Layout Publik
                    </button>
                    <button
                        type="button"
                        @click="activeLayout = 'user'"
                        class="px-3 py-1.5 text-xs font-medium rounded-button transition-colors"
                        :class="activeLayout === 'user' ? 'bg-brand-primary text-brand-primary-dark font-bold' : 'text-text-muted hover:text-text-primary'"
                    >
                        Layout Pengguna
                    </button>
                    <button
                        type="button"
                        @click="activeLayout = 'admin'"
                        class="px-3 py-1.5 text-xs font-medium rounded-button transition-colors"
                        :class="activeLayout === 'admin' ? 'bg-brand-primary text-brand-primary-dark font-bold' : 'text-text-muted hover:text-text-primary'"
                    >
                        Layout Admin
                    </button>
                </div>
            </div>

            <!-- Section 1: Palette Warna & Desain Token -->
            <section class="space-y-4">
                <div class="border-b border-border pb-2">
                    <h2 class="text-xl font-bold text-text-primary">1. Gaya Visual & Token Desain</h2>
                    <p class="text-xs text-text-muted">Ditetapkan satu kali di konfigurasi tema (Tailwind & CSS Utama) tanpa pengulangan nilai warna hex/ukuran.</p>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3">
                    <div class="p-3 rounded-card bg-surface-base border border-border">
                        <div class="h-8 rounded bg-surface-base border border-border mb-2"></div>
                        <p class="text-xs font-semibold text-text-primary">Surface Base</p>
                        <p class="text-[10px] font-mono text-text-dim">#0b1326</p>
                    </div>
                    <div class="p-3 rounded-card bg-surface-card border border-border">
                        <div class="h-8 rounded bg-surface-card border border-border mb-2"></div>
                        <p class="text-xs font-semibold text-text-primary">Surface Card</p>
                        <p class="text-[10px] font-mono text-text-dim">#131b2e</p>
                    </div>
                    <div class="p-3 rounded-card bg-surface-elevated border border-border">
                        <div class="h-8 rounded bg-surface-elevated border border-border mb-2"></div>
                        <p class="text-xs font-semibold text-text-primary">Surface Elevated</p>
                        <p class="text-[10px] font-mono text-text-dim">#171f33</p>
                    </div>
                    <div class="p-3 rounded-card bg-surface-card border border-border">
                        <div class="h-8 rounded bg-brand-primary mb-2"></div>
                        <p class="text-xs font-semibold text-text-primary">Primary (Emerald)</p>
                        <p class="text-[10px] font-mono text-text-dim">#10b981</p>
                    </div>
                    <div class="p-3 rounded-card bg-surface-card border border-border">
                        <div class="h-8 rounded bg-brand-secondary mb-2"></div>
                        <p class="text-xs font-semibold text-text-primary">Secondary (Amber)</p>
                        <p class="text-[10px] font-mono text-text-dim">#f59e0b</p>
                    </div>
                    <div class="p-3 rounded-card bg-surface-card border border-border">
                        <div class="h-8 rounded bg-brand-danger mb-2"></div>
                        <p class="text-xs font-semibold text-text-primary">Danger (Red)</p>
                        <p class="text-[10px] font-mono text-text-dim">#ef4444</p>
                    </div>
                </div>
            </section>

            <!-- Section 2: Tombol (AppButton) -->
            <section class="space-y-4">
                <div class="border-b border-border pb-2">
                    <h2 class="text-xl font-bold text-text-primary">2. Komponen Tombol (`AppButton.vue`)</h2>
                    <p class="text-xs text-text-muted">Varian semantik, skala ukuran, dan status interaktif (normal, loading, disabled).</p>
                </div>

                <AppCard title="Varian Tombol">
                    <div class="flex flex-wrap items-center gap-3">
                        <AppButton variant="primary">Primary (Aksi Utama)</AppButton>
                        <AppButton variant="secondary">Secondary (Aksi Kedua)</AppButton>
                        <AppButton variant="accent">Accent (Gamifikasi/Reward)</AppButton>
                        <AppButton variant="danger">Danger (Hapus/Batal)</AppButton>
                        <AppButton variant="ghost">Ghost (Tautan Tombol)</AppButton>
                    </div>
                </AppCard>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <AppCard title="Ukuran Tombol">
                        <div class="flex flex-wrap items-center gap-3">
                            <AppButton size="sm">Kecil (sm)</AppButton>
                            <AppButton size="md">Standar (md)</AppButton>
                            <AppButton size="lg">Besar (lg)</AppButton>
                        </div>
                    </AppCard>

                    <AppCard title="Status Interaktif (Disabled & Loading)">
                        <div class="flex flex-wrap items-center gap-3">
                            <AppButton disabled>Tombol Dinonaktifkan</AppButton>
                            <AppButton :loading="isButtonLoading" @click="toggleButtonLoading">
                                {{ isButtonLoading ? 'Memproses...' : 'Klik untuk Uji Loading' }}
                            </AppButton>
                        </div>
                    </AppCard>
                </div>
            </section>

            <!-- Section 3: Kolom Input & Pesan Error -->
            <section class="space-y-4">
                <div class="border-b border-border pb-2">
                    <h2 class="text-xl font-bold text-text-primary">3. Komponen Form Input (`FormInput.vue` & `InputError.vue`)</h2>
                    <p class="text-xs text-text-muted">Mendukung label, teks petunjuk (hint), mode monospace untuk perintah Linux, dan pesan error validasi.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <AppCard title="Input Teks Standar">
                        <div class="space-y-4">
                            <FormInput
                                v-model="testInputText"
                                label="Nama Lengkap"
                                placeholder="Masukkan nama..."
                                hint="Nama akan dicetak pada sertifikat kelulusan."
                                :required="true"
                            />
                            <p class="text-xs text-text-dim">Nilai saat ini: <span class="text-text-primary font-mono">{{ testInputText || '(kosong)' }}</span></p>
                        </div>
                    </AppCard>

                    <AppCard title="Input Perintah Linux (Font Monospace)">
                        <div class="space-y-4">
                            <FormInput
                                v-model="testCommandInput"
                                label="Perintah Terminal"
                                placeholder="mis. ls -la /home"
                                :is-mono="true"
                                hint="Format input monospace sesuai sintaks CLI."
                            >
                                <template #prefix>
                                    <span class="font-mono text-brand-primary font-bold text-xs">$</span>
                                </template>
                            </FormInput>
                        </div>
                    </AppCard>

                    <AppCard title="Input dengan Pesan Validasi Error">
                        <div class="space-y-4">
                            <FormInput
                                v-model="testErrorInput"
                                label="Perintah Validasi"
                                :is-mono="true"
                                error="Perintah berbahaya terdeteksi dan tidak diizinkan."
                            />
                        </div>
                    </AppCard>

                    <AppCard title="Input Nonaktif (Disabled)">
                        <div class="space-y-4">
                            <FormInput
                                model-value="sesi-terminal-12345"
                                label="ID Sesi (Otomatis)"
                                :disabled="true"
                                hint="Kolom hanya baca yang dikelola sistem."
                            />
                        </div>
                    </AppCard>
                </div>
            </section>

            <!-- Section 4: Kartu (AppCard) -->
            <section class="space-y-4">
                <div class="border-b border-border pb-2">
                    <h2 class="text-xl font-bold text-text-primary">4. Komponen Kartu (`AppCard.vue`)</h2>
                    <p class="text-xs text-text-muted">Kontainer visual konsisten dengan dukungan header, aksi header, isi body, dan footer.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <AppCard
                        title="Kartu Standar"
                        subtitle="Keterangan singkat kartu"
                    >
                        <p class="text-sm text-text-muted leading-relaxed">
                            Ini adalah kartu standar dengan judul, subjudul, dan isi konten informatif.
                        </p>
                    </AppCard>

                    <AppCard
                        title="Kartu Interaktif (Hoverable)"
                        subtitle="Arahkan kursor ke kartu ini"
                        :hoverable="true"
                    >
                        <p class="text-sm text-text-muted leading-relaxed">
                            Kartu ini memiliki efek elevasi dan garis tepi aktif saat kursor diarahkan, cocok untuk daftar modul atau pelajaran.
                        </p>
                    </AppCard>

                    <AppCard
                        title="Kartu dengan Aksi & Footer"
                        subtitle="Struktur lengkap"
                    >
                        <template #header-action>
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-badge bg-brand-primary/15 text-brand-primary border border-brand-primary/30">
                                Aktif
                            </span>
                        </template>

                        <p class="text-sm text-text-muted leading-relaxed">
                            Kartu dengan slot header action di kanan atas dan slot footer di bawah.
                        </p>

                        <template #footer>
                            <span>Dibuat: 19 Okt 2026</span>
                            <AppButton size="sm" variant="ghost">Detail &rarr;</AppButton>
                        </template>
                    </AppCard>
                </div>
            </section>

            <!-- Section 5: Bar Progres (ProgressBar) -->
            <section class="space-y-4">
                <div class="border-b border-border pb-2">
                    <h2 class="text-xl font-bold text-text-primary">5. Komponen Bar Progres (`ProgressBar.vue`)</h2>
                    <p class="text-xs text-text-muted">Menampilkan kemajuan modul dan latihan dalam persentase terukur.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <AppCard title="Uji Coba Progres Interaktif">
                        <div class="space-y-4">
                            <ProgressBar
                                :value="dynamicProgress"
                                label="Progres Dinamis"
                                size="md"
                            />

                            <div class="flex items-center gap-3 pt-2">
                                <span class="text-xs text-text-muted">Ubah Nilai:</span>
                                <input
                                    type="range"
                                    min="0"
                                    max="100"
                                    v-model.number="dynamicProgress"
                                    class="w-full accent-brand-primary cursor-pointer"
                                />
                                <span class="text-xs font-mono font-bold text-brand-primary w-10 text-right">{{ dynamicProgress }}%</span>
                            </div>
                        </div>
                    </AppCard>

                    <AppCard title="Variasi Ukuran & Varian Warna">
                        <div class="space-y-4">
                            <ProgressBar :value="25" label="Ukuran Kecil (sm) - Varian Sky" size="sm" variant="tertiary" />
                            <ProgressBar :value="60" label="Ukuran Standar (md) - Varian Emerald" size="md" variant="primary" />
                            <ProgressBar :value="90" label="Ukuran Besar (lg) - Varian Amber XP" size="lg" variant="secondary" />
                        </div>
                    </AppCard>
                </div>
            </section>

            <!-- Section 6: Kartu Lencana (BadgeCard) -->
            <section class="space-y-4">
                <div class="border-b border-border pb-2">
                    <h2 class="text-xl font-bold text-text-primary">6. Komponen Kartu Lencana (`BadgeCard.vue`)</h2>
                    <p class="text-xs text-text-muted">Status pencapaian gamifikasi sesuai SPEC Bagian 12 (keadaan terbuka vs terkunci).</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    <BadgeCard
                        name="Langkah Pertama"
                        description="Menyelesaikan latihan terminal interaktif pertamamu."
                        :is-unlocked="true"
                        unlocked-at="19 Okt 2026"
                    />

                    <BadgeCard
                        name="Penjelajah File System"
                        description="Menyelesaikan seluruh pelajaran pada modul File System."
                        :is-unlocked="true"
                        unlocked-at="19 Okt 2026"
                    />

                    <BadgeCard
                        name="Penjaga Izin"
                        description="Menguasai hak akses chmod dan permission Linux."
                        :is-unlocked="false"
                        criteria="Selesaikan Modul Izin File"
                    />

                    <BadgeCard
                        name="Pembelajar Tekun"
                        description="Menyelesaikan sekurang-kurangnya 10 latihan berbeda."
                        :is-unlocked="false"
                        criteria="Selesaikan 10 latihan"
                    />
                </div>
            </section>

            <!-- Section 7: Keadaan Kosong (EmptyState) -->
            <section class="space-y-4">
                <div class="border-b border-border pb-2">
                    <h2 class="text-xl font-bold text-text-primary">7. Keadaan Kosong (`EmptyState.vue` - SPEC UI-02)</h2>
                    <p class="text-xs text-text-muted">Empat kasus wajib keadaan kosong sesuai ketentuan UI-02.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Kasus 1: Pengguna baru tanpa progres -->
                    <AppCard title="1. Pengguna Baru Tanpa Progres">
                        <EmptyState
                            icon="progress"
                            title="Belum Ada Progres Belajar"
                            description="Kamu belum memulai pelajaran apa pun. Pilih modul pertama untuk mulai berlatih dengan terminal Linux langsung di browser."
                        >
                            <template #action>
                                <AppButton variant="primary" size="sm">Pilih Modul Pertama</AppButton>
                            </template>
                        </EmptyState>
                    </AppCard>

                    <!-- Kasus 2: Pelajaran tanpa latihan -->
                    <AppCard title="2. Pelajaran Tanpa Latihan">
                        <EmptyState
                            icon="exercise"
                            title="Belum Ada Latihan Praktik"
                            description="Pelajaran ini saat ini hanya memuat materi teori dan cheat sheet. Latihan interaktif sedang disiapkan."
                        >
                            <template #action>
                                <AppButton variant="secondary" size="sm">Kembali ke Daftar Materi</AppButton>
                            </template>
                        </EmptyState>
                    </AppCard>

                    <!-- Kasus 3: Forum tanpa pertanyaan -->
                    <AppCard title="3. Forum Tanpa Pertanyaan">
                        <EmptyState
                            icon="forum"
                            title="Belum Ada Diskusi"
                            description="Belum ada yang menanyakan materi ini. Jadilah yang pertama mengajukan pertanyaan untuk berdiskusi bersama pembelajar lain!"
                        >
                            <template #action>
                                <AppButton variant="accent" size="sm">Tulis Pertanyaan Baru</AppButton>
                            </template>
                        </EmptyState>
                    </AppCard>

                    <!-- Kasus 4: Lencana yang belum ada yang diperoleh -->
                    <AppCard title="4. Lencana Belum Ada yang Diperoleh">
                        <EmptyState
                            icon="badge"
                            title="Belum Ada Lencana yang Diraih"
                            description="Selesaikan latihan pertamamu atau capai modul 100% untuk membuka lencana pertamamu di LinuxLab."
                        >
                            <template #action>
                                <AppButton variant="primary" size="sm">Mulai Latihan Pertama</AppButton>
                            </template>
                        </EmptyState>
                    </AppCard>
                </div>
            </section>
        </div>
    </component>
</template>
