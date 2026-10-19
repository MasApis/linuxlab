<?php

use App\Models\Certificate;
use App\Models\Module;
use App\Models\ModuleProgress;
use App\Models\User;
use App\Services\CertificateService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->certificateService = app(CertificateService::class);

    $this->module1 = Module::create([
        'title' => 'Dasar File System',
        'slug' => 'file-system',
        'description' => 'Deskripsi',
        'order' => 1,
        'is_published' => true,
    ]);

    $this->module2 = Module::create([
        'title' => 'Permission',
        'slug' => 'permission',
        'description' => 'Deskripsi',
        'order' => 2,
        'is_published' => true,
    ]);
});

test('sertifikat hanya dapat terbit setelah seluruh modul terbitan berstatus completed (FR-CERT-01)', function () {
    $user = User::factory()->create(['role' => 'user']);

    // Baru modul 1 selesai -> belum berhak
    ModuleProgress::create([
        'user_id' => $user->id,
        'module_id' => $this->module1->id,
        'completion_percentage' => 100,
        'status' => 'completed',
    ]);

    expect($this->certificateService->isEligible($user))->toBeFalse();
    $certNull = $this->certificateService->issueCertificateIfEligible($user);
    expect($certNull)->toBeNull();

    // Selesaikan modul 2 -> berhak
    ModuleProgress::create([
        'user_id' => $user->id,
        'module_id' => $this->module2->id,
        'completion_percentage' => 100,
        'status' => 'completed',
    ]);

    expect($this->certificateService->isEligible($user))->toBeTrue();
    $cert = $this->certificateService->issueCertificateIfEligible($user);

    expect($cert)->not->toBeNull()
        ->and($cert->user_id)->toBe($user->id)
        ->and($cert->recipient_name)->toBe($user->name);
});

test('sertifikat memiliki format kode LLAB-<tahun>-<6 karakter> dan terbit tepat satu kali (FR-CERT-02)', function () {
    $user = User::factory()->create(['role' => 'user']);
    $year = now()->format('Y');

    ModuleProgress::create([
        'user_id' => $user->id,
        'module_id' => $this->module1->id,
        'completion_percentage' => 100,
        'status' => 'completed',
    ]);

    ModuleProgress::create([
        'user_id' => $user->id,
        'module_id' => $this->module2->id,
        'completion_percentage' => 100,
        'status' => 'completed',
    ]);

    // Penerbitan pertama
    $cert1 = $this->certificateService->issueCertificateIfEligible($user);
    expect($cert1)->not->toBeNull();
    expect($cert1->certificate_code)->toMatch("/^LLAB-{$year}-[A-Z0-9]{6}$/");

    // Panggilan kedua tidak membuat baris baru (UNIQUE user_id)
    $cert2 = $this->certificateService->issueCertificateIfEligible($user);
    expect($cert2->id)->toBe($cert1->id);
    expect(Certificate::where('user_id', $user->id)->count())->toBe(1);
});

test('pengguna dapat mengunduh sertifikat miliknya sebagai PDF dan ditolak 403 bila belum punya (FR-CERT-03)', function () {
    $userWithoutCert = User::factory()->create(['role' => 'user']);
    $userWithCert = User::factory()->create(['role' => 'user']);

    Certificate::create([
        'user_id' => $userWithCert->id,
        'recipient_name' => $userWithCert->name,
        'certificate_code' => 'LLAB-2026-ABC123',
        'issued_at' => now(),
    ]);

    // Pengguna tanpa sertifikat ditolak 403
    $this->actingAs($userWithoutCert)
        ->get(route('certificates.download'))
        ->assertForbidden();

    // Pengguna dengan sertifikat berhasil mengunduh file PDF
    $response = $this->actingAs($userWithCert)
        ->get(route('certificates.download'));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/pdf');
});

test('verifikasi publik menyatakan kode valid sebagai Sah dan kode acak sebagai Tidak Ditemukan (FR-CERT-04)', function () {
    $user = User::factory()->create(['role' => 'user', 'name' => 'Siti Nurhaliza']);

    $cert = Certificate::create([
        'user_id' => $user->id,
        'recipient_name' => $user->name,
        'certificate_code' => 'LLAB-2026-TEST99',
        'issued_at' => now(),
    ]);

    // 1. Verifikasi kode valid
    $validResult = $this->certificateService->verifyCertificate('LLAB-2026-TEST99');
    expect($validResult['status'])->toBe('valid')
        ->and($validResult['recipient_name'])->toBe('Siti Nurhaliza')
        ->and($validResult['certificate_code'])->toBe('LLAB-2026-TEST99');

    // Cek route publik dengan kode valid
    $this->get(route('certificates.verify', ['code' => 'LLAB-2026-TEST99']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Certificates/Verify')
            ->where('result.status', 'valid')
            ->where('result.recipient_name', 'Siti Nurhaliza')
        );

    // 2. Verifikasi kode acak yang tidak terdaftar
    $invalidResult = $this->certificateService->verifyCertificate('LLAB-2026-RANDOM');
    expect($invalidResult['status'])->toBe('not_found');

    // Cek route publik dengan kode acak
    $this->get(route('certificates.verify', ['code' => 'LLAB-2026-RANDOM']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Certificates/Verify')
            ->where('result.status', 'not_found')
        );
});
