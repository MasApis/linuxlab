<?php

use App\Models\Answer;
use App\Models\AnswerVote;
use App\Models\Badge;
use App\Models\Exercise;
use App\Models\Hint;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Question;
use App\Models\User;

test('pengguna dengan role user ditolak dari seluruh endpoint admin dengan status 403 (RBAC-04)', function () {
    $user = User::factory()->create(['role' => 'user']);
    $module = Module::factory()->create();
    $lesson = Lesson::factory()->create(['module_id' => $module->id]);
    $exercise = Exercise::create([
        'lesson_id' => $lesson->id,
        'title' => 'Test Exercise',
        'instruction' => 'Test instruction',
        'validation_type' => 'system_state',
        'validation_config' => ['checks' => []],
        'points' => 100,
        'order' => 1,
    ]);
    $badge = Badge::create([
        'name' => 'Test Badge',
        'description' => 'Test desc',
        'icon' => '🏆',
        'criteria_type' => 'first_exercise',
    ]);
    $question = Question::create([
        'user_id' => $user->id,
        'lesson_id' => $lesson->id,
        'title' => 'Sample Question',
        'body' => 'Sample body',
    ]);

    // Dashboard
    $this->actingAs($user)->get('/admin')->assertStatus(403);

    // Modul
    $this->actingAs($user)->get('/admin/modules')->assertStatus(403);
    $this->actingAs($user)->post('/admin/modules', ['title' => 'Hack', 'description' => 'Hack', 'order' => 99])->assertStatus(403);
    $this->actingAs($user)->patch("/admin/modules/{$module->slug}", ['title' => 'Hack'])->assertStatus(403);
    $this->actingAs($user)->delete("/admin/modules/{$module->slug}")->assertStatus(403);

    // Pelajaran
    $this->actingAs($user)->get('/admin/lessons')->assertStatus(403);
    $this->actingAs($user)->post('/admin/lessons', ['title' => 'Hack'])->assertStatus(403);
    $this->actingAs($user)->patch("/admin/lessons/{$lesson->slug}", ['title' => 'Hack'])->assertStatus(403);
    $this->actingAs($user)->delete("/admin/lessons/{$lesson->slug}")->assertStatus(403);

    // Latihan
    $this->actingAs($user)->get('/admin/exercises')->assertStatus(403);
    $this->actingAs($user)->post('/admin/exercises', ['title' => 'Hack'])->assertStatus(403);
    $this->actingAs($user)->patch("/admin/exercises/{$exercise->id}", ['title' => 'Hack'])->assertStatus(403);
    $this->actingAs($user)->delete("/admin/exercises/{$exercise->id}")->assertStatus(403);

    // Lencana
    $this->actingAs($user)->get('/admin/badges')->assertStatus(403);
    $this->actingAs($user)->post('/admin/badges', ['name' => 'Hack'])->assertStatus(403);
    $this->actingAs($user)->patch("/admin/badges/{$badge->id}", ['name' => 'Hack'])->assertStatus(403);
    $this->actingAs($user)->delete("/admin/badges/{$badge->id}")->assertStatus(403);

    // Moderasi Forum
    $this->actingAs($user)->get('/admin/forum')->assertStatus(403);
    $this->actingAs($user)->delete("/admin/forum/questions/{$question->id}")->assertStatus(403);

    // Pengguna
    $this->actingAs($user)->get('/admin/users')->assertStatus(403);
    $this->actingAs($user)->patch("/admin/users/{$user->id}", ['is_active' => false])->assertStatus(403);
});

test('validation_config dengan format JSON rusak ditolak pada form latihan (FR-ADMIN-01)', function () {
    $admin = User::factory()->admin()->create();
    $module = Module::factory()->create();
    $lesson = Lesson::factory()->create(['module_id' => $module->id]);

    // Percobaan simpan latihan dengan string JSON rusak / tidak valid
    $corruptJson = '{ "checks": [ { "type": "file_exists", path_tanpa_quotes: error } ] ';

    $response = $this->actingAs($admin)->post('/admin/exercises', [
        'lesson_id' => $lesson->id,
        'title' => 'Latihan Syntax Error',
        'instruction' => 'Buat file',
        'validation_type' => 'system_state',
        'validation_config' => $corruptJson,
        'points' => 100,
        'order' => 1,
    ]);

    $response->assertSessionHasErrors('validation_config');
    expect(Exercise::where('title', 'Latihan Syntax Error')->exists())->toBeFalse();

    // Percobaan update latihan dengan JSON rusak
    $validExercise = Exercise::create([
        'lesson_id' => $lesson->id,
        'title' => 'Latihan Valid Awal',
        'instruction' => 'Instruksi valid',
        'validation_type' => 'output',
        'validation_config' => ['checks' => []],
        'points' => 50,
        'order' => 1,
    ]);

    $updateResponse = $this->actingAs($admin)->patch("/admin/exercises/{$validExercise->id}", [
        'lesson_id' => $lesson->id,
        'title' => 'Latihan Valid Awal',
        'instruction' => 'Instruksi valid',
        'validation_type' => 'output',
        'validation_config' => '{"checks": NOT_VALID_JSON}',
        'points' => 50,
        'order' => 1,
    ]);

    $updateResponse->assertSessionHasErrors('validation_config');
    expect($validExercise->fresh()->validation_config)->toBe(['checks' => []]);

    // Percobaan simpan dengan JSON yang valid berhasil
    $validJsonString = json_encode([
        'checks' => [
            ['type' => 'file_exists', 'path' => '/home/student/test.txt'],
        ],
    ]);

    $validResponse = $this->actingAs($admin)->post('/admin/exercises', [
        'lesson_id' => $lesson->id,
        'title' => 'Latihan Valid Baru',
        'instruction' => 'Instruksi benar',
        'validation_type' => 'system_state',
        'validation_config' => $validJsonString,
        'points' => 100,
        'order' => 2,
    ]);

    $validResponse->assertSessionHasNoErrors();
    $validResponse->assertRedirect();
    $created = Exercise::where('title', 'Latihan Valid Baru')->first();
    expect($created)->not->toBeNull();
    expect($created->validation_config['checks'][0]['path'])->toBe('/home/student/test.txt');
});

test('administrator tidak dapat menonaktifkan atau mengubah peran akunnya sendiri (FR-ADMIN-04)', function () {
    $admin = User::factory()->admin()->create(['is_active' => true]);
    $otherUser = User::factory()->create(['is_active' => true, 'role' => 'user']);

    // Admin mencoba menonaktifkan akunnya sendiri
    $selfDeactivateResponse = $this->actingAs($admin)->patch("/admin/users/{$admin->id}", [
        'is_active' => false,
    ]);
    $selfDeactivateResponse->assertStatus(403);
    expect($admin->fresh()->is_active)->toBeTrue();

    // Admin mencoba mengubah peran akunnya sendiri menjadi user
    $selfRoleResponse = $this->actingAs($admin)->patch("/admin/users/{$admin->id}", [
        'role' => 'user',
    ]);
    $selfRoleResponse->assertStatus(403);
    expect($admin->fresh()->role)->toBe('admin');

    // Admin menonaktifkan akun pengguna lain (diperbolehkan)
    $otherDeactivateResponse = $this->actingAs($admin)->patch("/admin/users/{$otherUser->id}", [
        'is_active' => false,
    ]);
    $otherDeactivateResponse->assertRedirect();
    expect($otherUser->fresh()->is_active)->toBeFalse();

    // Admin mengaktifkan kembali akun pengguna lain
    $otherActivateResponse = $this->actingAs($admin)->patch("/admin/users/{$otherUser->id}", [
        'is_active' => true,
    ]);
    $otherActivateResponse->assertRedirect();
    expect($otherUser->fresh()->is_active)->toBeTrue();
});

test('administrator dapat mengelola modul, pelajaran, latihan, hint, dan lencana (FR-ADMIN-01, FR-ADMIN-02)', function () {
    $admin = User::factory()->admin()->create();

    // 1. Tambah Modul Baru
    $moduleResponse = $this->actingAs($admin)->post('/admin/modules', [
        'title' => 'Modul Admin Eksperimental',
        'slug' => 'modul-admin-eksperimental',
        'description' => 'Deskripsi modul baru',
        'order' => 10,
        'is_published' => true,
    ]);
    $moduleResponse->assertRedirect();
    $module = Module::where('slug', 'modul-admin-eksperimental')->first();
    expect($module)->not->toBeNull();

    // Ubah Modul
    $updateModResponse = $this->actingAs($admin)->patch("/admin/modules/{$module->slug}", [
        'title' => 'Modul Admin Diperbarui',
        'slug' => 'modul-admin-diperbarui',
        'description' => 'Deskripsi diperbarui',
        'order' => 10,
        'is_published' => true,
    ]);
    $updateModResponse->assertRedirect();
    expect($module->fresh()->title)->toBe('Modul Admin Diperbarui');
    $module = $module->fresh();

    // 2. Tambah Pelajaran Baru
    $lessonResponse = $this->actingAs($admin)->post('/admin/lessons', [
        'module_id' => $module->id,
        'title' => 'Pelajaran Admin Baru',
        'slug' => 'pelajaran-admin-baru',
        'content' => 'Konten materi admin lengkap',
        'example_commands' => 'echo "hello"',
        'cheat_sheet' => 'echo: mencetak teks',
        'order' => 1,
    ]);
    $lessonResponse->assertRedirect();
    $lesson = Lesson::where('slug', 'pelajaran-admin-baru')->first();
    expect($lesson)->not->toBeNull();

    // Ubah Pelajaran
    $updateLessonResponse = $this->actingAs($admin)->patch("/admin/lessons/{$lesson->slug}", [
        'module_id' => $module->id,
        'title' => 'Pelajaran Admin Diperbarui',
        'slug' => 'pelajaran-admin-diperbarui',
        'content' => 'Konten materi telah diperbarui',
        'order' => 1,
    ]);
    $updateLessonResponse->assertRedirect();
    expect($lesson->fresh()->title)->toBe('Pelajaran Admin Diperbarui');
    $lesson = $lesson->fresh();

    // 3. Tambah Latihan Baru dengan validator JSON
    $exerciseResponse = $this->actingAs($admin)->post('/admin/exercises', [
        'lesson_id' => $lesson->id,
        'title' => 'Latihan Echo Test',
        'instruction' => 'Cetak teks hello',
        'validation_type' => 'output',
        'validation_config' => json_encode([
            'checks' => [
                ['type' => 'command_output_contains', 'command' => 'echo "hello"', 'text' => 'hello'],
            ],
        ]),
        'points' => 25,
        'order' => 1,
    ]);
    $exerciseResponse->assertRedirect();
    $exercise = Exercise::where('title', 'Latihan Echo Test')->first();
    expect($exercise)->not->toBeNull();
    expect($exercise->points)->toBe(25);

    // 4. Tambah dan Kelola Hint untuk Latihan
    $hintResponse = $this->actingAs($admin)->post("/admin/exercises/{$exercise->id}/hints", [
        'content' => 'Gunakan perintah echo',
        'order' => 1,
    ]);
    $hintResponse->assertRedirect();
    $hint = Hint::where('exercise_id', $exercise->id)->first();
    expect($hint)->not->toBeNull();
    expect($hint->content)->toBe('Gunakan perintah echo');

    // Ubah Hint
    $updateHintResponse = $this->actingAs($admin)->patch("/admin/hints/{$hint->id}", [
        'content' => 'Gunakan echo disertai tanda kutip',
        'order' => 1,
    ]);
    $updateHintResponse->assertRedirect();
    expect($hint->fresh()->content)->toBe('Gunakan echo disertai tanda kutip');

    // Hapus Hint
    $deleteHintResponse = $this->actingAs($admin)->delete("/admin/hints/{$hint->id}");
    $deleteHintResponse->assertRedirect();
    expect(Hint::find($hint->id))->toBeNull();

    // 5. Kelola Master Lencana
    $badgeResponse = $this->actingAs($admin)->post('/admin/badges', [
        'name' => 'Penakluk Modul Admin',
        'description' => 'Diberikan setelah menyelesaikan modul admin',
        'icon' => '🌟',
        'criteria_type' => 'module_completed',
        'criteria_value' => json_encode(['module_id' => $module->id]),
    ]);
    $badgeResponse->assertRedirect();
    $badge = Badge::where('name', 'Penakluk Modul Admin')->first();
    expect($badge)->not->toBeNull();
    expect($badge->criteria_value)->toBe(['module_id' => $module->id]);

    // Hapus Lencana
    $deleteBadgeResponse = $this->actingAs($admin)->delete("/admin/badges/{$badge->id}");
    $deleteBadgeResponse->assertRedirect();
    expect(Badge::find($badge->id))->toBeNull();

    // 6. Uji Cascade Delete: Menghapus Modul menghapus Pelajaran dan Latihan di bawahnya
    $deleteModResponse = $this->actingAs($admin)->delete("/admin/modules/{$module->slug}");
    $deleteModResponse->assertRedirect();
    expect(Module::find($module->id))->toBeNull();
    expect(Lesson::find($lesson->id))->toBeNull();
    expect(Exercise::find($exercise->id))->toBeNull();
});

test('administrator dapat memoderasi forum termasuk penghapusan pertanyaan dan jawaban beserta aturan cascade (FR-ADMIN-03)', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    $module = Module::factory()->create();
    $lesson = Lesson::factory()->create(['module_id' => $module->id]);

    $question = Question::create([
        'user_id' => $user->id,
        'lesson_id' => $lesson->id,
        'title' => 'Pertanyaan Mengandung Spam',
        'body' => 'Spam link http://example.com',
        'is_resolved' => true,
    ]);

    $bestAnswer = Answer::create([
        'question_id' => $question->id,
        'user_id' => $user->id,
        'body' => 'Ini jawaban terbaik spam',
        'is_best_answer' => true,
    ]);

    $vote = AnswerVote::create([
        'answer_id' => $bestAnswer->id,
        'user_id' => $admin->id,
    ]);

    // 1. Hapus jawaban terbaik
    $deleteAnswerResponse = $this->actingAs($admin)->delete("/admin/forum/answers/{$bestAnswer->id}");
    $deleteAnswerResponse->assertRedirect();

    // Jawaban dan upvote-nya terhapus (cascade)
    expect(Answer::find($bestAnswer->id))->toBeNull();
    expect(AnswerVote::where('answer_id', $bestAnswer->id)->count())->toBe(0);

    // Karena jawaban terbaik dihapus, status is_resolved pada pertanyaan direset menjadi false
    expect($question->fresh()->is_resolved)->toBeFalse();

    // 2. Buat jawaban baru pada pertanyaan
    $newAnswer = Answer::create([
        'question_id' => $question->id,
        'user_id' => $user->id,
        'body' => 'Jawaban kedua',
        'is_best_answer' => false,
    ]);

    // Hapus seluruh pertanyaan
    $deleteQuestionResponse = $this->actingAs($admin)->delete("/admin/forum/questions/{$question->id}");
    $deleteQuestionResponse->assertRedirect();

    // Pertanyaan beserta jawaban di bawahnya terhapus
    expect(Question::find($question->id))->toBeNull();
    expect(Answer::find($newAnswer->id))->toBeNull();
});

test('dashboard admin menampilkan ringkasan statistik dari data nyata (FR-ADMIN-05)', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/admin');
    $response->assertStatus(200);

    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Dashboard')
        ->has('stats')
        ->where('stats.total_users', User::count())
        ->where('stats.active_users', User::where('is_active', true)->count())
        ->where('stats.total_modules', Module::count())
        ->where('stats.total_lessons', Lesson::count())
        ->where('stats.total_exercises', Exercise::count())
    );
});
