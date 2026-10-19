<?php

use App\Http\Controllers\AchievementController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProgressController;
use App\Models\Lesson;
use App\Models\Module;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// 9.1 Rute Publik (Pengunjung)
Route::get('/', function () {
    $modules = Module::query()
        ->where('is_published', true)
        ->orderBy('order')
        ->withCount('lessons')
        ->get(['id', 'title', 'slug', 'description', 'order']);

    return Inertia::render('Welcome', [
        'modules' => $modules,
    ]);
})->name('home');

// FR-LEAD-01 s.d. 04 Leaderboard Publik
Route::get('/leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard');

// FR-CERT-04 Verifikasi Publik Sertifikat
Route::get('/certificates/verify/{code?}', [CertificateController::class, 'verify'])->name('certificates.verify');

Route::get('/design-system', function () {
    return Inertia::render('DesignSystemTest');
})->name('design-system');

// 9.2 Rute Pengguna Terdaftar
Route::middleware('auth')->group(function () {
    // FR-PROG-03 Dashboard Pengguna
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // FR-PROG-02 & FR-EX-04 Progres & Riwayat
    Route::get('/progress', [ProgressController::class, 'index'])->name('progress.index');

    // FR-PROG-05, FR-CERT-03, FR-CERT-05 Pencapaian & Kartu Sertifikat
    Route::get('/achievements', [AchievementController::class, 'index'])->name('achievements.index');

    // FR-CERT-03 Unduh Sertifikat PDF (hanya milik sendiri)
    Route::get('/certificates/download', [CertificateController::class, 'download'])->name('certificates.download');

    // FR-MOD-01 Daftar dan Detail Modul
    Route::get('/modules', [ModuleController::class, 'index'])->name('modules.index');
    Route::get('/modules/{module}', [ModuleController::class, 'show'])->name('modules.show');

    // FR-MOD-02 Materi Pelajaran dan Praktik
    Route::get('/modules/{module}/lessons/{lesson}', [LessonController::class, 'show'])->name('lessons.show');
    Route::get('/lessons/{lesson}/practice', [LessonController::class, 'practice'])->name('lessons.practice');

    // FR-EX-01 s.d. FR-EX-03 Penilaian Latihan dan Hint Bertahap
    Route::post('/exercises/{exercise}/check', [\App\Http\Controllers\ExerciseController::class, 'check'])->name('exercises.check');
    Route::post('/exercises/{exercise}/hints', [\App\Http\Controllers\ExerciseController::class, 'hint'])->name('exercises.hints');

    // FR-TERM-01, FR-TERM-04, SEC-TERM-09 Sesi Terminal
    Route::post('/terminal/sessions', [\App\Http\Controllers\TerminalSessionController::class, 'store'])->name('terminal.sessions.store');
    Route::get('/terminal/sessions/{session}', [\App\Http\Controllers\TerminalSessionController::class, 'show'])->name('terminal.sessions.show');
    Route::delete('/terminal/sessions/{session}', [\App\Http\Controllers\TerminalSessionController::class, 'destroy'])->name('terminal.sessions.destroy');

    // FR-FORUM-01 Forum per Pelajaran
    Route::get('/forum', function () {
        $firstLesson = Lesson::query()->orderBy('order')->first();
        if ($firstLesson) {
            return redirect()->route('forum.index', $firstLesson);
        }
        return redirect()->route('modules.index');
    })->name('forum');
    Route::get('/lessons/{lesson}/forum', [ForumController::class, 'index'])->name('forum.index');
    Route::post('/lessons/{lesson}/forum', [ForumController::class, 'storeQuestion'])->name('forum.store');

    // FR-FORUM-02 Detail Pertanyaan & Menulis Jawaban
    Route::get('/forum/{question}', [ForumController::class, 'show'])->name('forum.show');
    Route::post('/forum/{question}/answers', [ForumController::class, 'storeAnswer'])->name('forum.answers.store');

    // FR-FORUM-03 Upvote Jawaban
    Route::post('/answers/{answer}/vote', [ForumController::class, 'voteAnswer'])->name('answers.vote');

    // FR-FORUM-04 Menandai Jawaban Terbaik
    Route::post('/answers/{answer}/best', [ForumController::class, 'markBestAnswer'])->name('answers.best');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Endpoint internal untuk validasi token oleh daemon WebSocket Gateway lokal (SEC-TERM-01)
Route::get('/internal/terminal/verify', [\App\Http\Controllers\TerminalSessionController::class, 'verifyInternal'])->name('internal.terminal.verify');


// 9.3 Rute Administrator (awalan /admin, middleware auth + admin)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return Inertia::render('Admin/Dashboard');
    })->name('dashboard');

    // Placeholder untuk sub-route admin lainnya agar terlindungi oleh middleware admin (RBAC-04)
    Route::get('/{any}', function () {
        return Inertia::render('Admin/Dashboard');
    })->where('any', '.*');
});

// Rute Autentikasi (Register, Login, Logout, dll.)
require __DIR__.'/auth.php';

// Fallback untuk route yang tidak ditemukan (404)
Route::fallback(function () {
    return Inertia::render('Error', [
        'status' => 404,
        'message' => 'Halaman Tidak Ditemukan',
    ])->toResponse(request())->setStatusCode(404);
});
