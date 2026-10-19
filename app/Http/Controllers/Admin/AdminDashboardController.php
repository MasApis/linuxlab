<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\Badge;
use App\Models\Exercise;
use App\Models\ExerciseAttempt;
use App\Models\Hint;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\ModuleProgress;
use App\Models\Question;
use App\Models\TerminalSession;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    /**
     * Menampilkan ringkasan statistik dan aktivitas terbaru untuk dashboard admin (FR-ADMIN-05).
     */
    public function index(Request $request): Response
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'total_modules' => Module::count(),
            'published_modules' => Module::where('is_published', true)->count(),
            'total_lessons' => Lesson::count(),
            'total_exercises' => Exercise::count(),
            'total_hints' => Hint::count(),
            'total_badges' => Badge::count(),
            'total_questions' => Question::count(),
            'unresolved_questions' => Question::where('is_resolved', false)->count(),
            'total_answers' => Answer::count(),
            'terminal_sessions_today' => TerminalSession::whereDate('created_at', today())->count(),
            'active_terminal_sessions' => TerminalSession::where('status', 'active')->count(),
            'module_completions' => ModuleProgress::where('status', 'completed')->count(),
        ];

        $recentQuestions = Question::with(['user:id,name,username', 'lesson:id,title'])
            ->withCount('answers')
            ->latest()
            ->take(5)
            ->get();

        $recentUsers = User::latest()
            ->take(5)
            ->get(['id', 'name', 'username', 'email', 'role', 'is_active', 'created_at']);

        $recentAttempts = ExerciseAttempt::with(['user:id,name,username', 'exercise:id,title'])
            ->latest('id')
            ->take(5)
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'recentQuestions' => $recentQuestions,
            'recentUsers' => $recentUsers,
            'recentAttempts' => $recentAttempts,
        ]);
    }
}
