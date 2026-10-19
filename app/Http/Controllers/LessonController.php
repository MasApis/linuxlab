<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Module;
use Inertia\Inertia;
use Inertia\Response;

class LessonController extends Controller
{
    /**
     * Menampilkan isi materi pelajaran, contoh perintah, dan cheat sheet (FR-MOD-02).
     */
    public function show(Module $module, Lesson $lesson): Response
    {
        abort_unless($module->is_published, 404);
        abort_unless($lesson->module_id === $module->id, 404);

        $moduleLessons = $module->lessons()
            ->orderBy('order')
            ->select(['id', 'module_id', 'title', 'slug', 'order'])
            ->get();

        $currentIndex = $moduleLessons->search(fn ($item) => $item->id === $lesson->id);

        $previousLesson = $currentIndex > 0 ? $moduleLessons->get($currentIndex - 1) : null;
        $nextLesson = ($currentIndex !== false && $currentIndex < $moduleLessons->count() - 1)
            ? $moduleLessons->get($currentIndex + 1)
            : null;

        return Inertia::render('Lessons/Show', [
            'module' => [
                'id' => $module->id,
                'title' => $module->title,
                'slug' => $module->slug,
            ],
            'lesson' => [
                'id' => $lesson->id,
                'title' => $lesson->title,
                'slug' => $lesson->slug,
                'content' => $lesson->content,
                'example_commands' => $lesson->example_commands,
                'cheat_sheet' => $lesson->cheat_sheet,
                'order' => $lesson->order,
            ],
            'moduleLessons' => $moduleLessons->map(fn ($item) => [
                'id' => $item->id,
                'title' => $item->title,
                'slug' => $item->slug,
                'order' => $item->order,
            ]),
            'previousLesson' => $previousLesson ? [
                'title' => $previousLesson->title,
                'slug' => $previousLesson->slug,
            ] : null,
            'nextLesson' => $nextLesson ? [
                'title' => $nextLesson->title,
                'slug' => $nextLesson->slug,
            ] : null,
        ]);
    }

    /**
     * Halaman placeholder praktik terminal pelajaran (FR-MOD-02 / Fase 2).
     * Terminal interaktif nyata dan isolasi container akan diaktifkan pada Fase 3 & Fase 5.
     */
    public function practice(Lesson $lesson): Response
    {
        $module = $lesson->module;
        abort_unless($module && $module->is_published, 404);

        return Inertia::render('Lessons/Practice', [
            'module' => [
                'id' => $module->id,
                'title' => $module->title,
                'slug' => $module->slug,
            ],
            'lesson' => [
                'id' => $lesson->id,
                'title' => $lesson->title,
                'slug' => $lesson->slug,
                'order' => $lesson->order,
            ],
        ]);
    }
}
