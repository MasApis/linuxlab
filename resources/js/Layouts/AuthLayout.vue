<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogo from '@/Components/AppLogo.vue';

defineProps<{
    activeTab?: 'login' | 'register';
    hideTabs?: boolean;
}>();
</script>

<template>
    <div class="min-h-screen bg-surface-base text-text-primary flex flex-col selection:bg-brand-primary selection:text-white">
        <header class="w-full min-h-16 border-b border-border bg-surface-base px-4 sm:px-6 lg:px-10 flex items-center justify-between gap-4">
            <AppLogo href="/" size="md" />
            <Link
                href="/"
                class="inline-flex min-h-11 items-center gap-2 text-sm font-extrabold text-text-muted hover:text-brand-primary-light focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary rounded-button"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali ke Beranda</span>
            </Link>
        </header>

        <main class="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-10 py-8 lg:py-10 flex items-center">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 w-full items-start">
                <section class="lg:col-span-6 flex flex-col gap-5" aria-labelledby="auth-heading">
                    <div class="inline-flex w-max items-center rounded-xl bg-[#D6EEFF] px-3 py-1.5 text-xs font-extrabold tracking-wide text-[#095F8F]">
                        TERMINAL 100% ONLINE DI BROWSER
                    </div>
                    <div class="space-y-3">
                        <h1 id="auth-heading" class="font-display text-3xl sm:text-4xl lg:text-[38px] font-extrabold tracking-tight text-ink leading-[1.15]">
                            Belajar Linux tanpa perlu install apa pun
                        </h1>
                        <p class="text-base text-text-secondary leading-relaxed max-w-xl">
                            Mulai dari perintah dasar hingga mahir mengelola sistem Linux melalui browser. Interaktif, terstruktur, dengan terminal container nyata dan penilaian otomatis.
                        </p>
                    </div>

                    <div class="rounded-panel overflow-hidden bg-ink border border-ink shadow-xl">
                        <div class="flex items-center gap-2 px-5 h-[52px]">
                            <span class="w-3 h-3 rounded-full bg-[#FF5F57]"></span>
                            <span class="w-3 h-3 rounded-full bg-[#FEBC2E]"></span>
                            <span class="w-3 h-3 rounded-full bg-[#28C840]"></span>
                            <span class="flex-1"></span>
                            <span class="font-mono text-xs text-white/50">bash — guest@linuxlab: ~</span>
                        </div>
                        <div class="px-6 pt-5 pb-6 space-y-2.5 font-mono text-xs sm:text-[13px] leading-relaxed">
                            <p class="text-[#58cc02]">$ curl -s https://linuxlab.id/init.sh | bash</p>
                            <p class="text-white/90">[✓] Mengalokasikan container Linux terisolasi...</p>
                            <p class="text-white/90">[✓] Menyiapkan modul &amp; lab interaktif</p>
                            <p class="text-white/90">[✓] Lingkungan aman: tidak memengaruhi OS lokal</p>
                            <p class="text-white/90">$ cat &lt;&lt; 'EOF'</p>
                            <p class="text-white/50">&gt; "Perjalanan ribuan baris perintah dimulai dari satu langkah kecil."</p>
                            <p class="text-white/90">$ linuxlab --status</p>
                            <p class="text-[#58cc02]">Sandbox Aktif</p>
                        </div>
                    </div>
                </section>

                <section class="lg:col-span-6 flex justify-center" aria-label="Formulir autentikasi">
                    <div class="w-full max-w-md bg-surface-card border border-border rounded-panel p-5 sm:p-7 lg:p-8">
                        <div v-if="!hideTabs" class="grid grid-cols-2 p-1 bg-surface-high rounded-card mb-6 border border-border" aria-label="Pilih masuk atau daftar">
                            <Link
                                :href="route('login')"
                                :aria-current="activeTab === 'login' ? 'page' : undefined"
                                class="min-h-11 px-3 rounded-button font-display font-bold text-sm transition-colors flex items-center justify-center focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                                :class="activeTab === 'login' ? 'bg-brand-primary text-white shadow-[0_2px_0_0_#245700]' : 'text-text-muted hover:text-text-primary'"
                            >
                                Masuk
                            </Link>
                            <Link
                                :href="route('register')"
                                :aria-current="activeTab === 'register' ? 'page' : undefined"
                                class="min-h-11 px-3 rounded-button font-display font-bold text-sm transition-colors flex items-center justify-center focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                                :class="activeTab === 'register' ? 'bg-brand-primary text-white shadow-[0_2px_0_0_#245700]' : 'text-text-muted hover:text-text-primary'"
                            >
                                Daftar
                            </Link>
                        </div>
                        <slot />
                    </div>
                </section>
            </div>
        </main>
    </div>
</template>
