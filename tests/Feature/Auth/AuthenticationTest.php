<?php

use App\Models\User;

test('halaman login dapat dibuka oleh pengunjung', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('pengguna dapat login menggunakan email dan diarahkan ke dashboard pengguna', function () {
    $user = User::factory()->create([
        'email' => 'user@linuxlab.local',
        'password' => 'password123',
        'role' => 'user',
        'is_active' => true,
    ]);

    $response = $this->post('/login', [
        'login' => 'user@linuxlab.local',
        'password' => 'password123',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect('/dashboard');
});

test('pengguna dapat login menggunakan username dan diarahkan ke dashboard pengguna', function () {
    $user = User::factory()->create([
        'username' => 'sysadmin_muda',
        'password' => 'password123',
        'role' => 'user',
        'is_active' => true,
    ]);

    $response = $this->post('/login', [
        'login' => 'sysadmin_muda',
        'password' => 'password123',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect('/dashboard');
});

test('admin yang login diarahkan ke dashboard admin', function () {
    $admin = User::factory()->admin()->create([
        'username' => 'admin_utama',
        'email' => 'admin@linuxlab.local',
        'password' => 'password123',
    ]);

    $response = $this->post('/login', [
        'login' => 'admin_utama',
        'password' => 'password123',
    ]);

    $this->assertAuthenticatedAs($admin);
    $response->assertRedirect('/admin');
});

test('login dengan password yang salah ditolak dengan pesan netral', function () {
    $user = User::factory()->create([
        'email' => 'user@linuxlab.local',
        'password' => 'password123',
    ]);

    $response = $this->post('/login', [
        'login' => 'user@linuxlab.local',
        'password' => 'password-salah',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('login');

    // FR-AUTH-02 AC: kredensial salah mengembalikan pesan kesalahan yang tidak membocorkan apakah email atau password yang salah
    $errorMessage = session('errors')->get('login')[0];
    expect($errorMessage)->toBe('Email/username atau password yang Anda masukkan salah.');
});

test('login dengan akun yang tidak terdaftar ditolak dengan pesan yang sama', function () {
    $response = $this->post('/login', [
        'login' => 'tidak_ada@linuxlab.local',
        'password' => 'sembarang',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('login');

    $errorMessage = session('errors')->get('login')[0];
    expect($errorMessage)->toBe('Email/username atau password yang Anda masukkan salah.');
});

test('akun nonaktif ditolak saat mencoba login', function () {
    User::factory()->inactive()->create([
        'username' => 'user_nonaktif',
        'password' => 'password123',
    ]);

    $response = $this->post('/login', [
        'login' => 'user_nonaktif',
        'password' => 'password123',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('login');
    expect(session('errors')->get('login')[0])->toContain('dinonaktifkan');
});

test('pengguna dapat logout dan diarahkan ke halaman utama', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});
