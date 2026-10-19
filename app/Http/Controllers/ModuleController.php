<?php

namespace App\Http\Controllers;

use App\Models\LessonProgress;
use App\Models\Module;
use App\Models\ModuleProgress;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ModuleController extends Controller
{
    /**
     * Menampilkan daftar seluruh modul pembelajaran yang aktif beserta progres pengguna (FR-MOD-01, FR-PROG-02).
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $modules = Module::query()
            ->where('is_published', true)
            ->orderBy('order')
            ->withCount('lessons')
            ->get()
            ->map(function (Module $module) use ($user) {
                $progress = $user
                    ? ModuleProgress::where('user_id', $user->id)->where('module_id', $module->id)->first()
                    : null;

                return [
                    'id' => $module->id,
                    'title' => $module->title,
                    'slug' => $module->slug,
                    'description' => $module->description,
                    'order' => $module->order,
                    'lessons_count' => $module->lessons_count,
                    'completion_percentage' => $progress ? (int) $progress->completion_percentage : 0,
                    'status' => $progress ? $progress->status : 'not_started',
                ];
            });

        return Inertia::render('Modules/Index', [
            'modules' => $modules,
        ]);
    }

    /**
     * Menampilkan detail suatu modul beserta daftar pelajarannya dan status penyelesaiannya (FR-MOD-01, FR-PROG-01 s.d. 02).
     */
    public function show(Request $request, Module $module): Response
    {
        abort_unless($module->is_published, 404);

        $user = $request->user();

        $module->load(['lessons' => function ($query) {
            $query->orderBy('order');
        }]);

        $moduleProgress = $user
            ? ModuleProgress::where('user_id', $user->id)->where('module_id', $module->id)->first()
            : null;

        $lessonsProgress = $user
            ? LessonProgress::where('user_id', $user->id)
                ->whereIn('lesson_id', $module->lessons->pluck('id'))
                ->get()
                ->keyBy('lesson_id')
            : collect();

        return Inertia::render('Modules/Show', [
            'module' => [
                'id' => $module->id,
                'title' => $module->title,
                'slug' => $module->slug,
                'description' => $module->description,
                'order' => $module->order,
                'completion_percentage' => $moduleProgress ? (int) $moduleProgress->completion_percentage : 0,
                'status' => $moduleProgress ? $moduleProgress->status : 'not_started',
                'lessons' => $module->lessons->map(function ($lesson) use ($lessonsProgress) {
                    $lp = $lessonsProgress->get($lesson->id);
                    return [
                        'id' => $lesson->id,
                        'title' => $lesson->title,
                        'slug' => $lesson->slug,
                        'order' => $lesson->order,
                        'status' => $lp ? $lp->status : 'not_started',
                        'is_completed' => $lp ? ($lp->status === 'completed') : false,
                    ];
                }),
            ],
        ]);
    }
}
