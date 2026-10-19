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

        $userId = \Illuminate\Support\Facades\Auth::id();

        // Tandai in_progress jika pengguna login dan belum mulai
        if ($userId) {
            \App\Models\LessonProgress::firstOrCreate(
                ['user_id' => $userId, 'lesson_id' => $lesson->id],
                ['status' => 'in_progress']
            );
            \App\Models\ModuleProgress::firstOrCreate(
                ['user_id' => $userId, 'module_id' => $module->id],
                ['status' => 'in_progress', 'completion_percentage' => 0]
            );
        }

        $passedExerciseIds = $userId ? \App\Models\ExerciseAttempt::query()
            ->where('user_id', $userId)
            ->whereIn('exercise_id', $lesson->exercises()->pluck('id'))
            ->where('is_correct', true)
            ->pluck('exercise_id')
            ->all() : [];

        $exercises = $lesson->exercises()
            ->orderBy('order')
            ->get(['id', 'title', 'instruction', 'points', 'order'])
            ->map(fn ($ex) => [
                'id' => $ex->id,
                'title' => $ex->title,
                'instruction' => $ex->instruction,
                'points' => $ex->points,
                'order' => $ex->order,
                'has_passed' => in_array($ex->id, $passedExerciseIds, true),
            ]);

        $lessonProgress = $userId ? \App\Models\LessonProgress::where('user_id', $userId)->where('lesson_id', $lesson->id)->first() : null;

        $completedLessonIds = $userId ? \App\Models\LessonProgress::query()
            ->where('user_id', $userId)
            ->whereIn('lesson_id', $moduleLessons->pluck('id'))
            ->where('status', 'completed')
            ->pluck('lesson_id')
            ->all() : [];

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
                'status' => $lessonProgress ? $lessonProgress->status : 'not_started',
                'is_completed' => $lessonProgress ? ($lessonProgress->status === 'completed') : false,
                'exercises' => $exercises,
            ],
            'moduleLessons' => $moduleLessons->map(fn ($item) => [
                'id' => $item->id,
                'title' => $item->title,
                'slug' => $item->slug,
                'order' => $item->order,
                'is_completed' => in_array($item->id, $completedLessonIds, true),
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
     * Halaman praktik terminal interaktif pelajaran (FR-MOD-02, FR-TERM-01 s.d. 05, FR-EX-01 s.d. 04).
     */
    public function practice(Lesson $lesson): Response
    {
        $module = $lesson->module;
        abort_unless($module && $module->is_published, 404);

        $userId = \Illuminate\Support\Facades\Auth::id();

        /** @var \App\Models\TerminalSession|null $activeSession */
        $activeSession = \App\Models\TerminalSession::query()
            ->where('user_id', $userId)
            ->active()
            ->first();

        $activeSessionData = null;
        if ($activeSession) {
            $now = \Carbon\Carbon::now();
            $activeSessionData = [
                'id' => $activeSession->id,
                'status' => $activeSession->status,
                'expires_at' => $activeSession->expires_at->toIso8601String(),
                'remaining_seconds' => max(0, $now->diffInSeconds($activeSession->expires_at, false)),
                'ws_token' => $activeSession->ws_token,
                'ws_port' => (int) config('terminal.ws_port', 3001),
            ];
        }

        $exercises = $lesson->exercises()
            ->with('hints')
            ->orderBy('order')
            ->get()
            ->map(function ($exercise) use ($userId) {
                $sessionKey = "exercise_{$exercise->id}_hints_user_{$userId}";
                $unlockedCount = (int) session()->get($sessionKey, 0);
                $allHints = $exercise->hints->sortBy('order')->values();
                $unlockedHints = $allHints->take($unlockedCount)->values();

                $latestAttempt = \App\Models\ExerciseAttempt::query()
                    ->where('user_id', $userId)
                    ->where('exercise_id', $exercise->id)
                    ->latest('id')
                    ->first();

                $hasPassed = \App\Models\ExerciseAttempt::query()
                    ->where('user_id', $userId)
                    ->where('exercise_id', $exercise->id)
                    ->where('is_correct', true)
                    ->exists();

                return [
                    'id' => $exercise->id,
                    'title' => $exercise->title,
                    'instruction' => $exercise->instruction,
                    'validation_type' => $exercise->validation_type,
                    'points' => $exercise->points,
                    'order' => $exercise->order,
                    'total_hints' => $allHints->count(),
                    'unlocked_hints' => $unlockedHints->map(fn ($h) => [
                        'id' => $h->id,
                        'content' => $h->content,
                        'order' => $h->order,
                    ]),
                    'has_passed' => $hasPassed,
                    'latest_attempt' => $latestAttempt ? [
                        'id' => $latestAttempt->id,
                        'is_correct' => $latestAttempt->is_correct,
                        'score' => $latestAttempt->score,
                        'feedback' => $latestAttempt->feedback,
                        'created_at' => $latestAttempt->created_at->toIso8601String(),
                    ] : null,
                ];
            });

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
            'initialSession' => $activeSessionData,
            'exercises' => $exercises,
        ]);
    }
}

