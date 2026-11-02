<script setup lang="ts">
import { ref } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import AppLogo from '@/Components/AppLogo.vue';

const page = usePage();
const mobileMenuOpen = ref(false);
const userDropdownOpen = ref(false);

const user = page.props.auth?.user as { name?: string; email?: string; role?: string } | undefined;

const navLinks = [
    { name: 'Dashboard', href: '/dashboard' },
    { name: 'Modul', href: '/modules' },
    { name: 'Progres', href: '/progress' },
    { name: 'Pencapaian', href: '/achievements' },
    { name: 'Forum', href: '/forum' },
    { name: 'Leaderboard', href: '/leaderboard' },
];

const logout = () => {
    router.post('/logout');
};

// Penanda navigasi aktif: cocok persis/awalan, plus kasus khusus
// - Forum aktif di /forum/* dan /lessons/*/forum (tujuan redirect /forum)
// - Modul aktif di materi & praktik (/modules/*, /lessons/*) kecuali halaman forum
const isLinkActive = (link: { name: string; href: string }) => {
    const url = page.url;
    if (url === link.href) return true;
    const isForumPage = /\/forum(\/|$)/.test(url);
    if (link.name === 'Forum') return isForumPage;
    if (link.name === 'Modul' && url.startsWith('/lessons/') && !isForumPage) return true;
    if (link.href !== '/' && url.startsWith(link.href)) return true;
    return false;
};
</script>

<template>
    <div class="min-h-screen bg-surface-base text-text-primary flex flex-col font-sans">
        <!-- Top Navbar -->
        <header class="sticky top-0 z-40 bg-surface-card/95 backdrop-blur-md border-b-2 border-border">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <!-- Brand / Logo & Main Nav -->
                <div class="flex items-center gap-6 lg:gap-8">
                    <AppLogo size="md" href="/dashboard" />

                    <!-- Desktop Nav Links -->
                    <nav class="hidden md:flex items-center gap-1">
                        <Link
                            v-for="link in navLinks"
                            :key="link.name"
                            :href="link.href"
                            class="px-3 py-1.5 min-h-11 inline-flex items-center justify-center text-sm font-medium rounded-button transition-colors"
                            :class="[
                                isLinkActive(link)
                                    ? 'text-brand-primary-light bg-surface-high font-semibold'
                                    : 'text-text-muted hover:text-text-primary hover:bg-surface-card',
                            ]"
                        >
                            {{ link.name }}
                        </Link>
                    </nav>
                </div>

                <!-- Right Side User Menu -->
                <div class="hidden sm:flex items-center gap-4">
                    <!-- User Profile Dropdown -->
                    <div class="relative">
                        <button
                            type="button"
                            @click="userDropdownOpen = !userDropdownOpen"
                            class="flex items-center gap-2.5 px-3 py-1.5 rounded-button bg-surface-card border border-border text-sm hover:border-border-focus focus:outline-none transition-colors"
                        >
                            <div class="w-6 h-6 rounded-full bg-brand-primary/20 text-brand-primary font-mono font-bold text-xs flex items-center justify-center border border-brand-primary/40">
                                {{ user?.name ? user.name.charAt(0).toUpperCase() : 'U' }}
                            </div>
                            <span class="font-medium text-text-primary text-xs max-w-[120px] truncate">
                                {{ user?.name || 'Pengguna' }}
                            </span>
                            <svg class="w-4 h-4 text-text-dim transition-transform duration-150" :class="{ 'rotate-180': userDropdownOpen }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div
                            v-if="userDropdownOpen"
                            @click="userDropdownOpen = false"
                            class="absolute right-0 mt-2 w-48 rounded-card bg-surface-card border border-border shadow-xl py-1.5 z-50 animate-in fade-in zoom-in-95 duration-100"
                        >
                            <div class="px-3 py-2 border-b border-border/60">
                                <p class="text-xs font-semibold text-text-primary truncate">{{ user?.name }}</p>
                                <p class="text-[11px] text-text-dim truncate">{{ user?.email }}</p>
                            </div>

                            <Link
                                v-if="user?.role === 'admin'"
                                href="/admin"
                                class="flex items-center gap-2 px-3 py-2 text-xs text-brand-primary font-medium hover:bg-surface-elevated transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span>Panel Admin</span>
                            </Link>

                            <Link
                                href="/profile"
                                class="flex items-center gap-2 px-3 py-2 text-xs text-text-muted hover:text-text-primary hover:bg-surface-elevated transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span>Profil Akun</span>
                            </Link>

                            <button
                                type="button"
                                @click="logout"
                                class="w-full text-left flex items-center gap-2 px-3 py-2 text-xs text-brand-danger hover:bg-surface-elevated transition-colors border-t border-border/60"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                <span>Keluar</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Mobile Menu Button -->
                <div class="flex sm:hidden">
                    <button
                        type="button"
                        @click="mobileMenuOpen = !mobileMenuOpen"
                        class="p-2 rounded-button text-text-muted hover:text-text-primary hover:bg-surface-elevated focus:outline-none"
                        aria-label="Buka atau tutup menu"
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

            <!-- Mobile Navigation Menu -->
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
                        isLinkActive(link)
                            ? 'text-brand-primary-light bg-surface-high'
                            : 'text-text-muted hover:text-text-primary hover:bg-surface-elevated',
                    ]"
                    @click="mobileMenuOpen = false"
                >
                    {{ link.name }}
                </Link>

                <div class="pt-3 border-t border-border flex flex-col gap-1 text-sm">
                    <div class="px-3 py-2 text-xs text-text-dim">
                        Masuk sebagai <span class="text-text-primary font-semibold">{{ user?.name || 'Pengguna' }}</span>
                    </div>

                    <Link
                        v-if="user?.role === 'admin'"
                        href="/admin"
                        class="block px-3 py-2 text-xs text-brand-primary font-medium rounded-button hover:bg-surface-elevated"
                        @click="mobileMenuOpen = false"
                    >
                        Panel Admin
                    </Link>

                    <Link
                        href="/profile"
                        class="block px-3 py-2 text-xs text-text-muted hover:text-text-primary rounded-button hover:bg-surface-elevated"
                        @click="mobileMenuOpen = false"
                    >
                        Profil Akun
                    </Link>

                    <button
                        type="button"
                        @click="logout"
                        class="w-full text-left px-3 py-2 text-xs text-brand-danger hover:bg-surface-elevated rounded-button"
                    >
                        Keluar
                    </button>
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <slot />
        </main>

        <!-- Footer -->
        <footer class="border-t-2 border-border bg-surface-card text-text-muted text-xs py-6 mt-auto">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <span class="font-bold text-text-primary">LinuxLab</span>
                    <span class="text-text-dim">— Platform Belajar Linux Interaktif</span>
                </div>
                <div class="text-text-dim">
                    Semua sesi terminal berjalan di container terisolasi.
                </div>
            </div>
        </footer>
    </div>
</template>
