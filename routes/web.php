<?php

use App\Http\Controllers\LessonController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\ProfileController;
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

Route::get('/design-system', function () {
    return Inertia::render('DesignSystemTest');
})->name('design-system');

// 9.2 Rute Pengguna Terdaftar
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        $modules = Module::query()
            ->where('is_published', true)
            ->orderBy('order')
            ->withCount('lessons')
            ->get()
            ->map(function (Module $module) {
                return [
                    'id' => $module->id,
                    'title' => $module->title,
                    'slug' => $module->slug,
                    'description' => $module->description,
                    'order' => $module->order,
                    'lessons_count' => $module->lessons_count,
                    'completion_percentage' => 0,
                ];
            });

        return Inertia::render('Dashboard', [
            'modules' => $modules,
        ]);
    })->name('dashboard');

    // FR-MOD-01 Daftar dan Detail Modul
    Route::get('/modules', [ModuleController::class, 'index'])->name('modules.index');
    Route::get('/modules/{module}', [ModuleController::class, 'show'])->name('modules.show');

    // FR-MOD-02 Materi Pelajaran dan Praktik
    Route::get('/modules/{module}/lessons/{lesson}', [LessonController::class, 'show'])->name('lessons.show');
    Route::get('/lessons/{lesson}/practice', [LessonController::class, 'practice'])->name('lessons.practice');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

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
