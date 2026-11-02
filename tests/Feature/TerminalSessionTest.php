<?php

use App\Models\TerminalSession;
use App\Models\User;
use App\Services\TerminalService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Pastikan konfigurasi terminal menggunakan nilai pengujian yang aman
    config([
        'terminal.image' => 'linuxlab-terminal:latest',
        'terminal.cpu_limit' => '0.5',
        'terminal.memory_limit' => '256m',
        'terminal.session_ttl' => 30,
        'terminal.max_sessions' => 3,
        'terminal.gateway_secret' => null,
    ]);
});

afterEach(function () {
    // Bersihkan semua container pengujian jika ada
    $sessions = TerminalSession::all();
    $terminalService = app(TerminalService::class);
    foreach ($sessions as $session) {
        if ($session->container_id) {
            $terminalService->destroyContainer($session->container_id);
        }
    }
});

test('pengunjung belum login tidak dapat membuat sesi terminal', function () {
    $response = $this->postJson(route('terminal.sessions.store'));

    $response->assertStatus(401);
});

test('pengguna terautentikasi dapat membuat sesi terminal baru (FR-TERM-01)', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson(route('terminal.sessions.store'));

    $response->assertStatus(201)
        ->assertJsonStructure([
            'success',
            'session' => [
                'id',
                'status',
                'expires_at',
                'remaining_seconds',
            ],
            'ws_token',
            'ws_port',
        ]);

    $this->assertDatabaseHas('terminal_sessions', [
        'user_id' => $user->id,
        'status' => 'active',
    ]);

    $session = TerminalSession::where('user_id', $user->id)->first();
    expect($session->container_id)->not->toBeEmpty();
    expect($session->ws_token)->not->toBeEmpty();
    expect($session->isActive())->toBeTrue();
});

test('satu pengguna hanya dapat memiliki satu sesi aktif (FR-TERM-01, D-03)', function () {
    $user = User::factory()->create();

    // Sesi pertama
    $resp1 = $this->actingAs($user)->postJson(route('terminal.sessions.store'));
    $resp1->assertStatus(201);
    $session1Id = $resp1->json('session.id');

    // Sesi kedua oleh pengguna yang sama
    $resp2 = $this->actingAs($user)->postJson(route('terminal.sessions.store'));
    $resp2->assertStatus(201);
    $session2Id = $resp2->json('session.id');

    expect($session2Id)->not->toBe($session1Id);

    // Sesi 1 harus berstatus terminated
    $session1 = TerminalSession::find($session1Id);
    expect($session1->status)->toBe('terminated');
    expect($session1->ended_at)->not->toBeNull();

    // Sesi 2 harus aktif
    $session2 = TerminalSession::find($session2Id);
    expect($session2->status)->toBe('active');
});

test('eksekusi perintah Linux nyata berjalan di container terisolasi (FR-TERM-02, SEC-TERM-07)', function () {
    $user = User::factory()->create();
    $terminalService = app(TerminalService::class);

    $session = $terminalService->createSession($user);

    // Eksekusi whoami di dalam container
    $resultWhoami = $terminalService->executeCommand($session, 'whoami');
    expect($resultWhoami['exit_code'])->toBe(0);
    expect(trim($resultWhoami['output']))->toBe('student');

    // Eksekusi pwd di dalam container
    $resultPwd = $terminalService->executeCommand($session, 'pwd');
    expect($resultPwd['exit_code'])->toBe(0);
    expect(trim($resultPwd['output']))->toBe('/home/student');
});

test('pengguna hanya dapat mengakses dan mengakhiri sesi miliknya sendiri (SEC-TERM-09)', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $terminalService = app(TerminalService::class);
    $sessionA = $terminalService->createSession($userA);

    // User B mencoba melihat sesi User A -> ditolak 403
    $this->actingAs($userB)
        ->getJson(route('terminal.sessions.show', $sessionA))
        ->assertStatus(403);

    // User B mencoba menghapus sesi User A -> ditolak 403
    $this->actingAs($userB)
        ->deleteJson(route('terminal.sessions.destroy', $sessionA))
        ->assertStatus(403);

    // Sesi User A tetap aktif
    expect($sessionA->fresh()->status)->toBe('active');

    // User A mengakhiri sesinya sendiri -> berhasil 200 (FR-TERM-04)
    $this->actingAs($userA)
        ->deleteJson(route('terminal.sessions.destroy', $sessionA))
        ->assertStatus(200);

    expect($sessionA->fresh()->status)->toBe('terminated');
});

test('perintah clean-expired membersihkan sesi yang telah melewati expires_at (FR-TERM-04)', function () {
    $user = User::factory()->create();
    $terminalService = app(TerminalService::class);

    $session = $terminalService->createSession($user);

    // Ubah expires_at menjadi waktu lampau
    $session->update([
        'expires_at' => Carbon::now()->subMinutes(5),
    ]);

    expect($session->fresh()->status)->toBe('active');

    // Jalankan artisan terminal:clean-expired
    $this->artisan('terminal:clean-expired')
        ->assertSuccessful();

    // Sesi harus berubah menjadi expired
    expect($session->fresh()->status)->toBe('expired');
    expect($session->fresh()->ended_at)->not->toBeNull();
});

test('pembuatan sesi ditolak saat server mencapai batas maksimal sesi aktif (SEC-TERM-08)', function () {
    config(['terminal.max_sessions' => 2]);

    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $user3 = User::factory()->create();

    // Buat 2 sesi aktif (maksimal kuota)
    $this->actingAs($user1)->postJson(route('terminal.sessions.store'))->assertStatus(201);
    $this->actingAs($user2)->postJson(route('terminal.sessions.store'))->assertStatus(201);

    // User 3 mencoba membuat sesi -> ditolak dengan status 429
    $resp3 = $this->actingAs($user3)->postJson(route('terminal.sessions.store'));
    $resp3->assertStatus(429)
        ->assertJson([
            'success' => false,
            'message' => 'Server praktik sedang penuh, coba lagi sesaat lagi.',
        ]);
});

test('verifikasi internal token untuk gateway hanya mengizinkan localhost (SEC-TERM-01)', function () {
    $user = User::factory()->create();
    $terminalService = app(TerminalService::class);
    $session = $terminalService->createSession($user);

    // Akses dari 127.0.0.1 dengan token yang sah -> 200
    $resp = $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
        ->getJson(route('internal.terminal.verify', ['token' => $session->ws_token]));

    $resp->assertStatus(200)
        ->assertJson([
            'valid' => true,
            'session_id' => $session->id,
            'user_id' => $user->id,
        ]);

    // Akses dengan token ngawur -> 404
    $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
        ->getJson(route('internal.terminal.verify', ['token' => 'token-palsu']))
        ->assertStatus(404);

    // Akses dari IP publik luar (mis. 192.168.1.50) -> ditolak 403
    $this->withServerVariables(['REMOTE_ADDR' => '192.168.1.50'])
        ->getJson(route('internal.terminal.verify', ['token' => $session->ws_token]))
        ->assertStatus(403);
});

test('verifikasi internal menolak request tanpa secret gateway yang benar (SEC-TERM-01)', function () {
    config(['terminal.gateway_secret' => 'rahasia-gateway-uji']);

    $user = User::factory()->create();
    $terminalService = app(TerminalService::class);
    $session = $terminalService->createSession($user);

    $params = ['token' => $session->ws_token];

    // Tanpa header secret -> 403 walau dari localhost
    $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
        ->getJson(route('internal.terminal.verify', $params), ['X-Gateway-Secret' => ''])
        ->assertStatus(403);

    // Secret salah -> 403
    $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
        ->getJson(route('internal.terminal.verify', $params), ['X-Gateway-Secret' => 'salah'])
        ->assertStatus(403);

    // Secret benar -> 200
    $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
        ->getJson(route('internal.terminal.verify', $params), ['X-Gateway-Secret' => 'rahasia-gateway-uji'])
        ->assertStatus(200)
        ->assertJson(['valid' => true, 'session_id' => $session->id]);
});
