<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';
import AppCard from '@/Components/AppCard.vue';
import AppButton from '@/Components/AppButton.vue';
import type { Lesson, Module } from '@/types';

interface SimpleNavLesson {
    id: number;
    title: string;
    slug: string;
    order: number;
    is_completed?: boolean;
}

interface Props {
    module: {
        id: number;
        title: string;
        slug: string;
    };
    lesson: Lesson;
    moduleLessons: SimpleNavLesson[];
    previousLesson?: { title: string; slug: string } | null;
    nextLesson?: { title: string; slug: string } | null;
}

const props = defineProps<Props>();

const copiedCommands = ref(false);

const copyExampleCommands = async () => {
    if (!props.lesson.example_commands) return;
    try {
        await navigator.clipboard.writeText(props.lesson.example_commands);
        copiedCommands.value = true;
        setTimeout(() => {
            copiedCommands.value = false;
        }, 2000);
    } catch {
        // Fallback jika clipboard API gagal
        copiedCommands.value = false;
    }
};

/**
 * Format markdown sederhana dan aman untuk teks materi pelajaran.
 * Menghandle heading (#, ##, ###), bold (**text**), inline code (`code`),
 * code block (``` ... ```), unordered list (- ), blockquote (>), hr (---), dan table sederhana.
 */
const formattedContent = computed(() => {
    const raw = props.lesson.content || '';
    const lines = raw.split('\n');
    let html = '';
    let inCodeBlock = false;
    let codeBlockContent = '';
    let inList = false;
    let inTable = false;

    const escapeHtml = (str: string) =>
        str
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');

    const parseInline = (text: string) => {
        let escaped = escapeHtml(text);
        // Inline code `code`
        escaped = escaped.replace(/`([^`]+)`/g, '<code class="px-1.5 py-0.5 rounded bg-surface-base text-brand-primary-light font-mono text-xs border border-border/80">$1</code>');
        // Bold **text**
        escaped = escaped.replace(/\*\*([^*]+)\*\*/g, '<strong class="text-ink font-semibold">$1</strong>');
        // Italic *text*
        escaped = escaped.replace(/\*([^*]+)\*/g, '<em class="text-text-primary">$1</em>');
        return escaped;
    };

    for (let i = 0; i < lines.length; i++) {
        const line = lines[i];

        // Code block toggle ```
        if (line.trim().startsWith('```')) {
            if (inCodeBlock) {
                html += `<pre class="my-4 p-4 rounded-xl bg-ink border border-ink overflow-x-auto font-mono text-xs text-[#58cc02] leading-relaxed"><code>${escapeHtml(codeBlockContent.trim())}</code></pre>`;
                codeBlockContent = '';
                inCodeBlock = false;
            } else {
                inCodeBlock = true;
            }
            continue;
        }

        if (inCodeBlock) {
            codeBlockContent += line + '\n';
            continue;
        }

        // Close list if line is not a list item
        if (inList && !line.trim().startsWith('- ') && !line.trim().startsWith('* ')) {
            html += '</ul>';
            inList = false;
        }

        // Horizontal rule
        if (line.trim() === '---') {
            html += '<hr class="my-6 border-border/60" />';
            continue;
        }

        // Headings
        if (line.startsWith('# ')) {
            html += `<h1 class="font-display text-2xl sm:text-3xl font-bold text-ink mt-6 mb-3 tracking-tight">${parseInline(line.substring(2))}</h1>`;
            continue;
        }
        if (line.startsWith('## ')) {
            html += `<h2 class="font-display text-xl sm:text-2xl font-bold text-ink mt-6 mb-3 flex items-center gap-2 border-b border-border pb-2">${parseInline(line.substring(3))}</h2>`;
            continue;
        }
        if (line.startsWith('### ')) {
            html += `<h3 class="text-lg font-semibold text-brand-primary-light mt-4 mb-2">${parseInline(line.substring(4))}</h3>`;
            continue;
        }

        // Blockquotes
        if (line.startsWith('> ')) {
            html += `<blockquote class="my-3 pl-4 py-2 border-l-4 border-brand-primary bg-surface-elevated/40 text-text-muted text-sm rounded-r">${parseInline(line.substring(2))}</blockquote>`;
            continue;
        }

        // Unordered list item
        if (line.trim().startsWith('- ') || line.trim().startsWith('* ')) {
            if (!inList) {
                html += '<ul class="list-disc list-outside pl-5 my-3 space-y-1 text-sm text-text-muted">';
                inList = true;
            }
            const itemText = line.trim().substring(2);
            html += `<li>${parseInline(itemText)}</li>`;
            continue;
        }

        // Table row
        if (line.trim().startsWith('|') && line.trim().endsWith('|')) {
            const cells = line
                .split('|')
                .slice(1, -1)
                .map((c) => c.trim());
            // Header separator row like | :---: | :---: |
            if (cells.every((c) => /^:?-+:?$/.test(c))) {
                continue;
            }

            if (!inTable) {
                html += '<div class="overflow-x-auto my-4"><table class="w-full text-left text-xs border border-border divide-y divide-border rounded-lg overflow-hidden"><tbody class="divide-y divide-border/60">';
                inTable = true;
            }
            html += '<tr class="hover:bg-surface-elevated/50 transition-colors">';
            cells.forEach((cell) => {
                html += `<td class="p-2.5 text-text-muted">${parseInline(cell)}</td>`;
            });
            html += '</tr>';
            continue;
        } else if (inTable) {
            html += '</tbody></table></div>';
            inTable = false;
        }

        // Empty line
        if (line.trim() === '') {
            continue;
        }

        // Regular paragraph
        html += `<p class="my-3 text-sm sm:text-base text-text-muted leading-relaxed">${parseInline(line)}</p>`;
    }

    if (inList) html += '</ul>';
    if (inTable) html += '</tbody></table></div>';
    if (inCodeBlock) {
        html += `<pre class="my-4 p-4 rounded-xl bg-surface-base border border-border overflow-x-auto font-mono text-xs text-brand-primary-light"><code>${escapeHtml(codeBlockContent.trim())}</code></pre>`;
    }

    return html;
});
</script>

<template>
    <UserLayout>
        <Head :title="`${lesson.title} - ${module.title} - LinuxLab`" />

        <div class="py-6 space-y-6">
            <!-- Breadcrumbs -->
            <nav class="flex items-center gap-2 text-xs sm:text-sm text-text-muted font-medium overflow-x-auto whitespace-nowrap">
                <Link :href="route('dashboard')" class="hover:text-brand-primary-light transition-colors flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Beranda</span>
                </Link>
                <span class="text-text-dim">/</span>
                <Link :href="route('modules.index')" class="hover:text-brand-primary-light transition-colors">
                    Modul
                </Link>
                <span class="text-text-dim">/</span>
                <Link :href="route('modules.show', module.slug)" class="hover:text-brand-primary-light transition-colors">
                    {{ module.title }}
                </Link>
                <span class="text-text-dim">/</span>
                <span class="text-ink font-semibold truncate">{{ lesson.title }}</span>
            </nav>

            <!-- Main Content Area: 2 Columns Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                <!-- Left Column: Lesson Content (8 cols) -->
                <article class="lg:col-span-8 space-y-6">
                    <!-- Lesson Header Card -->
                    <AppCard class="border-border">
                        <div class="space-y-3">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-xs font-mono font-bold px-2.5 py-0.5 rounded bg-brand-primary/10 text-brand-primary-light border border-brand-primary/30">
                                    PELAJARAN 0{{ lesson.order }}
                                </span>
                                <span class="text-xs font-mono text-text-dim">
                                    • {{ module.title }}
                                </span>
                            </div>

                            <h1 class="font-display text-2xl sm:text-3xl font-bold text-ink tracking-tight">
                                {{ lesson.title }}
                            </h1>
                        </div>
                    </AppCard>

                    <!-- Rendered Lesson Body (Markdown) -->
                    <AppCard class="border-border max-w-none">
                        <!-- eslint-disable-next-line vue/no-v-html -->
                        <div class="text-text-primary" v-html="formattedContent"></div>
                    </AppCard>

                    <!-- Example Commands Block (lessons.example_commands) -->
                    <div v-if="lesson.example_commands" class="space-y-3">
                        <div class="flex items-center justify-between">
                            <h3 class="font-display text-lg font-bold text-ink flex items-center gap-2">
                                <svg class="w-5 h-5 text-brand-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>Contoh Perintah Terminal</span>
                            </h3>
                            <button
                                type="button"
                                @click="copyExampleCommands"
                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded bg-surface-card hover:bg-surface-high border border-border text-xs text-text-muted hover:text-text-primary transition-colors"
                            >
                                <svg v-if="!copiedCommands" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                                </svg>
                                <svg v-else class="w-3.5 h-3.5 text-brand-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>{{ copiedCommands ? 'Tersalin!' : 'Salin Perintah' }}</span>
                            </button>
                        </div>

                        <!-- Stylized Terminal Output Window -->
                        <div class="rounded-xl overflow-hidden bg-ink border border-ink shadow-lg">
                            <div class="bg-white/5 px-4 py-2 border-b border-white/10 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-2.5 h-2.5 rounded-full bg-red-500/80"></span>
                                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500/80"></span>
                                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500/80"></span>
                                    </div>
                                    <span class="font-mono text-xs text-text-dim ml-2">bash terminal — simulasi interaktif</span>
                                </div>
                            </div>
                            <pre class="p-4 font-mono text-xs sm:text-sm text-white/90 overflow-x-auto leading-relaxed whitespace-pre"><code>{{ lesson.example_commands }}</code></pre>
                        </div>
                    </div>

                    <!-- Latihan Praktik Terikat Pelajaran Ini (FR-EX-01, FR-PROG-01) -->
                    <AppCard class="border-border space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-border/60">
                            <div>
                                <h3 class="font-display text-lg font-bold text-ink flex items-center gap-2">
                                    <svg class="w-5 h-5 text-brand-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                    </svg>
                                    <span>Latihan Praktik Terikat</span>
                                </h3>
                                <p class="text-xs text-text-muted mt-0.5">
                                    Selesaikan seluruh latihan di terminal untuk menandai pelajaran ini 100% selesai.
                                </p>
                            </div>
                            <span
                                class="text-xs font-mono font-semibold px-2.5 py-1 rounded-full self-start sm:self-auto border"
                                :class="lesson.is_completed ? 'bg-brand-primary/15 text-brand-primary-light border border-brand-primary/30' : 'bg-surface-high text-brand-secondary-light border border-brand-secondary/30'"
                            >
                                {{ lesson.is_completed ? 'Pelajaran Selesai 100%' : 'Latihan Belum Tuntas' }}
                            </span>
                        </div>

                        <div v-if="lesson.exercises && lesson.exercises.length > 0" class="space-y-2.5">
                            <div
                                v-for="(ex, index) in lesson.exercises"
                                :key="ex.id"
                                class="p-3 rounded-lg border flex items-center justify-between gap-4 transition-colors"
                                :class="ex.has_passed ? 'bg-brand-primary/5 border-brand-primary/30' : 'bg-surface-base border-border'"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-7 h-7 rounded-full flex items-center justify-center font-mono font-bold text-xs shrink-0 border"
                                        :class="ex.has_passed ? 'bg-brand-primary text-surface-base border-brand-primary' : 'bg-surface-elevated text-text-dim border-border'"
                                    >
                                        {{ ex.has_passed ? '✓' : index + 1 }}
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-semibold text-ink">{{ ex.title }}</h4>
                                        <p class="text-xs text-text-muted line-clamp-1">{{ ex.instruction }}</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3 shrink-0">
                                    <span class="text-xs font-mono text-text-dim">{{ ex.points }} poin</span>
                                    <span
                                        class="text-[11px] font-semibold px-2 py-0.5 rounded font-mono"
                                        :class="ex.has_passed ? 'bg-brand-primary/15 text-brand-primary-light' : 'bg-surface-high text-text-dim'"
                                    >
                                        {{ ex.has_passed ? 'Selesai' : 'Belum' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- CTA to Practice -->
                        <div class="pt-2 flex justify-end">
                            <Link :href="route('lessons.practice', lesson.slug)">
                                <AppButton variant="primary" size="sm">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                    </svg>
                                    <span>Buka Terminal Praktik & Periksa Jawaban</span>
                                </AppButton>
                            </Link>
                        </div>
                    </AppCard>

                    <!-- Forum Discussion Link Card (FR-FORUM-01 Preview) -->
                    <AppCard class="border-border bg-surface-card/60">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="space-y-1">
                                <h4 class="font-semibold text-ink flex items-center gap-2">
                                    <svg class="w-4 h-4 text-brand-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                    </svg>
                                    <span>Diskusi & Forum Pelajaran</span>
                                </h4>
                                <p class="text-xs text-text-muted">
                                    Ada pertanyaan seputar {{ lesson.title }}? Ajukan dan diskusikan bersama pembelajar lain.
                                </p>
                            </div>
                            <Link :href="`/lessons/${lesson.slug}/forum`">
                                <AppButton variant="secondary" size="sm" class="whitespace-nowrap">
                                    Buka Forum Pelajaran
                                </AppButton>
                            </Link>
                        </div>
                    </AppCard>

                    <!-- Navigation Next/Previous Buttons -->
                    <div class="flex items-center justify-between pt-4 border-t border-border/60">
                        <div>
                            <Link
                                v-if="previousLesson"
                                :href="route('lessons.show', [module.slug, previousLesson.slug])"
                            >
                                <AppButton variant="secondary" size="sm">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                    </svg>
                                    <span>{{ previousLesson.title }}</span>
                                </AppButton>
                            </Link>
                        </div>

                        <div>
                            <Link
                                v-if="nextLesson"
                                :href="route('lessons.show', [module.slug, nextLesson.slug])"
                            >
                                <AppButton variant="primary" size="sm">
                                    <span>{{ nextLesson.title }}</span>
                                    <svg class="w-4 h-4 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </AppButton>
                            </Link>
                        </div>
                    </div>
                </article>

                <!-- Right Column: Sidebar (Cheat Sheet & Practice CTA) (4 cols) -->
                <aside class="lg:col-span-4 sticky top-20 space-y-6">
                    <!-- Primary Practice CTA Card -->
                    <AppCard class="border-brand-primary/40 bg-gradient-to-b from-brand-primary/10 to-surface-card p-5 space-y-4">
                        <div class="space-y-1">
                            <span class="text-xs font-mono font-bold text-brand-primary-light uppercase tracking-wider">
                                Latihan Mandiri
                            </span>
                            <h3 class="font-display text-lg font-bold text-ink">
                                Praktikkan di Terminal
                            </h3>
                            <p class="text-xs text-text-muted leading-relaxed">
                                Jalankan perintah nyata di container Linux terisolasi untuk menguji pemahaman materi ini.
                            </p>
                        </div>

                        <!-- FR-MOD-02: Mulai Praktik button points to practice route -->
                        <Link :href="route('lessons.practice', lesson.slug)" class="block">
                            <AppButton
                                variant="primary"
                                class="w-full justify-center shadow-lg shadow-brand-primary/10 py-3 font-semibold"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>Mulai Praktik</span>
                            </AppButton>
                        </Link>
                    </AppCard>

                    <!-- Cheatsheet Card (lessons.cheat_sheet) -->
                    <AppCard v-if="lesson.cheat_sheet" class="border-border space-y-3">
                        <div class="flex items-center justify-between pb-2 border-b border-border/60">
                            <h3 class="text-sm font-bold text-ink flex items-center gap-2">
                                <svg class="w-4 h-4 text-brand-tertiary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span>Cheat Sheet Cepat</span>
                            </h3>
                            <span class="text-[11px] font-mono text-brand-primary-light">
                                Ringkasan
                            </span>
                        </div>

                        <div class="space-y-2">
                            <pre class="p-3 rounded-lg bg-surface-high border border-border/80 font-mono text-xs text-text-primary whitespace-pre-wrap leading-relaxed"><code>{{ lesson.cheat_sheet }}</code></pre>
                        </div>
                    </AppCard>

                    <!-- Lesson Navigation in Module -->
                    <AppCard class="border-border space-y-3">
                        <h4 class="text-xs font-mono font-bold text-text-dim uppercase tracking-wider">
                            Daftar Pelajaran Modul Ini
                        </h4>

                        <div class="space-y-1.5 divide-y divide-border/40">
                            <Link
                                v-for="item in moduleLessons"
                                :key="item.id"
                                :href="route('lessons.show', [module.slug, item.slug])"
                                class="flex items-center justify-between py-2 px-2.5 rounded-lg text-xs transition-colors"
                                :class="[
                                    item.slug === lesson.slug
                                        ? 'bg-brand-primary/15 text-brand-primary-light font-semibold border border-brand-primary/30'
                                        : 'text-text-muted hover:text-text-primary hover:bg-surface-high',
                                ]"
                            >
                                <span class="truncate">0{{ item.order }}. {{ item.title }}</span>
                                <span v-if="item.is_completed" class="text-[10px] font-bold text-brand-primary-light ml-2 shrink-0">
                                    ✓ Selesai
                                </span>
                                <span v-else-if="item.slug === lesson.slug" class="text-[11px] font-semibold text-brand-secondary-light ml-2 shrink-0">
                                    Aktif
                                </span>
                            </Link>
                        </div>
                    </AppCard>
                </aside>
            </div>
        </div>
    </UserLayout>
</template>
