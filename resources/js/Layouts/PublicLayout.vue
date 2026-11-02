<script setup lang="ts">
import { ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import AppLogo from '@/Components/AppLogo.vue';
import AppButton from '@/Components/AppButton.vue';

const page = usePage();
const mobileMenuOpen = ref(false);

const navLinks = [
    { name: 'Beranda', href: '/' },
    { name: 'Modul', href: '/modules' },
    { name: 'Leaderboard', href: '/leaderboard' },
    { name: 'Verifikasi Sertifikat', href: '/certificates/verify' },
];
</script>

<template>
    <div class="min-h-screen bg-surface-base text-text-primary flex flex-col font-sans">
        <header class="sticky top-0 z-40 bg-surface-card border-b-2 border-border">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 min-h-16 flex items-center justify-between gap-3">
                <AppLogo size="md" href="/" />

                <nav class="hidden lg:flex items-center gap-2" aria-label="Navigasi utama">
                    <Link
                        v-for="link in navLinks"
                        :key="link.name"
                        :href="link.href"
                        class="px-3 py-1.5 min-h-11 inline-flex items-center justify-center text-sm font-medium rounded-button transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                        :class="[
                            page.url === link.href || (link.href !== '/' && page.url.startsWith(link.href))
                                ? 'text-brand-primary-light bg-surface-high'
                                : 'text-text-muted hover:text-text-primary hover:bg-surface-high',
                        ]"
                    >
                        {{ link.name }}
                    </Link>
                </nav>

                <div class="hidden lg:flex items-center gap-3">
                    <AppButton
                        v-if="page.props.auth?.user"
                        variant="secondary"
                        size="sm"
                        href="/dashboard"
                    >
                        Dashboard
                    </AppButton>
                    <template v-else>
                        <AppButton variant="ghost" size="sm" href="/login">Masuk</AppButton>
                        <AppButton variant="primary" size="sm" href="/register">Daftar sekarang</AppButton>
                    </template>
                </div>

                <div class="flex lg:hidden">
                    <button
                        type="button"
                        @click="mobileMenuOpen = !mobileMenuOpen"
                        class="p-2 min-h-11 min-w-11 inline-flex items-center justify-center rounded-button text-text-muted hover:text-text-primary hover:bg-surface-high focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                        :aria-expanded="mobileMenuOpen"
                        aria-controls="public-mobile-menu"
                        :aria-label="mobileMenuOpen ? 'Tutup menu' : 'Buka menu'"
                    >
                        <svg v-if="!mobileMenuOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg v-else class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <div
                v-if="mobileMenuOpen"
                id="public-mobile-menu"
                class="lg:hidden border-b border-border bg-surface-card px-4 pt-2 pb-4 space-y-1"
            >
                <Link
                    v-for="link in navLinks"
                    :key="link.name"
                    :href="link.href"
                    class="min-h-11 flex items-center px-3 py-2 text-sm font-medium rounded-button focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                    :class="[
                        page.url === link.href || (link.href !== '/' && page.url.startsWith(link.href))
                            ? 'text-brand-primary-light bg-surface-high'
                            : 'text-text-muted hover:text-text-primary hover:bg-surface-high',
                    ]"
                    @click="mobileMenuOpen = false"
                >
                    {{ link.name }}
                </Link>

                <div class="pt-3 border-t border-border flex flex-col gap-2">
                    <AppButton
                        v-if="page.props.auth?.user"
                        variant="secondary"
                        size="md"
                        href="/dashboard"
                        class="w-full"
                        @click="mobileMenuOpen = false"
                    >
                        Buka Dashboard
                    </AppButton>
                    <template v-else>
                        <AppButton variant="secondary" size="md" href="/login" class="w-full" @click="mobileMenuOpen = false">
                            Masuk
                        </AppButton>
                        <AppButton variant="primary" size="md" href="/register" class="w-full" @click="mobileMenuOpen = false">
                            Daftar sekarang
                        </AppButton>
                    </template>
                </div>
            </div>
        </header>

        <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <slot />
        </main>

        <footer class="border-t-2 border-border bg-surface-card text-text-muted text-xs py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2 text-center sm:text-left">
                    <span class="font-bold text-text-primary">LinuxLab</span>
                    <span class="text-text-dim">Platform belajar Linux interaktif berbasis web</span>
                </div>
                <nav class="flex flex-wrap justify-center gap-x-5 gap-y-2 text-text-dim" aria-label="Navigasi footer">
                    <Link href="/" class="min-h-11 flex items-center hover:text-text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary rounded-button">Beranda</Link>
                    <Link href="/modules" class="min-h-11 flex items-center hover:text-text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary rounded-button">Modul</Link>
                    <Link href="/leaderboard" class="min-h-11 flex items-center hover:text-text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary rounded-button">Leaderboard</Link>
                    <Link href="/certificates/verify" class="min-h-11 flex items-center hover:text-text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary rounded-button">Verifikasi sertifikat</Link>
                </nav>
            </div>
        </footer>
    </div>
</template>
