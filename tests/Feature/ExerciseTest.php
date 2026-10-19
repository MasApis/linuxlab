<?php

use App\Models\Exercise;
use App\Models\ExerciseAttempt;
use App\Models\Hint;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\TerminalSession;
use App\Models\User;
use App\Services\TerminalService;
use App\Services\ValidationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->module = Module::create([
        'title' => 'Modul Uji',
        'slug' => 'modul-uji',
        'description' => 'Deskripsi modul uji',
        'order' => 1,
        'is_published' => true,
    ]);

    $this->lesson = Lesson::create([
        'module_id' => $this->module->id,
        'title' => 'Pelajaran Uji',
        'slug' => 'pelajaran-uji',
        'content' => 'Konten uji',
        'order' => 1,
    ]);
});

test('penilaian latihan system_state: jawaban benar lulus dengan skor penuh (FR-EX-02, VAL-01 s.d. 05)', function () {
    $user = User::factory()->create();

    $exercise = Exercise::create([
        'lesson_id' => $this->lesson->id,
        'title' => 'Latihan System State: Membuat file hello.txt',
        'instruction' => 'Buat file hello.txt berisi Halo Linux',
        'validation_type' => 'system_state',
        'validation_config' => [
            'checks' => [
                ['type' => 'file_exists', 'path' => '/home/student/latihan/hello.txt'],
                ['type' => 'file_contains', 'path' => '/home/student/latihan/hello.txt', 'text' => 'Halo Linux'],
            ],
        ],
        'points' => 100,
        'order' => 1,
    ]);

    $session = TerminalSession::create([
        'user_id' => $user->id,
        'exercise_id' => $exercise->id,
        'container_id' => 'dummy-container-1',
        'status' => 'active',
        'ws_token' => 'dummy-ws-token-1',
        'expires_at' => Carbon::now()->addMinutes(30),
    ]);

    $mockTerminal = Mockery::mock(TerminalService::class);
    $mockTerminal->shouldReceive('executeCommand')
        ->with(Mockery::type(TerminalSession::class), 'test -f \'/home/student/latihan/hello.txt\'')
        ->andReturn(['exit_code' => 0, 'output' => '', 'error' => '']);
    $mockTerminal->shouldReceive('executeCommand')
        ->with(Mockery::type(TerminalSession::class), 'test -f \'/home/student/latihan/hello.txt\'')
        ->andReturn(['exit_code' => 0, 'output' => '', 'error' => '']);
    $mockTerminal->shouldReceive('executeCommand')
        ->with(Mockery::type(TerminalSession::class), 'grep -F -q \'Halo Linux\' \'/home/student/latihan/hello.txt\'')
        ->andReturn(['exit_code' => 0, 'output' => '', 'error' => '']);

    $this->app->instance(TerminalService::class, $mockTerminal);
    $this->app->forgetInstance(ValidationService::class);

    $response = $this->actingAs($user)->postJson(route('exercises.check', $exercise));

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'is_correct' => true,
            'score' => 100,
        ]);

    $this->assertDatabaseHas('exercise_attempts', [
        'user_id' => $user->id,
        'exercise_id' => $exercise->id,
        'terminal_session_id' => $session->id,
        'is_correct' => true,
        'score' => 100,
    ]);
});

test('penilaian latihan system_state: jawaban salah gagal dengan skor 0 (FR-EX-02, VAL-01 s.d. 05)', function () {
    $user = User::factory()->create();

    $exercise = Exercise::create([
        'lesson_id' => $this->lesson->id,
        'title' => 'Latihan System State: Membuat file hello.txt',
        'instruction' => 'Buat file hello.txt berisi Halo Linux',
        'validation_type' => 'system_state',
        'validation_config' => [
            'checks' => [
                ['type' => 'file_exists', 'path' => '/home/student/latihan/hello.txt'],
                ['type' => 'file_contains', 'path' => '/home/student/latihan/hello.txt', 'text' => 'Halo Linux'],
            ],
        ],
        'points' => 100,
        'order' => 1,
    ]);

    $session = TerminalSession::create([
        'user_id' => $user->id,
        'exercise_id' => $exercise->id,
        'container_id' => 'dummy-container-1-err',
        'status' => 'active',
        'ws_token' => 'dummy-ws-token-1-err',
        'expires_at' => Carbon::now()->addMinutes(30),
    ]);

    $mockTerminal = Mockery::mock(TerminalService::class);
    // file tidak ditemukan
    $mockTerminal->shouldReceive('executeCommand')
        ->with(Mockery::type(TerminalSession::class), 'test -f \'/home/student/latihan/hello.txt\'')
        ->andReturn(['exit_code' => 1, 'output' => '', 'error' => 'No such file']);

    $this->app->instance(TerminalService::class, $mockTerminal);
    $this->app->forgetInstance(ValidationService::class);

    $response = $this->actingAs($user)->postJson(route('exercises.check', $exercise));

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'is_correct' => false,
            'score' => 0,
        ]);

    $this->assertDatabaseHas('exercise_attempts', [
        'user_id' => $user->id,
        'exercise_id' => $exercise->id,
        'terminal_session_id' => $session->id,
        'is_correct' => false,
        'score' => 0,
    ]);
});

test('penilaian latihan output: jawaban benar lulus dengan skor penuh (FR-EX-02, VAL-01 s.d. 05)', function () {
    $user = User::factory()->create();

    $exercise = Exercise::create([
        'lesson_id' => $this->lesson->id,
        'title' => 'Latihan Output: Menghitung kata linux',
        'instruction' => 'Gunakan grep -c',
        'validation_type' => 'output',
        'validation_config' => [
            'checks' => [
                ['type' => 'command_output_equals', 'command' => "grep -c 'linux' /home/student/kata.txt", 'text' => '3'],
            ],
        ],
        'points' => 100,
        'order' => 2,
    ]);

    $session = TerminalSession::create([
        'user_id' => $user->id,
        'container_id' => 'dummy-container-2',
        'status' => 'active',
        'ws_token' => 'dummy-ws-token-2',
        'expires_at' => Carbon::now()->addMinutes(30),
    ]);

    $mockTerminal = Mockery::mock(TerminalService::class);
    $mockTerminal->shouldReceive('executeCommand')
        ->with(Mockery::type(TerminalSession::class), "grep -c 'linux' /home/student/kata.txt")
        ->andReturn(['exit_code' => 0, 'output' => "3\n", 'error' => '']);

    $this->app->instance(TerminalService::class, $mockTerminal);
    $this->app->forgetInstance(ValidationService::class);

    $response = $this->actingAs($user)->postJson(route('exercises.check', $exercise));

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'is_correct' => true,
            'score' => 100,
        ]);

    $this->assertDatabaseHas('exercise_attempts', [
        'user_id' => $user->id,
        'exercise_id' => $exercise->id,
        'terminal_session_id' => $session->id,
        'is_correct' => true,
        'score' => 100,
    ]);
});

test('penilaian latihan output: jawaban salah gagal dengan skor 0 (FR-EX-02, VAL-01 s.d. 05)', function () {
    $user = User::factory()->create();

    $exercise = Exercise::create([
        'lesson_id' => $this->lesson->id,
        'title' => 'Latihan Output: Menghitung kata linux',
        'instruction' => 'Gunakan grep -c',
        'validation_type' => 'output',
        'validation_config' => [
            'checks' => [
                ['type' => 'command_output_equals', 'command' => "grep -c 'linux' /home/student/kata.txt", 'text' => '3'],
            ],
        ],
        'points' => 100,
        'order' => 2,
    ]);

    $session = TerminalSession::create([
        'user_id' => $user->id,
        'container_id' => 'dummy-container-2-err',
        'status' => 'active',
        'ws_token' => 'dummy-ws-token-2-err',
        'expires_at' => Carbon::now()->addMinutes(30),
    ]);

    $mockTerminal = Mockery::mock(TerminalService::class);
    $mockTerminal->shouldReceive('executeCommand')
        ->with(Mockery::type(TerminalSession::class), "grep -c 'linux' /home/student/kata.txt")
        ->andReturn(['exit_code' => 0, 'output' => "1\n", 'error' => '']);

    $this->app->instance(TerminalService::class, $mockTerminal);
    $this->app->forgetInstance(ValidationService::class);

    $response = $this->actingAs($user)->postJson(route('exercises.check', $exercise));

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'is_correct' => false,
            'score' => 0,
        ]);

    $this->assertDatabaseHas('exercise_attempts', [
        'user_id' => $user->id,
        'exercise_id' => $exercise->id,
        'terminal_session_id' => $session->id,
        'is_correct' => false,
        'score' => 0,
    ]);
});

test('penilaian latihan combined: jawaban benar lulus dengan skor penuh (FR-EX-02, VAL-01 s.d. 05)', function () {
    $user = User::factory()->create();

    $exercise = Exercise::create([
        'lesson_id' => $this->lesson->id,
        'title' => 'Latihan Combined: Direktori dan file redirection',
        'instruction' => 'Buat folder data dan file hasil.txt',
        'validation_type' => 'combined',
        'validation_config' => [
            'checks' => [
                ['type' => 'directory_exists', 'path' => '/home/student/latihan/data'],
                ['type' => 'file_exists', 'path' => '/home/student/latihan/data/hasil.txt'],
            ],
        ],
        'points' => 100,
        'order' => 3,
    ]);

    $session = TerminalSession::create([
        'user_id' => $user->id,
        'container_id' => 'dummy-container-3',
        'status' => 'active',
        'ws_token' => 'dummy-ws-token-3',
        'expires_at' => Carbon::now()->addMinutes(30),
    ]);

    $mockTerminal = Mockery::mock(TerminalService::class);
    $mockTerminal->shouldReceive('executeCommand')
        ->with(Mockery::type(TerminalSession::class), 'test -d \'/home/student/latihan/data\'')
        ->andReturn(['exit_code' => 0, 'output' => '', 'error' => '']);
    $mockTerminal->shouldReceive('executeCommand')
        ->with(Mockery::type(TerminalSession::class), 'test -f \'/home/student/latihan/data/hasil.txt\'')
        ->andReturn(['exit_code' => 0, 'output' => '', 'error' => '']);

    $this->app->instance(TerminalService::class, $mockTerminal);
    $this->app->forgetInstance(ValidationService::class);

    $response = $this->actingAs($user)->postJson(route('exercises.check', $exercise));

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'is_correct' => true,
            'score' => 100,
        ]);

    $this->assertDatabaseHas('exercise_attempts', [
        'user_id' => $user->id,
        'exercise_id' => $exercise->id,
        'terminal_session_id' => $session->id,
        'is_correct' => true,
        'score' => 100,
    ]);
});

test('penilaian latihan combined: jawaban salah gagal dengan skor 0 (FR-EX-02, VAL-01 s.d. 05)', function () {
    $user = User::factory()->create();

    $exercise = Exercise::create([
        'lesson_id' => $this->lesson->id,
        'title' => 'Latihan Combined: Direktori dan file redirection',
        'instruction' => 'Buat folder data dan file hasil.txt',
        'validation_type' => 'combined',
        'validation_config' => [
            'checks' => [
                ['type' => 'directory_exists', 'path' => '/home/student/latihan/data'],
                ['type' => 'file_exists', 'path' => '/home/student/latihan/data/hasil.txt'],
            ],
        ],
        'points' => 100,
        'order' => 3,
    ]);

    $session = TerminalSession::create([
        'user_id' => $user->id,
        'container_id' => 'dummy-container-3-err',
        'status' => 'active',
        'ws_token' => 'dummy-ws-token-3-err',
        'expires_at' => Carbon::now()->addMinutes(30),
    ]);

    $mockTerminal = Mockery::mock(TerminalService::class);
    $mockTerminal->shouldReceive('executeCommand')
        ->with(Mockery::type(TerminalSession::class), 'test -d \'/home/student/latihan/data\'')
        ->andReturn(['exit_code' => 0, 'output' => '', 'error' => '']);
    $mockTerminal->shouldReceive('executeCommand')
        ->with(Mockery::type(TerminalSession::class), 'test -f \'/home/student/latihan/data/hasil.txt\'')
        ->andReturn(['exit_code' => 1, 'output' => '', 'error' => '']);

    $this->app->instance(TerminalService::class, $mockTerminal);
    $this->app->forgetInstance(ValidationService::class);

    $response = $this->actingAs($user)->postJson(route('exercises.check', $exercise));

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'is_correct' => false,
            'score' => 0,
        ]);

    $this->assertDatabaseHas('exercise_attempts', [
        'user_id' => $user->id,
        'exercise_id' => $exercise->id,
        'terminal_session_id' => $session->id,
        'is_correct' => false,
        'score' => 0,
    ]);
});

test('pemeriksaan tanpa sesi terminal aktif menolak dengan pesan jelas dan mencatat attempt (VAL-06, D-08, FR-EX-02)', function () {
    $user = User::factory()->create();

    $exercise = Exercise::create([
        'lesson_id' => $this->lesson->id,
        'title' => 'Latihan Tanpa Sesi',
        'instruction' => 'Instruksi',
        'validation_type' => 'output',
        'validation_config' => ['checks' => []],
        'points' => 100,
        'order' => 4,
    ]);

    // Tidak ada sesi terminal aktif
    $response = $this->actingAs($user)->postJson(route('exercises.check', $exercise));

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'is_correct' => false,
            'feedback' => 'Tidak ada sesi terminal aktif. Silakan mulai sesi praktik terlebih dahulu.',
        ]);

    // Wajib mencatat satu baris pada exercise_attempts (FR-EX-02 AC)
    $this->assertDatabaseHas('exercise_attempts', [
        'user_id' => $user->id,
        'exercise_id' => $exercise->id,
        'terminal_session_id' => null,
        'is_correct' => false,
        'score' => 0,
        'feedback' => 'Tidak ada sesi terminal aktif. Silakan mulai sesi praktik terlebih dahulu.',
    ]);
});

test('hint bertahap ditampilkan satu per satu dan tidak mengurangi skor bawaan (FR-EX-03, D-06)', function () {
    $user = User::factory()->create();

    $exercise = Exercise::create([
        'lesson_id' => $this->lesson->id,
        'title' => 'Latihan Dengan Hint',
        'instruction' => 'Instruksi',
        'validation_type' => 'system_state',
        'validation_config' => ['checks' => []],
        'points' => 100,
        'order' => 5,
    ]);

    Hint::create([
        'exercise_id' => $exercise->id,
        'content' => 'Petunjuk pertama',
        'order' => 1,
    ]);

    Hint::create([
        'exercise_id' => $exercise->id,
        'content' => 'Petunjuk kedua',
        'order' => 2,
    ]);

    // Permintaan Hint Pertama
    $resp1 = $this->actingAs($user)->postJson(route('exercises.hints', $exercise));
    $resp1->assertStatus(200)
        ->assertJson([
            'success' => true,
            'remaining_hints' => 1,
            'total_hints' => 2,
        ]);
    expect($resp1->json('unlocked_hints'))->toHaveCount(1);
    expect($resp1->json('unlocked_hints.0.content'))->toBe('Petunjuk pertama');

    // Permintaan Hint Kedua
    $resp2 = $this->actingAs($user)->postJson(route('exercises.hints', $exercise));
    $resp2->assertStatus(200)
        ->assertJson([
            'success' => true,
            'remaining_hints' => 0,
            'total_hints' => 2,
        ]);
    expect($resp2->json('unlocked_hints'))->toHaveCount(2);
    expect($resp2->json('unlocked_hints.1.content'))->toBe('Petunjuk kedua');
});

test('perintah pemeriksa hanya berasal dari validation_config yang ditulis admin, bukan input pengguna (VAL-02)', function () {
    $user = User::factory()->create();

    $exercise = Exercise::create([
        'lesson_id' => $this->lesson->id,
        'title' => 'Latihan Keamanan Admin Command',
        'instruction' => 'Instruksi',
        'validation_type' => 'output',
        'validation_config' => [
            'checks' => [
                ['type' => 'command_output_contains', 'command' => 'echo admin_trusted_command', 'text' => 'admin_trusted_command'],
            ],
        ],
        'points' => 100,
        'order' => 6,
    ]);

    $session = TerminalSession::create([
        'user_id' => $user->id,
        'container_id' => 'dummy-container-sec',
        'status' => 'active',
        'ws_token' => 'dummy-ws-token-sec',
        'expires_at' => Carbon::now()->addMinutes(30),
    ]);

    // User mencoba mengirim input payload palsu 'malicious_command'
    $mockTerminal = Mockery::mock(TerminalService::class);
    // ValidationService HARUS tetap menjalankan perintah admin_trusted_command, BUKAN payload user
    $mockTerminal->shouldReceive('executeCommand')
        ->with(Mockery::type(TerminalSession::class), 'echo admin_trusted_command')
        ->once()
        ->andReturn(['exit_code' => 0, 'output' => 'admin_trusted_command', 'error' => '']);

    $this->app->instance(TerminalService::class, $mockTerminal);
    $this->app->forgetInstance(ValidationService::class);

    $response = $this->actingAs($user)->postJson(route('exercises.check', $exercise), [
        'command' => 'rm -rf /',
        'user_input' => 'malicious_input',
    ]);

    $response->assertStatus(200)->assertJson(['is_correct' => true]);
});

test('pemeriksaan jenis file_permission memeriksa mode berkas dengan benar', function () {
    $user = User::factory()->create();

    $exercise = Exercise::create([
        'lesson_id' => $this->lesson->id,
        'title' => 'Latihan Permission',
        'instruction' => 'Instruksi',
        'validation_type' => 'system_state',
        'validation_config' => [
            'checks' => [
                ['type' => 'file_permission', 'path' => '/home/student/latihan/run.sh', 'mode' => '755'],
            ],
        ],
        'points' => 100,
        'order' => 7,
    ]);

    $session = TerminalSession::create([
        'user_id' => $user->id,
        'container_id' => 'dummy-container-perm',
        'status' => 'active',
        'ws_token' => 'dummy-ws-token-perm',
        'expires_at' => Carbon::now()->addMinutes(30),
    ]);

    $mockTerminal = Mockery::mock(TerminalService::class);
    $mockTerminal->shouldReceive('executeCommand')
        ->with(Mockery::type(TerminalSession::class), 'test -e \'/home/student/latihan/run.sh\'')
        ->andReturn(['exit_code' => 0, 'output' => '', 'error' => '']);
    $mockTerminal->shouldReceive('executeCommand')
        ->with(Mockery::type(TerminalSession::class), 'stat -c "%a" \'/home/student/latihan/run.sh\'')
        ->andReturn(['exit_code' => 0, 'output' => "755\n", 'error' => '']);

    $this->app->instance(TerminalService::class, $mockTerminal);
    $this->app->forgetInstance(ValidationService::class);

    $response = $this->actingAs($user)->postJson(route('exercises.check', $exercise));

    $response->assertStatus(200)->assertJson([
        'is_correct' => true,
        'score' => 100,
    ]);
});

test('skor parsial dihitung dengan benar jika partial true (VAL-03)', function () {
    $user = User::factory()->create();

    $exercise = Exercise::create([
        'lesson_id' => $this->lesson->id,
        'title' => 'Latihan Partial',
        'instruction' => 'Instruksi',
        'validation_type' => 'system_state',
        'validation_config' => [
            'partial' => true,
            'checks' => [
                ['type' => 'directory_exists', 'path' => '/home/student/proyek'],
                ['type' => 'directory_exists', 'path' => '/home/student/proyek/backup'],
            ],
        ],
        'points' => 100,
        'order' => 8,
    ]);

    $session = TerminalSession::create([
        'user_id' => $user->id,
        'container_id' => 'dummy-container-partial',
        'status' => 'active',
        'ws_token' => 'dummy-ws-token-partial',
        'expires_at' => Carbon::now()->addMinutes(30),
    ]);

    // 1 check lulus, 1 gagal -> 50 poin
    $mockTerminal = Mockery::mock(TerminalService::class);
    $mockTerminal->shouldReceive('executeCommand')
        ->with(Mockery::type(TerminalSession::class), 'test -d \'/home/student/proyek\'')
        ->andReturn(['exit_code' => 0, 'output' => '', 'error' => '']);
    $mockTerminal->shouldReceive('executeCommand')
        ->with(Mockery::type(TerminalSession::class), 'test -d \'/home/student/proyek/backup\'')
        ->andReturn(['exit_code' => 1, 'output' => '', 'error' => '']);

    $this->app->instance(TerminalService::class, $mockTerminal);
    $this->app->forgetInstance(ValidationService::class);

    $response = $this->actingAs($user)->postJson(route('exercises.check', $exercise));

    $response->assertStatus(200)->assertJson([
        'is_correct' => false,
        'score' => 50,
    ]);
});

test('tidak dapat menilai sesi milik pengguna lain (VAL-01)', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $exercise = Exercise::create([
        'lesson_id' => $this->lesson->id,
        'title' => 'Latihan Isolasi Sesi',
        'instruction' => 'Instruksi',
        'validation_type' => 'output',
        'validation_config' => ['checks' => []],
        'points' => 100,
        'order' => 9,
    ]);

    // Sesi milik user2
    $sessionUser2 = TerminalSession::create([
        'user_id' => $user2->id,
        'container_id' => 'dummy-container-u2',
        'status' => 'active',
        'ws_token' => 'dummy-token-u2',
        'expires_at' => Carbon::now()->addMinutes(30),
    ]);

    $validationService = app(ValidationService::class);

    // Memanggil validate untuk user1 dengan sesi user2 harus melempar AuthorizationException
    expect(fn () => $validationService->validate($user1, $exercise, $sessionUser2))
        ->toThrow(\Illuminate\Auth\Access\AuthorizationException::class);
});

