<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('halaman registrasi dapat dibuka oleh pengunjung', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('pengunjung dapat mendaftarkan akun baru dengan data yang valid', function () {
    $response = $this->post('/register', [
        'name' => 'Linus Torvalds',
        'username' => 'linus_t',
        'email' => 'linus@example.com',
        'password' => 'rahasia123',
        'password_confirmation' => 'rahasia123',
    ]);

    // FR-AUTH-01 AC: setelah registrasi berhasil, pengguna diarahkan ke halaman login dengan pesan sukses.
    $response->assertRedirect('/login');
    $response->assertSessionHas('success');

    // Data tersimpan di users dengan role=user dan is_active=true
    $this->assertDatabaseHas('users', [
        'name' => 'Linus Torvalds',
        'username' => 'linus_t',
        'email' => 'linus@example.com',
        'role' => 'user',
        'is_active' => true,
    ]);

    // AC: password tidak pernah disimpan sebagai teks biasa
    $user = User::where('username', 'linus_t')->first();
    expect($user)->not->toBeNull();
    expect(Hash::check('rahasia123', $user->password))->toBeTrue();
    expect($user->password)->not->toBe('rahasia123');

    // Pengguna belum diautentikasi otomatis (diarahkan untuk login dulu)
    $this->assertGuest();
});

test('pendaftaran dengan email ganda ditolak', function () {
    User::factory()->create([
        'email' => 'terdaftar@example.com',
        'username' => 'user_lama',
    ]);

    $response = $this->post('/register', [
        'name' => 'Pengguna Baru',
        'username' => 'user_baru',
        'email' => 'terdaftar@example.com',
        'password' => 'rahasia123',
        'password_confirmation' => 'rahasia123',
    ]);

    $response->assertSessionHasErrors('email');
    expect(session('errors')->get('email')[0])->toBe('Alamat email ini sudah terdaftar.');
});

test('pendaftaran dengan username ganda ditolak', function () {
    User::factory()->create([
        'username' => 'penguin_lab',
        'email' => 'lama@example.com',
    ]);

    $response = $this->post('/register', [
        'name' => 'Pengguna Baru',
        'username' => 'penguin_lab',
        'email' => 'baru@example.com',
        'password' => 'rahasia123',
        'password_confirmation' => 'rahasia123',
    ]);

    $response->assertSessionHasErrors('username');
    expect(session('errors')->get('username')[0])->toBe('Username ini sudah digunakan.');
});

test('pendaftaran dengan username berformat tidak valid ditolak', function () {
    $response = $this->post('/register', [
        'name' => 'Pengguna Baru',
        'username' => 'user spasi!',
        'email' => 'format@example.com',
        'password' => 'rahasia123',
        'password_confirmation' => 'rahasia123',
    ]);

    $response->assertSessionHasErrors('username');
});

test('pendaftaran dengan konfirmasi password yang tidak cocok ditolak', function () {
    $response = $this->post('/register', [
        'name' => 'Pengguna Baru',
        'username' => 'user_beda',
        'email' => 'beda@example.com',
        'password' => 'rahasia123',
        'password_confirmation' => 'berbeda456',
    ]);

    $response->assertSessionHasErrors('password');
    expect(session('errors')->get('password')[0])->toBe('Konfirmasi password tidak cocok.');
});
