<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import AppLogo from '@/Components/AppLogo.vue';

const page = usePage();
const sidebarOpen = ref(false);

const user = page.props.auth?.user as { name?: string; email?: string } | undefined;
const flash = computed(() => (page.props.flash ?? {}) as { success?: string | null; error?: string | null });

const adminNavItems = [
    {
        name: 'Dashboard',
        href: '/admin',
        icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
    },
    {
        name: 'Modul',
        href: '/admin/modules',
        icon: 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
    },
    {
        name: 'Pelajaran',
        href: '/admin/lessons',
        icon: 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
    },
    {
        name: 'Latihan & Hint',
        href: '/admin/exercises',
        icon: 'M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
    },
    {
        name: 'Lencana',
        href: '/admin/badges',
        icon: 'M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7',
    },
    {
        name: 'Moderasi Forum',
        href: '/admin/forum',
        icon: 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
    },
    {
        name: 'Kelola Pengguna',
        href: '/admin/users',
        icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
    },
];

const logout = () => {
    router.post('/logout');
};
</script>

<template>
    <div class="min-h-screen bg-surface-base text-text-primary flex font-sans">
        <!-- Sidebar for Desktop -->
        <aside class="hidden lg:flex flex-col w-64 bg-surface-card border-r border-border shrink-0">
            <!-- Brand Header -->
            <div class="h-16 px-6 border-b border-border flex items-center justify-between">
                <AppLogo size="sm" href="/admin" />
                <span class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded-badge bg-brand-primary/15 text-brand-primary border border-brand-primary/30">
                    Admin
                </span>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                <Link
                    v-for="item in adminNavItems"
                    :key="item.name"
                    :href="item.href"
                    class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-button transition-colors"
                    :class="[
                        page.url === item.href || (item.href !== '/admin' && page.url.startsWith(item.href))
                            ? 'bg-surface-high text-brand-primary-light border-l-2 border-brand-primary font-semibold'
                            : 'text-text-muted hover:text-text-primary hover:bg-surface-elevated/60',
                    ]"
                >
                    <svg
                        class="w-5 h-5 shrink-0"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" :d="item.icon" />
                    </svg>
                    <span>{{ item.name }}</span>
                </Link>
            </nav>

            <!-- Bottom User & App Links -->
            <div class="p-3 border-t border-border space-y-1">
                <Link
                    href="/dashboard"
                    class="flex items-center gap-2.5 px-3 py-2 text-xs font-medium text-text-muted hover:text-text-primary hover:bg-surface-elevated rounded-button transition-colors"
                >
                    <svg class="w-4 h-4 text-brand-tertiary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Kembali ke Belajar</span>
                </Link>

                <button
                    type="button"
                    @click="logout"
                    class="w-full flex items-center gap-2.5 px-3 py-2 text-xs font-medium text-brand-danger hover:bg-surface-elevated rounded-button transition-colors text-left"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span>Keluar</span>
                </button>
            </div>
        </aside>

        <!-- Mobile Sidebar Drawer (Off-canvas) -->
        <div
            v-if="sidebarOpen"
            class="fixed inset-0 z-50 lg:hidden flex"
        >
            <!-- Overlay -->
            <div
                class="fixed inset-0 bg-black/60 backdrop-blur-xs"
                @click="sidebarOpen = false"
            ></div>

            <!-- Drawer Container -->
            <div class="relative w-64 bg-surface-card border-r border-border flex flex-col z-10">
                <div class="h-16 px-6 border-b border-border flex items-center justify-between">
                    <AppLogo size="sm" href="/admin" />
                    <button
                        type="button"
                        @click="sidebarOpen = false"
                        class="p-1 rounded-button text-text-muted hover:text-text-primary"
                    >
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                    <Link
                        v-for="item in adminNavItems"
                        :key="item.name"
                        :href="item.href"
                        class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-button"
                        :class="[
                            page.url === item.href || (item.href !== '/admin' && page.url.startsWith(item.href))
                                ? 'bg-surface-high text-brand-primary-light border-l-2 border-brand-primary font-semibold'
                                : 'text-text-muted hover:text-text-primary hover:bg-surface-elevated/60',
                        ]"
                        @click="sidebarOpen = false"
                    >
                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" :d="item.icon" />
                        </svg>
                        <span>{{ item.name }}</span>
                    </Link>
                </nav>

                <div class="p-3 border-t border-border space-y-1">
                    <Link
                        href="/dashboard"
                        class="flex items-center gap-2.5 px-3 py-2 text-xs font-medium text-text-muted hover:text-text-primary hover:bg-surface-elevated rounded-button"
                        @click="sidebarOpen = false"
                    >
                        <svg class="w-4 h-4 text-brand-tertiary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span>Kembali ke Belajar</span>
                    </Link>
                    <button
                        type="button"
                        @click="logout"
                        class="w-full flex items-center gap-2.5 px-3 py-2 text-xs font-medium text-brand-danger hover:bg-surface-elevated rounded-button text-left"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span>Keluar</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Right Side: Header + Main Area -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Top Header -->
            <header class="h-16 border-b-2 border-border bg-surface-card/95 backdrop-blur-md px-4 sm:px-6 lg:px-8 flex items-center justify-between sticky top-0 z-30">
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        @click="sidebarOpen = true"
                        class="p-2 rounded-button text-text-muted hover:text-text-primary hover:bg-surface-card lg:hidden"
                    >
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <div class="flex items-center gap-2">
                        <span class="text-xs uppercase font-mono tracking-wider text-text-dim">Admin Console</span>
                        <span class="text-text-dim text-xs">/</span>
                        <span class="text-xs font-semibold text-text-primary">LinuxLab</span>
                    </div>
                </div>

                <div class="flex items-center gap-4 text-xs">
                    <div class="hidden sm:flex items-center gap-2 text-text-dim">
                        <span>Admin:</span>
                        <span class="font-medium text-text-primary">{{ user?.name || 'Administrator' }}</span>
                    </div>

                    <Link
                        href="/dashboard"
                        class="px-2.5 py-1.5 rounded-button bg-surface-card border border-border text-text-muted hover:text-text-primary hover:border-border-focus transition-colors"
                    >
                        Lihat Aplikasi
                    </Link>
                </div>
            </header>

            <!-- Main Content Slot -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8 overflow-y-auto">
                <!-- Flash Notification Banner -->
                <div
                    v-if="flash.success"
                    class="mb-6 p-4 rounded-card bg-brand-primary/10 border border-brand-primary/30 text-brand-primary-light flex items-center justify-between gap-3 text-sm animate-fade-in"
                >
                    <div class="flex items-center gap-2.5">
                        <svg class="w-5 h-5 shrink-0 text-brand-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>{{ flash.success }}</span>
                    </div>
                </div>

                <div
                    v-if="flash.error"
                    class="mb-6 p-4 rounded-card bg-brand-danger/10 border border-brand-danger/30 text-brand-danger-light flex items-center justify-between gap-3 text-sm animate-fade-in"
                >
                    <div class="flex items-center gap-2.5">
                        <svg class="w-5 h-5 shrink-0 text-brand-danger" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>{{ flash.error }}</span>
                    </div>
                </div>

                <slot />
            </main>
        </div>
    </div>
</template>
