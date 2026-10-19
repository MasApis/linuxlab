<?php

namespace App\Http\Controllers;

use App\Models\ExerciseAttempt;
use App\Models\Module;
use App\Models\ModuleProgress;
use App\Services\ProgressService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProgressController extends Controller
{
    public function __construct(
        protected ProgressService $progressService
    ) {}

    /**
     * Menampilkan halaman Progres & Riwayat latihan pengguna (FR-PROG-02, FR-EX-04).
     */
    public function index(Request $request): Response
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // 1. Progres Keseluruhan
        $overallProgress = $this->progressService->getOverallProgress($user);

        // 2. Progres per Modul
        $modules = Module::query()
            ->where('is_published', true)
            ->orderBy('order')
            ->withCount('lessons')
            ->with(['lessons' => function ($query) use ($user) {
                $query->orderBy('order')->with(['lessonProgress' => function ($lp) use ($user) {
                    $lp->where('user_id', $user->id);
                }]);
            }])
            ->get()
            ->map(function (Module $module) use ($user) {
                $moduleProgress = ModuleProgress::where('user_id', $user->id)
                    ->where('module_id', $module->id)
                    ->first();

                $lessons = $module->lessons->map(function ($lesson) {
                    $progress = $lesson->lessonProgress->first();
                    return [
                        'id' => $lesson->id,
                        'title' => $lesson->title,
                        'slug' => $lesson->slug,
                        'status' => $progress ? $progress->status : 'not_started',
                        'completed_at' => $progress && $progress->completed_at ? $progress->completed_at->translatedFormat('d M Y H:i') : null,
                    ];
                });

                return [
                    'id' => $module->id,
                    'title' => $module->title,
                    'slug' => $module->slug,
                    'description' => $module->description,
                    'lessons_count' => $module->lessons_count,
                    'completion_percentage' => $moduleProgress ? (int) $moduleProgress->completion_percentage : 0,
                    'status' => $moduleProgress ? $moduleProgress->status : 'not_started',
                    'lessons' => $lessons,
                ];
            });

        // 3. Riwayat Percobaan Latihan (FR-EX-04)
        $attempts = ExerciseAttempt::query()
            ->where('user_id', $user->id)
            ->with(['exercise.lesson.module'])
            ->orderByDesc('created_at')
            ->paginate(15)
            ->through(function (ExerciseAttempt $attempt) {
                return [
                    'id' => $attempt->id,
                    'is_correct' => (bool) $attempt->is_correct,
                    'score' => (int) $attempt->score,
                    'feedback' => $attempt->feedback,
                    'created_at' => $attempt->created_at->translatedFormat('d M Y H:i'),
                    'exercise' => [
                        'id' => $attempt->exercise?->id,
                        'title' => $attempt->exercise?->title,
                        'points' => $attempt->exercise?->points,
                    ],
                    'lesson' => [
                        'id' => $attempt->exercise?->lesson?->id,
                        'title' => $attempt->exercise?->lesson?->title,
                        'slug' => $attempt->exercise?->lesson?->slug,
                    ],
                    'module' => [
                        'id' => $attempt->exercise?->lesson?->module?->id,
                        'title' => $attempt->exercise?->lesson?->module?->title,
                        'slug' => $attempt->exercise?->lesson?->module?->slug,
                    ],
                ];
            });

        return Inertia::render('Progress/Index', [
            'overallProgress' => $overallProgress,
            'modules' => $modules,
            'attempts' => $attempts,
        ]);
    }
}
