<?php

use App\Models\Lesson;
use App\Models\Module;
use App\Models\User;
use Illuminate\Database\QueryException;
use Inertia\Testing\AssertableInertia as Assert;

test('tamu diarahkan ke login saat mengakses halaman modul dan pelajaran', function () {
    $module = Module::factory()->create(['is_published' => true]);
    $lesson = Lesson::factory()->create(['module_id' => $module->id]);

    $this->get('/modules')->assertRedirect('/login');
    $this->get("/modules/{$module->slug}")->assertRedirect('/login');
    $this->get("/modules/{$module->slug}/lessons/{$lesson->slug}")->assertRedirect('/login');
    $this->get("/lessons/{$lesson->slug}/practice")->assertRedirect('/login');
});

test('pengguna terdaftar dapat melihat daftar modul', function () {
    $user = User::factory()->create();
    Module::factory()->create(['title' => 'Modul Satu', 'order' => 1, 'is_published' => true]);
    Module::factory()->create(['title' => 'Modul Dua', 'order' => 2, 'is_published' => true]);

    $response = $this->actingAs($user)->get('/modules');

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Modules/Index')
        ->has('modules', 2)
        ->where('modules.0.title', 'Modul Satu')
        ->where('modules.1.title', 'Modul Dua')
    );
});

test('modul yang tidak dipublikasikan tidak muncul di daftar modul dan mengembalikan 404', function () {
    $user = User::factory()->create();
    $unpublished = Module::factory()->create(['title' => 'Modul Rahasia', 'is_published' => false]);
    Module::factory()->create(['title' => 'Modul Publik', 'is_published' => true]);

    $indexResponse = $this->actingAs($user)->get('/modules');
    $indexResponse->assertOk();
    $indexResponse->assertInertia(fn (Assert $page) => $page
        ->component('Modules/Index')
        ->has('modules', 1)
        ->where('modules.0.title', 'Modul Publik')
    );

    $showResponse = $this->actingAs($user)->get("/modules/{$unpublished->slug}");
    $showResponse->assertNotFound();
});

test('pengguna dapat melihat detail modul dan daftar pelajarannya', function () {
    $user = User::factory()->create();
    $module = Module::factory()->create(['title' => 'Modul File System', 'is_published' => true]);
    Lesson::factory()->create([
        'module_id' => $module->id,
        'title' => 'Pelajaran Navigasi',
        'order' => 1,
    ]);

    $response = $this->actingAs($user)->get("/modules/{$module->slug}");

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Modules/Show')
        ->where('module.title', 'Modul File System')
        ->has('module.lessons', 1)
        ->where('module.lessons.0.title', 'Pelajaran Navigasi')
    );
});

test('urutan pelajaran mengikuti kolom order', function () {
    $module = Module::factory()->create(['is_published' => true]);

    // Buat pelajaran secara acak/tidak berurutan
    $lesson3 = Lesson::factory()->create([
        'module_id' => $module->id,
        'title' => 'Pelajaran Ketiga',
        'order' => 3,
    ]);
    $lesson1 = Lesson::factory()->create([
        'module_id' => $module->id,
        'title' => 'Pelajaran Pertama',
        'order' => 1,
    ]);
    $lesson2 = Lesson::factory()->create([
        'module_id' => $module->id,
        'title' => 'Pelajaran Kedua',
        'order' => 2,
    ]);

    // Verifikasi relasi Eloquent
    $orderedLessons = $module->lessons()->get();
    expect($orderedLessons->pluck('id')->all())->toBe([$lesson1->id, $lesson2->id, $lesson3->id]);
    expect($orderedLessons->pluck('order')->all())->toBe([1, 2, 3]);

    // Verifikasi respon HTTP Controller
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get("/modules/{$module->slug}");
    $response->assertOk();

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Modules/Show')
        ->where('module.lessons.0.id', $lesson1->id)
        ->where('module.lessons.1.id', $lesson2->id)
        ->where('module.lessons.2.id', $lesson3->id)
    );
});

test('slug modul harus unik di tingkat database', function () {
    Module::factory()->create(['slug' => 'modul-unik']);

    expect(fn () => Module::factory()->create(['slug' => 'modul-unik']))
        ->toThrow(QueryException::class);
});

test('slug pelajaran harus unik di tingkat database', function () {
    $module = Module::factory()->create();

    Lesson::factory()->create(['slug' => 'pelajaran-unik', 'module_id' => $module->id]);

    expect(fn () => Lesson::factory()->create(['slug' => 'pelajaran-unik', 'module_id' => $module->id]))
        ->toThrow(QueryException::class);
});

test('halaman materi menampilkan content, example_commands, dan cheat_sheet', function () {
    $user = User::factory()->create();
    $module = Module::factory()->create(['is_published' => true]);
    $lesson = Lesson::factory()->create([
        'module_id' => $module->id,
        'title' => 'Navigasi Direktori',
        'content' => 'Penjelasan rinci tentang perintah pwd dan ls.',
        'example_commands' => "$ pwd\n/home/student",
        'cheat_sheet' => 'pwd : print working directory',
    ]);

    $response = $this->actingAs($user)->get("/modules/{$module->slug}/lessons/{$lesson->slug}");

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Lessons/Show')
        ->where('lesson.title', 'Navigasi Direktori')
        ->where('lesson.content', 'Penjelasan rinci tentang perintah pwd dan ls.')
        ->where('lesson.example_commands', "$ pwd\n/home/student")
        ->where('lesson.cheat_sheet', 'pwd : print working directory')
    );
});

test('pelajaran dari modul lain mengembalikan 404 jika diakses lewat modul yang salah', function () {
    $user = User::factory()->create();
    $module1 = Module::factory()->create(['is_published' => true]);
    $module2 = Module::factory()->create(['is_published' => true]);
    $lesson1 = Lesson::factory()->create(['module_id' => $module1->id]);

    $response = $this->actingAs($user)->get("/modules/{$module2->slug}/lessons/{$lesson1->slug}");
    $response->assertNotFound();
});

test('tombol mulai praktik mengarah ke route praktik sederhana bertuliskan judul pelajaran', function () {
    $user = User::factory()->create();
    $module = Module::factory()->create(['is_published' => true]);
    $lesson = Lesson::factory()->create([
        'module_id' => $module->id,
        'title' => 'Navigasi Direktori Lanjutan',
    ]);

    $response = $this->actingAs($user)->get("/lessons/{$lesson->slug}/practice");

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Lessons/Practice')
        ->where('lesson.title', 'Navigasi Direktori Lanjutan')
    );
});

test('menghapus modul menghapus pelajaran di bawahnya secara cascade', function () {
    $module = Module::factory()->create();
    $lesson = Lesson::factory()->create(['module_id' => $module->id]);

    expect(Lesson::find($lesson->id))->not->toBeNull();

    $module->delete();

    expect(Lesson::find($lesson->id))->toBeNull();
});
