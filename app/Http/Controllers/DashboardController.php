<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\ModuleProgress;
use App\Services\CertificateService;
use App\Services\ProgressService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        protected ProgressService $progressService,
        protected CertificateService $certificateService
    ) {}

    /**
     * Menampilkan dashboard progres pembelajaran pengguna (SPEC Bagian 6.E FR-PROG-03 & Bagian 10).
     */
    public function index(Request $request): Response
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // 1. Progres Keseluruhan
        $overallProgress = $this->progressService->getOverallProgress($user);

        // 2. Daftar Modul Terbitan dengan persentase progres masing-masing
        $modules = Module::query()
            ->where('is_published', true)
            ->orderBy('order')
            ->withCount('lessons')
            ->get()
            ->map(function (Module $module) use ($user) {
                $progress = ModuleProgress::where('user_id', $user->id)
                    ->where('module_id', $module->id)
                    ->first();

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

        // 3. Pelajaran berikutnya untuk tombol Lanjutkan Belajar
        $nextLesson = $this->progressService->getNextLessonToLearn($user);
        $nextLessonData = null;
        if ($nextLesson) {
            $nextLessonData = [
                'id' => $nextLesson->id,
                'title' => $nextLesson->title,
                'slug' => $nextLesson->slug,
                'module_slug' => $nextLesson->module?->slug,
                'module_title' => $nextLesson->module?->title,
            ];
        }

        // 4. Lencana terakhir yang diperoleh
        $recentBadges = $user->badges()
            ->orderByPivot('awarded_at', 'desc')
            ->take(4)
            ->get()
            ->map(fn ($badge) => [
                'id' => $badge->id,
                'name' => $badge->name,
                'description' => $badge->description,
                'icon' => $badge->icon,
                'awarded_at' => $badge->pivot->awarded_at ? \Carbon\Carbon::parse($badge->pivot->awarded_at)->translatedFormat('d M Y') : null,
            ]);

        // 5. Peringkat saat ini di leaderboard (FR-LEAD-03)
        $currentRank = $this->certificateService->getUserRank($user);

        return Inertia::render('Dashboard', [
            'overallProgress' => $overallProgress,
            'modules' => $modules,
            'nextLesson' => $nextLessonData,
            'recentBadges' => $recentBadges,
            'currentRank' => $currentRank,
        ]);
    }
}
