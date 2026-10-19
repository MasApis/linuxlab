<?php

use App\Models\Exercise;
use App\Models\ExerciseAttempt;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Module;
use App\Models\ModuleProgress;
use App\Models\User;
use App\Services\ProgressService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->progressService = app(ProgressService::class);

    $this->module = Module::create([
        'title' => 'Dasar File System',
        'slug' => 'file-system',
        'description' => 'Deskripsi modul',
        'order' => 1,
        'is_published' => true,
    ]);

    $this->lesson1 = Lesson::create([
        'module_id' => $this->module->id,
        'title' => 'Pelajaran 1',
        'slug' => 'pelajaran-1',
        'content' => 'Konten 1',
        'order' => 1,
    ]);

    $this->lesson2 = Lesson::create([
        'module_id' => $this->module->id,
        'title' => 'Pelajaran 2',
        'slug' => 'pelajaran-2',
        'content' => 'Konten 2',
        'order' => 2,
    ]);

    $this->exercise1 = Exercise::create([
        'lesson_id' => $this->lesson1->id,
        'title' => 'Latihan 1',
        'instruction' => 'Instruksi 1',
        'validation_type' => 'output',
        'validation_config' => ['checks' => []],
        'points' => 100,
        'order' => 1,
    ]);

    $this->exercise2 = Exercise::create([
        'lesson_id' => $this->lesson2->id,
        'title' => 'Latihan 2',
        'instruction' => 'Instruksi 2',
        'validation_type' => 'output',
        'validation_config' => ['checks' => []],
        'points' => 100,
        'order' => 1,
    ]);
});

test('pelajaran berstatus completed apabila seluruh latihan dijawab benar sekurang-kurangnya satu kali (FR-PROG-01)', function () {
    $user = User::factory()->create();

    // Belum dijawab -> updateLessonProgress menghasilkan false
    $completedBefore = $this->progressService->updateLessonProgress($user, $this->lesson1);
    expect($completedBefore)->toBeFalse();

    $progress = LessonProgress::where('user_id', $user->id)
        ->where('lesson_id', $this->lesson1->id)
        ->first();
    expect($progress->status)->toBe('in_progress');

    // Menjawab salah -> tetap in_progress
    ExerciseAttempt::create([
        'user_id' => $user->id,
        'exercise_id' => $this->exercise1->id,
        'is_correct' => false,
        'score' => 0,
        'feedback' => 'Salah',
    ]);

    $completedAttemptWrong = $this->progressService->updateLessonProgress($user, $this->lesson1);
    expect($completedAttemptWrong)->toBeFalse();

    // Menjawab benar -> menjadi completed
    ExerciseAttempt::create([
        'user_id' => $user->id,
        'exercise_id' => $this->exercise1->id,
        'is_correct' => true,
        'score' => 100,
        'feedback' => 'Benar!',
    ]);

    $completedAttemptCorrect = $this->progressService->updateLessonProgress($user, $this->lesson1);
    expect($completedAttemptCorrect)->toBeTrue();

    $progressAfter = LessonProgress::where('user_id', $user->id)
        ->where('lesson_id', $this->lesson1->id)
        ->first();
    expect($progressAfter->status)->toBe('completed')
        ->and($progressAfter->completed_at)->not->toBeNull();
});

test('persentase progres modul dihitung akurat berdasarkan pelajaran completed dan berstatus completed pada 100% (FR-PROG-02)', function () {
    $user = User::factory()->create();

    // Awal: belum ada pelajaran selesai -> 0%
    $this->progressService->updateModuleProgress($user, $this->module);
    $mp = ModuleProgress::where('user_id', $user->id)->where('module_id', $this->module->id)->first();
    expect($mp->completion_percentage)->toBe(0)
        ->and($mp->status)->toBe('not_started');

    // Selesaikan Pelajaran 1 (1 dari 2 pelajaran -> 50%)
    ExerciseAttempt::create([
        'user_id' => $user->id,
        'exercise_id' => $this->exercise1->id,
        'is_correct' => true,
        'score' => 100,
        'feedback' => 'Benar',
    ]);
    $this->progressService->updateLessonProgress($user, $this->lesson1);
    $this->progressService->updateModuleProgress($user, $this->module);

    $mpHalf = ModuleProgress::where('user_id', $user->id)->where('module_id', $this->module->id)->first();
    expect($mpHalf->completion_percentage)->toBe(50)
        ->and($mpHalf->status)->toBe('in_progress');

    // Selesaikan Pelajaran 2 (2 dari 2 pelajaran -> 100%)
    ExerciseAttempt::create([
        'user_id' => $user->id,
        'exercise_id' => $this->exercise2->id,
        'is_correct' => true,
        'score' => 100,
        'feedback' => 'Benar',
    ]);
    $this->progressService->updateLessonProgress($user, $this->lesson2);
    $this->progressService->updateModuleProgress($user, $this->module);

    $mpFull = ModuleProgress::where('user_id', $user->id)->where('module_id', $this->module->id)->first();
    expect($mpFull->completion_percentage)->toBe(100)
        ->and($mpFull->status)->toBe('completed');
});

test('tombol lanjutkan belajar mengarah ke pelajaran pertama yang belum selesai (FR-PROG-03)', function () {
    $user = User::factory()->create();

    // Saat belum ada yang selesai, mengarah ke pelajaran 1
    $next1 = $this->progressService->getNextLessonToLearn($user);
    expect($next1->id)->toBe($this->lesson1->id);

    // Tandai pelajaran 1 selesai
    LessonProgress::create([
        'user_id' => $user->id,
        'lesson_id' => $this->lesson1->id,
        'status' => 'completed',
        'completed_at' => now(),
    ]);

    // Sekarang harus mengarah ke pelajaran 2
    $next2 = $this->progressService->getNextLessonToLearn($user);
    expect($next2->id)->toBe($this->lesson2->id);
});

test('halaman dashboard dan progress dapat diakses oleh pengguna terautentikasi (FR-PROG-03, FR-EX-04)', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk();

    $this->actingAs($user)
        ->get(route('progress.index'))
        ->assertOk();
});

test('penilaian latihan memicu rangkaian VAL-05: progres diperbarui dan lencana dievaluasi', function () {
    $user = User::factory()->create(['role' => 'user']);

    $badge = \App\Models\Badge::create([
        'name' => 'Langkah Pertama',
        'description' => 'Latihan pertama benar',
        'icon' => 'rocket_launch',
        'criteria_type' => 'first_exercise',
        'criteria_value' => null,
    ]);

    $session = \App\Models\TerminalSession::create([
        'user_id' => $user->id,
        'exercise_id' => $this->exercise1->id,
        'container_id' => 'dummy-container-val05',
        'status' => 'active',
        'ws_token' => 'dummy-ws-token-val05',
        'expires_at' => now()->addMinutes(30),
    ]);

    // Mock terminalService agar evaluateCheck berhasil
    $terminalServiceMock = Mockery::mock(\App\Services\TerminalService::class);
    $terminalServiceMock->shouldReceive('executeCommand')
        ->andReturn(['stdout' => 'ok', 'stderr' => '', 'exit_code' => 0]);
    app()->instance(\App\Services\TerminalService::class, $terminalServiceMock);

    $this->exercise1->update([
        'validation_config' => [
            'checks' => [
                ['type' => 'file_exists', 'path' => '/home/student/file.txt'],
            ],
        ],
    ]);

    $validationService = app(\App\Services\ValidationService::class);
    $result = $validationService->validate($user, $this->exercise1, $session, 0);

    expect($result['success'])->toBeTrue()
        ->and($result['is_correct'])->toBeTrue();

    // Pastikan progress pelajaran terisi
    $lessonProgress = LessonProgress::where('user_id', $user->id)->where('lesson_id', $this->lesson1->id)->first();
    expect($lessonProgress)->not->toBeNull()
        ->and($lessonProgress->status)->toBe('completed');

    // Pastikan lencana diberikan
    expect($user->badges()->where('badge_id', $badge->id)->exists())->toBeTrue();
});
