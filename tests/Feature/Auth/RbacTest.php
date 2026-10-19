<?php

use App\Models\User;

test('peran pengguna tersimpan pada kolom role dengan nilai user atau admin (RBAC-01)', function () {
    $regularUser = User::factory()->create(['role' => 'user']);
    $adminUser = User::factory()->admin()->create();

    expect($regularUser->role)->toBe('user');
    expect($regularUser->isAdmin())->toBeFalse();

    expect($adminUser->role)->toBe('admin');
    expect($adminUser->isAdmin())->toBeTrue();
});

test('pengunjung belum login yang membuka route terproteksi diarahkan ke login (RBAC-03)', function () {
    $this->get('/dashboard')->assertRedirect('/login');
    $this->get('/profile')->assertRedirect('/login');
    $this->get('/admin')->assertRedirect('/login');
});

test('pengguna dengan role user ditolak dari route admin dengan status 403 (RBAC-04)', function () {
    $user = User::factory()->create(['role' => 'user']);

    // Mengakses dashboard admin /admin
    $response = $this->actingAs($user)->get('/admin');
    $response->assertStatus(403);

    // Mengakses sub-route admin /admin/modules
    $subResponse = $this->actingAs($user)->get('/admin/modules');
    $subResponse->assertStatus(403);
});

test('pengguna dengan role admin dapat membuka route admin', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/admin');

    $response->assertStatus(200);
});
