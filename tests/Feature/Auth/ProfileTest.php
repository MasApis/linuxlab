<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('halaman profil dapat dibuka oleh pengguna yang login (FR-AUTH-03)', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/profile');

    $response->assertStatus(200);
});

test('pengguna dapat memperbarui nama miliknya sendiri', function () {
    $user = User::factory()->create([
        'name' => 'Nama Lama',
        'email' => 'profil@linuxlab.local',
    ]);

    $response = $this->actingAs($user)->patch('/profile', [
        'name' => 'Nama Baru Yang Keren',
        'email' => 'profil@linuxlab.local',
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect('/profile');

    $user->refresh();
    expect($user->name)->toBe('Nama Baru Yang Keren');
});

test('pengguna dapat memperbarui password miliknya sendiri', function () {
    $user = User::factory()->create([
        'password' => 'passwordLama123',
    ]);

    $response = $this->actingAs($user)
        ->from('/profile')
        ->put('/password', [
            'current_password' => 'passwordLama123',
            'password' => 'passwordBaru456',
            'password_confirmation' => 'passwordBaru456',
        ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect('/profile');

    $user->refresh();
    expect(Hash::check('passwordBaru456', $user->password))->toBeTrue();
});

test('pembaruan password ditolak jika password saat ini salah', function () {
    $user = User::factory()->create([
        'password' => 'passwordBenar123',
    ]);

    $response = $this->actingAs($user)
        ->from('/profile')
        ->put('/password', [
            'current_password' => 'passwordSalahTotal',
            'password' => 'passwordBaru456',
            'password_confirmation' => 'passwordBaru456',
        ]);

    $response->assertSessionHasErrors('current_password');

    $user->refresh();
    expect(Hash::check('passwordBenar123', $user->password))->toBeTrue();
});
