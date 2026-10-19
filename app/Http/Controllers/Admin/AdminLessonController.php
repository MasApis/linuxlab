<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Module;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AdminLessonController extends Controller
{
    /**
     * Menampilkan daftar pelajaran untuk dikelola administrator (FR-ADMIN-01).
     */
    public function index(Request $request): Response
    {
        $moduleId = $request->query('module_id');

        $query = Lesson::query()
            ->with(['module:id,title,slug'])
            ->withCount('exercises');

        if ($moduleId) {
            $query->where('module_id', $moduleId);
        }

        $lessons = $query->orderBy('module_id')
            ->orderBy('order')
            ->get();

        $modules = Module::query()
            ->orderBy('order')
            ->get(['id', 'title', 'slug', 'order']);

        return Inertia::render('Admin/Lessons/Index', [
            'lessons' => $lessons,
            'modules' => $modules,
            'selectedModuleId' => $moduleId ? (int) $moduleId : null,
        ]);
    }

    /**
     * Menyimpan pelajaran baru (FR-ADMIN-01).
     */
    public function store(Request $request): RedirectResponse
    {
        if (! $request->filled('slug') && $request->filled('title')) {
            $request->merge(['slug' => Str::slug($request->input('title'))]);
        }

        $validated = $request->validate([
            'module_id' => ['required', 'exists:modules,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:lessons,slug'],
            'content' => ['required', 'string'],
            'example_commands' => ['nullable', 'string'],
            'cheat_sheet' => ['nullable', 'string'],
            'order' => ['required', 'integer', 'min:1'],
        ], [
            'module_id.required' => 'Modul induk wajib dipilih.',
            'module_id.exists' => 'Modul yang dipilih tidak ditemukan.',
            'title.required' => 'Judul pelajaran wajib diisi.',
            'slug.required' => 'Slug pelajaran wajib diisi.',
            'slug.unique' => 'Slug pelajaran sudah digunakan.',
            'content.required' => 'Konten materi pelajaran wajib diisi.',
            'order.required' => 'Urutan pelajaran wajib diisi.',
            'order.integer' => 'Urutan pelajaran harus berupa angka bulat positif.',
        ]);

        Lesson::create($validated);

        return redirect()->route('admin.lessons.index', ['module_id' => $validated['module_id']])
            ->with('success', 'Pelajaran berhasil ditambahkan.');
    }

    /**
     * Memperbarui data pelajaran (FR-ADMIN-01).
     */
    public function update(Request $request, Lesson $lesson): RedirectResponse
    {
        if (! $request->filled('slug') && $request->filled('title')) {
            $request->merge(['slug' => Str::slug($request->input('title'))]);
        }

        $validated = $request->validate([
            'module_id' => ['required', 'exists:modules,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('lessons', 'slug')->ignore($lesson->id)],
            'content' => ['required', 'string'],
            'example_commands' => ['nullable', 'string'],
            'cheat_sheet' => ['nullable', 'string'],
            'order' => ['required', 'integer', 'min:1'],
        ], [
            'module_id.required' => 'Modul induk wajib dipilih.',
            'module_id.exists' => 'Modul yang dipilih tidak ditemukan.',
            'title.required' => 'Judul pelajaran wajib diisi.',
            'slug.required' => 'Slug pelajaran wajib diisi.',
            'slug.unique' => 'Slug pelajaran sudah digunakan.',
            'content.required' => 'Konten materi pelajaran wajib diisi.',
            'order.required' => 'Urutan pelajaran wajib diisi.',
            'order.integer' => 'Urutan pelajaran harus berupa angka bulat positif.',
        ]);

        $lesson->update($validated);

        return redirect()->route('admin.lessons.index', ['module_id' => $validated['module_id']])
            ->with('success', 'Pelajaran berhasil diperbarui.');
    }

    /**
     * Menghapus pelajaran beserta latihan dan hint di bawahnya (FR-ADMIN-01, SPEC Bagian 8.3).
     */
    public function destroy(Lesson $lesson): RedirectResponse
    {
        $moduleId = $lesson->module_id;
        $lesson->delete();

        return redirect()->route('admin.lessons.index', ['module_id' => $moduleId])
            ->with('success', 'Pelajaran berhasil dihapus beserta seluruh latihannya.');
    }
}
