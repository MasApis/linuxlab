<?php

use App\Models\Answer;
use App\Models\AnswerVote;
use App\Models\Badge;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Question;
use App\Models\User;
use App\Services\BadgeService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->module = Module::create([
        'title' => 'Dasar File System',
        'slug' => 'file-system',
        'description' => 'Modul pembelajaran file system Linux',
        'order' => 1,
        'is_published' => true,
    ]);

    $this->lesson = Lesson::create([
        'module_id' => $this->module->id,
        'title' => 'Navigasi Direktori',
        'slug' => 'navigasi-direktori',
        'content' => 'Materi navigasi direktori...',
        'example_commands' => 'pwd, ls, cd',
        'cheat_sheet' => 'pwd - print working directory',
        'order' => 1,
    ]);

    $this->user = User::factory()->create([
        'name' => 'Budi Pengguna',
        'username' => 'budip',
        'role' => 'user',
    ]);

    $this->otherUser = User::factory()->create([
        'name' => 'Siti Pembelajar',
        'username' => 'sitip',
        'role' => 'user',
    ]);
});

test('pengguna terdaftar dapat melihat forum per pelajaran (FR-FORUM-01)', function () {
    $this->actingAs($this->user);

    $response = $this->get(route('forum.index', $this->lesson->slug));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Forum/Index')
        ->has('lesson')
        ->has('questions')
    );
});

test('pengunjung belum login diarahkan ke login saat membuka forum (RBAC-03)', function () {
    $response = $this->get(route('forum.index', $this->lesson->slug));

    $response->assertRedirect(route('login'));
});

test('pengguna dapat membuat pertanyaan terikat pelajaran dan lesson_id tidak dapat diubah dari input (FR-FORUM-01)', function () {
    $this->actingAs($this->user);

    $otherLesson = Lesson::create([
        'module_id' => $this->module->id,
        'title' => 'Membuat File',
        'slug' => 'membuat-file',
        'content' => 'Materi touch...',
        'order' => 2,
    ]);

    // Input mencoba mengirim lesson_id palsu
    $response = $this->post(route('forum.store', $this->lesson->slug), [
        'title' => 'Bagaimana cara menggunakan cd relatif?',
        'body' => 'Saya mencoba menggunakan cd ../ tetapi tidak berhasil masuk ke direktori induk.',
        'lesson_id' => $otherLesson->id, // harus diabaikan, context route tetap this->lesson
    ]);

    $question = Question::first();

    expect($question)->not->toBeNull();
    expect($question->lesson_id)->toBe($this->lesson->id);
    expect($question->user_id)->toBe($this->user->id);
    expect($question->title)->toBe('Bagaimana cara menggunakan cd relatif?');
    expect($question->is_resolved)->toBeFalse();

    $response->assertRedirect(route('forum.show', $question->id));
});

test('validasi input pertanyaan: judul dan isi wajib serta memenuhi batas minimum (FR-FORUM-01, SEC-GEN-01)', function () {
    $this->actingAs($this->user);

    $response = $this->post(route('forum.store', $this->lesson->slug), [
        'title' => 'abc', // kurang dari 5 karakter
        'body' => 'singkat', // kurang dari 10 karakter
    ]);

    $response->assertSessionHasErrors(['title', 'body']);
    expect(Question::count())->toBe(0);
});

test('pengguna lain dan penanya sendiri dapat menulis jawaban (FR-FORUM-02)', function () {
    $question = Question::create([
        'user_id' => $this->user->id,
        'lesson_id' => $this->lesson->id,
        'title' => 'Pertanyaan Pertama',
        'body' => 'Isi pertanyaan yang cukup panjang dan detail.',
        'is_resolved' => false,
    ]);

    // Jawaban dari pengguna lain
    $this->actingAs($this->otherUser);
    $responseOther = $this->post(route('forum.answers.store', $question->id), [
        'body' => 'Gunakan perintah cd .. untuk berpindah ke direktori di atasnya.',
    ]);
    $responseOther->assertRedirect();

    // Jawaban dari penanya sendiri
    $this->actingAs($this->user);
    $responseSelf = $this->post(route('forum.answers.store', $question->id), [
        'body' => 'Terima kasih, saya sudah mencobanya dan berhasil!',
    ]);
    $responseSelf->assertRedirect();

    expect($question->answers()->count())->toBe(2);
});

test('upvote jawaban berhasil menambah jumlah vote (FR-FORUM-03)', function () {
    $question = Question::create([
        'user_id' => $this->user->id,
        'lesson_id' => $this->lesson->id,
        'title' => 'Pertanyaan',
        'body' => 'Isi pertanyaan detail.',
    ]);

    $answer = Answer::create([
        'question_id' => $question->id,
        'user_id' => $this->otherUser->id,
        'body' => 'Solusi yang sangat membantu.',
    ]);

    $this->actingAs($this->user);

    $response = $this->post(route('answers.vote', $answer->id));
    $response->assertRedirect();

    expect($answer->votes()->count())->toBe(1);
    expect(AnswerVote::where('answer_id', $answer->id)->where('user_id', $this->user->id)->exists())->toBeTrue();
});

test('upvote jawaban sendiri ditolak (FR-FORUM-03)', function () {
    $question = Question::create([
        'user_id' => $this->user->id,
        'lesson_id' => $this->lesson->id,
        'title' => 'Pertanyaan',
        'body' => 'Isi pertanyaan detail.',
    ]);

    $answer = Answer::create([
        'question_id' => $question->id,
        'user_id' => $this->user->id,
        'body' => 'Jawaban dari diri sendiri.',
    ]);

    // Mencoba upvote jawaban milik sendiri
    $this->actingAs($this->user);
    $response = $this->post(route('answers.vote', $answer->id));

    $response->assertStatus(403);
    expect($answer->votes()->count())->toBe(0);
});

test('upvote ganda ditolak oleh server dan tingkat database (FR-FORUM-03)', function () {
    $question = Question::create([
        'user_id' => $this->user->id,
        'lesson_id' => $this->lesson->id,
        'title' => 'Pertanyaan',
        'body' => 'Isi pertanyaan detail.',
    ]);

    $answer = Answer::create([
        'question_id' => $question->id,
        'user_id' => $this->otherUser->id,
        'body' => 'Jawaban orang lain.',
    ]);

    $this->actingAs($this->user);

    // Upvote pertama
    $firstVote = $this->post(route('answers.vote', $answer->id));
    $firstVote->assertRedirect();
    expect($answer->votes()->count())->toBe(1);

    // Upvote kedua (ganda) harus ditolak
    $secondVote = $this->post(route('answers.vote', $answer->id));
    $secondVote->assertStatus(422);

    expect($answer->votes()->count())->toBe(1);

    // Penegakan UNIQUE constraint di database level
    expect(function () use ($answer) {
        AnswerVote::create([
            'answer_id' => $answer->id,
            'user_id' => $this->user->id,
        ]);
    })->toThrow(\Illuminate\Database\QueryException::class);
});

test('penanya dapat menandai jawaban terbaik dan questions.is_resolved menjadi true (FR-FORUM-04)', function () {
    $question = Question::create([
        'user_id' => $this->user->id,
        'lesson_id' => $this->lesson->id,
        'title' => 'Pertanyaan Penanya',
        'body' => 'Isi pertanyaan detail.',
        'is_resolved' => false,
    ]);

    $answer = Answer::create([
        'question_id' => $question->id,
        'user_id' => $this->otherUser->id,
        'body' => 'Jawaban yang sangat tepat.',
        'is_best_answer' => false,
    ]);

    $this->actingAs($this->user);

    $response = $this->post(route('answers.best', $answer->id));
    $response->assertRedirect();

    expect($answer->fresh()->is_best_answer)->toBeTrue();
    expect($question->fresh()->is_resolved)->toBeTrue();
});

test('menandai jawaban terbaik lain menggantikan yang sebelumnya (FR-FORUM-04)', function () {
    $question = Question::create([
        'user_id' => $this->user->id,
        'lesson_id' => $this->lesson->id,
        'title' => 'Pertanyaan Penanya',
        'body' => 'Isi pertanyaan detail.',
        'is_resolved' => false,
    ]);

    $answer1 = Answer::create([
        'question_id' => $question->id,
        'user_id' => $this->otherUser->id,
        'body' => 'Jawaban pertama.',
        'is_best_answer' => true,
    ]);

    $thirdUser = User::factory()->create();
    $answer2 = Answer::create([
        'question_id' => $question->id,
        'user_id' => $thirdUser->id,
        'body' => 'Jawaban kedua yang lebih komprehensif.',
        'is_best_answer' => false,
    ]);

    $this->actingAs($this->user);

    // Tandai jawaban 2 sebagai terbaik
    $response = $this->post(route('answers.best', $answer2->id));
    $response->assertRedirect();

    expect($answer1->fresh()->is_best_answer)->toBeFalse();
    expect($answer2->fresh()->is_best_answer)->toBeTrue();
    expect($question->fresh()->is_resolved)->toBeTrue();
});

test('bukan penanya tidak bisa menandai jawaban terbaik (FR-FORUM-04)', function () {
    $question = Question::create([
        'user_id' => $this->user->id,
        'lesson_id' => $this->lesson->id,
        'title' => 'Pertanyaan Penanya Asli',
        'body' => 'Isi pertanyaan detail.',
        'is_resolved' => false,
    ]);

    $answer = Answer::create([
        'question_id' => $question->id,
        'user_id' => $this->otherUser->id,
        'body' => 'Jawaban bagus.',
        'is_best_answer' => false,
    ]);

    // Pengguna lain (bukan penanya) mencoba menandai jawaban terbaik
    $this->actingAs($this->otherUser);
    $response = $this->post(route('answers.best', $answer->id));

    $response->assertStatus(403);
    expect($answer->fresh()->is_best_answer)->toBeFalse();
    expect($question->fresh()->is_resolved)->toBeFalse();
});

test('urutan tampilan pertanyaan dan jawaban sesuai FR-FORUM-05', function () {
    // 1. Uji urutan pertanyaan: dari yang terbaru
    $qOld = Question::create([
        'user_id' => $this->user->id,
        'lesson_id' => $this->lesson->id,
        'title' => 'Pertanyaan Lama',
        'body' => 'Isi lama.',
        'created_at' => now()->subDay(),
    ]);

    $qNew = Question::create([
        'user_id' => $this->user->id,
        'lesson_id' => $this->lesson->id,
        'title' => 'Pertanyaan Baru',
        'body' => 'Isi baru.',
        'created_at' => now(),
    ]);

    $this->actingAs($this->user);

    $indexResponse = $this->get(route('forum.index', $this->lesson->slug));
    $indexResponse->assertOk();
    $indexResponse->assertInertia(function ($page) use ($qNew, $qOld) {
        $questions = $page->toArray()['props']['questions'];
        expect($questions[0]['id'])->toBe($qNew->id);
        expect($questions[1]['id'])->toBe($qOld->id);
    });

    // 2. Uji urutan jawaban: best answer di posisi teratas, lalu jumlah upvote terbanyak
    $ansNormal = Answer::create([
        'question_id' => $qNew->id,
        'user_id' => $this->otherUser->id,
        'body' => 'Jawaban biasa tanpa vote.',
        'is_best_answer' => false,
        'created_at' => now()->subMinutes(10),
    ]);

    $ansVoted = Answer::create([
        'question_id' => $qNew->id,
        'user_id' => $this->otherUser->id,
        'body' => 'Jawaban dengan banyak vote.',
        'is_best_answer' => false,
        'created_at' => now()->subMinutes(5),
    ]);

    // Beri 2 vote ke $ansVoted
    $voter1 = User::factory()->create();
    $voter2 = User::factory()->create();
    AnswerVote::create(['answer_id' => $ansVoted->id, 'user_id' => $voter1->id]);
    AnswerVote::create(['answer_id' => $ansVoted->id, 'user_id' => $voter2->id]);

    $ansBest = Answer::create([
        'question_id' => $qNew->id,
        'user_id' => $this->otherUser->id,
        'body' => 'Jawaban terbaik (meski tanpa vote).',
        'is_best_answer' => true,
        'created_at' => now()->subMinutes(2),
    ]);

    $showResponse = $this->get(route('forum.show', $qNew->id));
    $showResponse->assertOk();
    $showResponse->assertInertia(function ($page) use ($ansBest, $ansVoted, $ansNormal) {
        $answers = $page->toArray()['props']['answers'];
        expect($answers[0]['id'])->toBe($ansBest->id); // Best answer teratas
        expect($answers[1]['id'])->toBe($ansVoted->id); // Disusul vote terbanyak (2 votes)
        expect($answers[2]['id'])->toBe($ansNormal->id); // Tanpa vote
    });
});

test('membuat pertanyaan pertama memicu lencana first_question dan jawaban terbaik memicu best_answer (SPEC Bagian 12)', function () {
    $badgeFirstQ = Badge::create([
        'name' => 'Suka Bertanya',
        'description' => 'Membuat pertanyaan pertama di forum.',
        'icon' => 'help',
        'criteria_type' => 'first_question',
        'criteria_value' => null,
    ]);

    $badgeBestAns = Badge::create([
        'name' => 'Penolong Komunitas',
        'description' => 'Jawabannya ditandai sebagai jawaban terbaik.',
        'icon' => 'verified',
        'criteria_type' => 'best_answer',
        'criteria_value' => ['count' => 1],
    ]);

    $this->actingAs($this->user);

    // Belum punya lencana
    expect($this->user->badges()->count())->toBe(0);

    // Buat pertanyaan pertama
    $this->post(route('forum.store', $this->lesson->slug), [
        'title' => 'Pertanyaan Pertama Saya',
        'body' => 'Isi pertanyaan yang cukup panjang dan jelas.',
    ]);

    // Penanya mendapatkan lencana first_question
    expect($this->user->badges()->where('badges.id', $badgeFirstQ->id)->exists())->toBeTrue();

    $question = Question::first();

    // otherUser membuat jawaban
    $this->actingAs($this->otherUser);
    $this->post(route('forum.answers.store', $question->id), [
        'body' => 'Ini jawaban yang sangat jelas.',
    ]);

    $answer = Answer::first();
    expect($this->otherUser->badges()->count())->toBe(0);

    // user (penanya) menandai jawaban otherUser sebagai yang terbaik
    $this->actingAs($this->user);
    $this->post(route('answers.best', $answer->id));

    // otherUser mendapatkan lencana best_answer
    expect($this->otherUser->badges()->where('badges.id', $badgeBestAns->id)->exists())->toBeTrue();
});
