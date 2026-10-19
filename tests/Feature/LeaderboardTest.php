<?php

use App\Models\Exercise;
use App\Models\ExerciseAttempt;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\User;
use App\Services\CertificateService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->certificateService = app(CertificateService::class);

    $this->module = Module::create([
        'title' => 'Modul 1',
        'slug' => 'modul-1',
        'description' => 'Deskripsi',
        'order' => 1,
        'is_published' => true,
    ]);

    $this->lesson = Lesson::create([
        'module_id' => $this->module->id,
        'title' => 'Pelajaran 1',
        'slug' => 'pelajaran-1',
        'content' => 'Konten',
        'order' => 1,
    ]);

    $this->exercise1 = Exercise::create([
        'lesson_id' => $this->lesson->id,
        'title' => 'Latihan 1',
        'instruction' => 'Instruksi',
        'validation_type' => 'output',
        'validation_config' => ['checks' => []],
        'points' => 100,
        'order' => 1,
    ]);

    $this->exercise2 = Exercise::create([
        'lesson_id' => $this->lesson->id,
        'title' => 'Latihan 2',
        'instruction' => 'Instruksi',
        'validation_type' => 'output',
        'validation_config' => ['checks' => []],
        'points' => 50,
        'order' => 2,
    ]);
});

test('leaderboard dihitung dari skor terbaik per latihan dan mengabaikan percobaan berulang (FR-LEAD-02)', function () {
    $user = User::factory()->create(['role' => 'user', 'is_active' => true]);

    // Latihan 1 dikerjakan 3 kali (skor: 70, 100, 100) -> skor terbaik harus 100, bukan 270
    ExerciseAttempt::create([
        'user_id' => $user->id,
        'exercise_id' => $this->exercise1->id,
        'is_correct' => true,
        'score' => 70,
        'feedback' => 'Sebagian',
        'created_at' => now()->subMinutes(10),
    ]);

    ExerciseAttempt::create([
        'user_id' => $user->id,
        'exercise_id' => $this->exercise1->id,
        'is_correct' => true,
        'score' => 100,
        'feedback' => 'Sempurna',
        'created_at' => now()->subMinutes(5),
    ]);

    ExerciseAttempt::create([
        'user_id' => $user->id,
        'exercise_id' => $this->exercise1->id,
        'is_correct' => true,
        'score' => 100,
        'feedback' => 'Sempurna lagi',
        'created_at' => now()->subMinutes(1),
    ]);

    // Latihan 2 dikerjakan 1 kali (skor: 50)
    ExerciseAttempt::create([
        'user_id' => $user->id,
        'exercise_id' => $this->exercise2->id,
        'is_correct' => true,
        'score' => 50,
        'feedback' => 'Benar',
        'created_at' => now(),
    ]);

    $leaderboard = $this->certificateService->getLeaderboard();

    expect($leaderboard)->toHaveCount(1);
    $entry = $leaderboard->first();

    // Total score harus 100 + 50 = 150 (bukan 320)
    expect($entry->total_score)->toBe(150)
        ->and($entry->correct_exercises_count)->toBe(2);
});

test('leaderboard mengabaikan akun admin dan akun nonaktif (FR-LEAD-01)', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $inactiveUser = User::factory()->create(['role' => 'user', 'is_active' => false]);
    $activeUser = User::factory()->create(['role' => 'user', 'is_active' => true]);

    // Ketiganya mengerjakan latihan yang sama dengan benar
    foreach ([$admin, $inactiveUser, $activeUser] as $u) {
        ExerciseAttempt::create([
            'user_id' => $u->id,
            'exercise_id' => $this->exercise1->id,
            'is_correct' => true,
            'score' => 100,
            'feedback' => 'Benar',
        ]);
    }

    $leaderboard = $this->certificateService->getLeaderboard();

    // Hanya activeUser yang masuk leaderboard
    expect($leaderboard)->toHaveCount(1);
    expect($leaderboard->first()->user_id)->toBe($activeUser->id);
});

test('halaman leaderboard publik dapat diakses tanpa login dan tidak menampilkan email (FR-LEAD-01, FR-LEAD-04)', function () {
    $user = User::factory()->create([
        'role' => 'user',
        'is_active' => true,
        'name' => 'Budi Raharjo',
        'username' => 'budir',
        'email' => 'budi.rahasia@example.com',
    ]);

    ExerciseAttempt::create([
        'user_id' => $user->id,
        'exercise_id' => $this->exercise1->id,
        'is_correct' => true,
        'score' => 100,
        'feedback' => 'Benar',
    ]);

    $response = $this->get(route('leaderboard'));
    $response->assertOk();

    // Pastikan email tidak ada pada properti yang diteruskan ke frontend
    $response->assertInertia(fn ($page) => $page
        ->component('Leaderboard/Index')
        ->has('rankings', 1)
        ->where('rankings.0.username', 'budir')
        ->where('rankings.0.name', 'Budi Raharjo')
        ->missing('rankings.0.email')
    );
});

test('posisi pengguna yang login disorot pada leaderboard (FR-LEAD-03)', function () {
    $user1 = User::factory()->create(['role' => 'user', 'is_active' => true]);
    $user2 = User::factory()->create(['role' => 'user', 'is_active' => true]);

    ExerciseAttempt::create([
        'user_id' => $user1->id,
        'exercise_id' => $this->exercise1->id,
        'is_correct' => true,
        'score' => 100,
        'feedback' => 'Benar',
    ]);

    ExerciseAttempt::create([
        'user_id' => $user2->id,
        'exercise_id' => $this->exercise1->id,
        'is_correct' => true,
        'score' => 100,
        'feedback' => 'Benar',
    ]);

    $this->actingAs($user1)
        ->get(route('leaderboard'))
        ->assertInertia(fn ($page) => $page
            ->component('Leaderboard/Index')
            ->where('rankings.0.is_current_user', true)
            ->where('currentUserRank', 1)
        );
});

test('aturan tie-breaking peringkat leaderboard: latihan terbanyak lalu waktu lebih awal (FR-LEAD-02)', function () {
    $userA = User::factory()->create(['role' => 'user', 'is_active' => true, 'name' => 'User A']);
    $userB = User::factory()->create(['role' => 'user', 'is_active' => true, 'name' => 'User B']);

    // Kasus 1: userA menyelesaikan 2 latihan (skor 50 + 50 = 100), userB menyelesaikan 1 latihan (skor 100)
    // Walau skor sama (100), userA punya 2 latihan selesai, userB punya 1 latihan selesai -> userA rank 1, userB rank 2
    ExerciseAttempt::create([
        'user_id' => $userA->id,
        'exercise_id' => $this->exercise1->id,
        'is_correct' => true,
        'score' => 50,
        'feedback' => 'Benar',
        'created_at' => now()->subMinutes(10),
    ]);
    ExerciseAttempt::create([
        'user_id' => $userA->id,
        'exercise_id' => $this->exercise2->id,
        'is_correct' => true,
        'score' => 50,
        'feedback' => 'Benar',
        'created_at' => now()->subMinutes(8),
    ]);

    ExerciseAttempt::create([
        'user_id' => $userB->id,
        'exercise_id' => $this->exercise1->id,
        'is_correct' => true,
        'score' => 100,
        'feedback' => 'Benar',
        'created_at' => now()->subMinutes(20),
    ]);

    $leaderboard = $this->certificateService->getLeaderboard();

    expect($leaderboard[0]->user_id)->toBe($userA->id);
    expect($leaderboard[1]->user_id)->toBe($userB->id);
});
