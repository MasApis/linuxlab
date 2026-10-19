<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogo from '@/Components/AppLogo.vue';

defineProps<{
    activeTab: 'login' | 'register';
}>();
</script>

<template>
    <div class="min-h-screen bg-surface-base text-text-primary flex flex-col relative overflow-x-hidden selection:bg-brand-primary selection:text-black">
        <!-- Ambient Light Accents -->
        <div class="absolute top-0 left-0 w-96 h-96 bg-brand-primary/10 rounded-full blur-3xl pointer-events-none -translate-x-1/2 -translate-y-1/2"></div>
        <div class="absolute bottom-0 right-0 w-[500px] h-[500px] bg-brand-tertiary/10 rounded-full blur-3xl pointer-events-none translate-x-1/3 translate-y-1/3"></div>

        <!-- Header Ringkas -->
        <header class="w-full h-16 border-b border-border-subtle bg-surface-base/80 backdrop-blur-md sticky top-0 z-30 px-6 lg:px-12 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <AppLogo href="/" size="md" />
            </div>
            <Link
                href="/"
                class="inline-flex items-center gap-1.5 text-sm font-medium text-text-muted hover:text-brand-primary-light transition-colors"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali ke Beranda</span>
            </Link>
        </header>

        <!-- Kontainer Utama: Split Screen (Sesuai wireframe 17_wf_auth / linuxstart_masuk_daftar_split_screen) -->
        <main class="flex-1 w-full max-w-7xl mx-auto px-6 lg:px-12 py-8 lg:py-12 flex items-center">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 w-full items-center">
                <!-- KOLOM KIRI: Terminal Mockup & Informasi Fitur -->
                <div class="lg:col-span-6 flex flex-col justify-center space-y-6">
                    <!-- Badge Info -->
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-surface-card border border-brand-primary/30 text-brand-primary-light text-xs font-mono w-max">
                        <span class="w-2 h-2 rounded-full bg-brand-primary animate-pulse"></span>
                        <span>TERMINAL 100% ONLINE DI BROWSER</span>
                    </div>

                    <!-- Tagline Utama -->
                    <div class="space-y-3">
                        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-white leading-tight">
                            Belajar Linux tanpa perlu install apa pun
                        </h1>
                        <p class="text-base lg:text-lg text-text-muted font-normal leading-relaxed">
                            Mulai dari perintah dasar hingga mahir mengelola sistem Linux melalui browser. Interaktif, terstruktur, dengan terminal container nyata dan penilaian otomatis.
                        </p>
                    </div>

                    <!-- Terminal Window Mockup -->
                    <div class="rounded-panel overflow-hidden border border-border bg-surface-base shadow-2xl relative">
                        <!-- Terminal Titlebar -->
                        <div class="bg-surface-card px-4 py-2.5 border-b border-border/80 flex items-center justify-between select-none">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-[#EF4444] inline-block"></span>
                                <span class="w-3 h-3 rounded-full bg-[#F59E0B] inline-block"></span>
                                <span class="w-3 h-3 rounded-full bg-[#10B981] inline-block"></span>
                                <span class="ml-2 font-mono text-xs text-text-muted">bash — guest@linuxlab:~</span>
                            </div>
                            <div class="flex items-center gap-2 text-text-muted text-xs font-mono">
                                <span class="flex items-center gap-1.5 text-brand-primary-light">
                                    <span class="w-1.5 h-1.5 rounded-full bg-brand-primary"></span>
                                    Sandbox Aktif
                                </span>
                            </div>
                        </div>

                        <!-- Terminal Screen Content -->
                        <div class="p-5 font-mono text-xs sm:text-sm leading-relaxed space-y-2 text-text-primary">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-brand-primary-light font-bold">user@linuxlab:~$</span>
                                <span class="text-white">curl -s https://linuxlab.id/init.sh | bash</span>
                            </div>
                            <div class="text-text-muted space-y-1 pl-2 border-l-2 border-border my-2">
                                <p class="text-brand-primary-light font-semibold flex items-center gap-2">
                                    <span>[✓]</span> Mengalokasikan container Linux terisolasi...
                                </p>
                                <p class="text-text-secondary flex items-center gap-2">
                                    <span>[✓]</span> Menyiapkan modul & lab interaktif
                                </p>
                                <p class="text-text-secondary flex items-center gap-2">
                                    <span>[✓]</span> Lingkungan aman: Tidak memengaruhi OS lokal Anda
                                </p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2 pt-1">
                                <span class="text-brand-primary-light font-bold">user@linuxlab:~$</span>
                                <span class="text-white">cat &lt;&lt; 'EOF'</span>
                            </div>
                            <div class="p-3 bg-surface-card/60 rounded border border-border/60 text-brand-secondary-light font-medium">
                                &gt; "Perjalanan ribuan baris perintah dimulai dari satu langkah kecil."
                            </div>
                            <div class="flex items-center gap-2 pt-2">
                                <span class="text-brand-primary-light font-bold">user@linuxlab:~$</span>
                                <span class="text-white">linuxlab --status</span>
                                <span class="inline-block w-2.5 h-4 bg-brand-primary animate-pulse ml-0.5"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KOLOM KANAN: Card Form Masuk / Daftar -->
                <div class="lg:col-span-6 flex justify-center">
                    <div class="w-full max-w-md bg-surface-card border border-border rounded-panel p-6 sm:p-8 shadow-2xl relative backdrop-blur-sm">
                        <!-- Tab Switcher: Masuk / Daftar -->
                        <div class="grid grid-cols-2 p-1 bg-surface-base rounded-card mb-6 border border-border/60">
                            <Link
                                :href="route('login')"
                                class="py-2.5 px-4 rounded-button font-medium text-sm transition-all flex items-center justify-center gap-2"
                                :class="activeTab === 'login'
                                    ? 'bg-brand-primary text-black font-semibold shadow-sm'
                                    : 'text-text-muted hover:text-white'"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                </svg>
                                <span>Masuk</span>
                            </Link>
                            <Link
                                :href="route('register')"
                                class="py-2.5 px-4 rounded-button font-medium text-sm transition-all flex items-center justify-center gap-2"
                                :class="activeTab === 'register'
                                    ? 'bg-brand-primary text-black font-semibold shadow-sm'
                                    : 'text-text-muted hover:text-white'"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                </svg>
                                <span>Daftar</span>
                            </Link>
                        </div>

                        <!-- Slot untuk Formulir -->
                        <slot />
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>
