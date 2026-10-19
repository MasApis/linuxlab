<?php

use App\Models\Badge;
use App\Models\Exercise;
use App\Models\ExerciseAttempt;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\ModuleProgress;
use App\Models\User;
use App\Models\UserBadge;
use App\Services\BadgeService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->badgeService = app(BadgeService::class);

    $this->badgeFirst = Badge::create([
        'name' => 'Langkah Pertama',
        'description' => 'Menyelesaikan latihan pertama dengan benar.',
        'icon' => 'rocket_launch',
        'criteria_type' => 'first_exercise',
        'criteria_value' => null,
    ]);

    $this->module = Module::create([
        'title' => 'Dasar File System',
        'slug' => 'file-system',
        'description' => 'Deskripsi',
        'order' => 1,
        'is_published' => true,
    ]);

    $this->badgeModule = Badge::create([
        'name' => 'Penjelajah File System',
        'description' => 'Menyelesaikan Modul File System.',
        'icon' => 'folder_open',
        'criteria_type' => 'module_completed',
        'criteria_value' => ['module_id' => $this->module->id],
    ]);

    $this->badge10 = Badge::create([
        'name' => 'Pembelajar Tekun',
        'description' => 'Telah menyelesaikan 10 latihan berbeda.',
        'icon' => 'school',
        'criteria_type' => 'exercises_count',
        'criteria_value' => ['count' => 10],
    ]);
});

test('lencana first_exercise diberikan saat latihan pertama diselesaikan dengan benar (FR-PROG-04, Bagian 12)', function () {
    $user = User::factory()->create();

    // Sebelum mengerjakan: belum dapat lencana
    $awardedBefore = $this->badgeService->evaluateBadges($user);
    expect($awardedBefore)->toBeEmpty();
    expect($user->badges()->count())->toBe(0);

    // Kerjakan latihan dengan is_correct = true
    $lesson = Lesson::create([
        'module_id' => $this->module->id,
        'title' => 'Pelajaran 1',
        'slug' => 'pelajaran-1',
        'content' => 'Konten',
        'order' => 1,
    ]);

    $exercise = Exercise::create([
        'lesson_id' => $lesson->id,
        'title' => 'Latihan 1',
        'instruction' => 'Instruksi',
        'validation_type' => 'output',
        'validation_config' => ['checks' => []],
        'points' => 100,
        'order' => 1,
    ]);

    ExerciseAttempt::create([
        'user_id' => $user->id,
        'exercise_id' => $exercise->id,
        'is_correct' => true,
        'score' => 100,
        'feedback' => 'Benar',
    ]);

    $awardedAfter = $this->badgeService->evaluateBadges($user);
    expect($awardedAfter)->toHaveCount(1)
        ->and($awardedAfter->first()->id)->toBe($this->badgeFirst->id);

    expect($user->badges()->count())->toBe(1);
});

test('lencana diberikan tepat satu kali kepada pengguna yang sama (FR-PROG-04)', function () {
    $user = User::factory()->create();

    $lesson = Lesson::create([
        'module_id' => $this->module->id,
        'title' => 'Pelajaran 1',
        'slug' => 'pelajaran-1',
        'content' => 'Konten',
        'order' => 1,
    ]);

    $exercise = Exercise::create([
        'lesson_id' => $lesson->id,
        'title' => 'Latihan 1',
        'instruction' => 'Instruksi',
        'validation_type' => 'output',
        'validation_config' => ['checks' => []],
        'points' => 100,
        'order' => 1,
    ]);

    ExerciseAttempt::create([
        'user_id' => $user->id,
        'exercise_id' => $exercise->id,
        'is_correct' => true,
        'score' => 100,
        'feedback' => 'Benar',
    ]);

    // Panggilan pertama
    $awarded1 = $this->badgeService->evaluateBadges($user);
    expect($awarded1)->toHaveCount(1);
    expect(UserBadge::where('user_id', $user->id)->count())->toBe(1);

    // Panggilan kedua untuk latihan berikutnya
    $awarded2 = $this->badgeService->evaluateBadges($user);
    expect($awarded2)->toBeEmpty();
    expect(UserBadge::where('user_id', $user->id)->count())->toBe(1);

    // Coba awardBadge secara manual untuk lencana yang sudah ada
    $awardedManual = $this->badgeService->awardBadge($user, $this->badgeFirst);
    expect($awardedManual)->toBeFalse();
    expect(UserBadge::where('user_id', $user->id)->count())->toBe(1);
});

test('kriteria module_completed memberikan lencana saat status modul completed (FR-PROG-04, Bagian 12)', function () {
    $user = User::factory()->create();

    ModuleProgress::create([
        'user_id' => $user->id,
        'module_id' => $this->module->id,
        'completion_percentage' => 100,
        'status' => 'completed',
    ]);

    $awarded = $this->badgeService->evaluateBadges($user);
    expect($awarded->pluck('id'))->toContain($this->badgeModule->id);
});

test('halaman pencapaian menampilkan lencana dan statusnya (FR-PROG-05)', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('achievements.index'))
        ->assertOk();
});
