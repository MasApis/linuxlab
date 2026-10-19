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
];
</script>

<template>
    <div class="min-h-screen bg-surface-base text-text-primary flex flex-col font-sans">
        <!-- Top Navbar -->
        <header class="sticky top-0 z-40 bg-surface-base/90 backdrop-blur-md border-b border-border">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <!-- Brand / Logo -->
                <div class="flex items-center gap-8">
                    <AppLogo size="md" href="/" />

                    <!-- Desktop Nav Links -->
                    <nav class="hidden md:flex items-center gap-1">
                        <Link
                            v-for="link in navLinks"
                            :key="link.name"
                            :href="link.href"
                            class="px-3 py-1.5 text-sm font-medium rounded-button transition-colors"
                            :class="[
                                page.url === link.href || (link.href !== '/' && page.url.startsWith(link.href))
                                    ? 'text-brand-primary bg-surface-elevated'
                                    : 'text-text-muted hover:text-text-primary hover:bg-surface-card',
                            ]"
                        >
                            {{ link.name }}
                        </Link>
                    </nav>
                </div>

                <!-- Right Actions -->
                <div class="hidden sm:flex items-center gap-3">
                    <template v-if="page.props.auth?.user">
                        <AppButton
                            variant="secondary"
                            size="sm"
                            href="/dashboard"
                        >
                            Dashboard
                        </AppButton>
                    </template>
                    <template v-else>
                        <AppButton
                            variant="ghost"
                            size="sm"
                            href="/login"
                        >
                            Masuk
                        </AppButton>
                        <AppButton
                            variant="primary"
                            size="sm"
                            href="/register"
                        >
                            Daftar Sekarang
                        </AppButton>
                    </template>
                </div>

                <!-- Mobile Menu Button -->
                <div class="flex sm:hidden">
                    <button
                        type="button"
                        @click="mobileMenuOpen = !mobileMenuOpen"
                        class="p-2 rounded-button text-text-muted hover:text-text-primary hover:bg-surface-elevated focus:outline-none"
                        aria-label="Toggle menu"
                    >
                        <svg
                            v-if="!mobileMenuOpen"
                            class="h-6 w-6"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg
                            v-else
                            class="h-6 w-6"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Dropdown Menu -->
            <div
                v-if="mobileMenuOpen"
                class="sm:hidden border-b border-border bg-surface-card px-4 pt-2 pb-4 space-y-1"
            >
                <Link
                    v-for="link in navLinks"
                    :key="link.name"
                    :href="link.href"
                    class="block px-3 py-2 text-sm font-medium rounded-button"
                    :class="[
                        page.url === link.href || (link.href !== '/' && page.url.startsWith(link.href))
                            ? 'text-brand-primary bg-surface-elevated'
                            : 'text-text-muted hover:text-text-primary hover:bg-surface-elevated',
                    ]"
                    @click="mobileMenuOpen = false"
                >
                    {{ link.name }}
                </Link>

                <div class="pt-3 border-t border-border flex flex-col gap-2">
                    <template v-if="page.props.auth?.user">
                        <AppButton variant="primary" size="md" href="/dashboard" class="w-full">
                            Buka Dashboard
                        </AppButton>
                    </template>
                    <template v-else>
                        <AppButton variant="secondary" size="md" href="/login" class="w-full">
                            Masuk
                        </AppButton>
                        <AppButton variant="primary" size="md" href="/register" class="w-full">
                            Daftar Sekarang
                        </AppButton>
                    </template>
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <slot />
        </main>

        <!-- Footer -->
        <footer class="border-t border-border bg-surface-card/50 text-text-muted text-xs py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <span class="font-bold text-text-primary">LinuxLab</span>
                    <span class="text-text-dim">— Platform Belajar Linux Interaktif Berbasis Web</span>
                </div>
                <div class="flex items-center gap-6 text-text-dim">
                    <Link href="/" class="hover:text-text-primary transition-colors">Beranda</Link>
                    <Link href="/modules" class="hover:text-text-primary transition-colors">Modul</Link>
                    <Link href="/leaderboard" class="hover:text-text-primary transition-colors">Leaderboard</Link>
                </div>
            </div>
        </footer>
    </div>
</template>
