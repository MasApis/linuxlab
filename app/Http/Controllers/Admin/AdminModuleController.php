<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AdminModuleController extends Controller
{
    /**
     * Menampilkan daftar seluruh modul untuk dikelola administrator (FR-ADMIN-01).
     */
    public function index(Request $request): Response
    {
        $modules = Module::query()
            ->withCount(['lessons', 'lessons as exercises_count' => function ($query) {
                $query->join('exercises', 'lessons.id', '=', 'exercises.lesson_id');
            }])
            ->orderBy('order')
            ->get();

        return Inertia::render('Admin/Modules/Index', [
            'modules' => $modules,
        ]);
    }

    /**
     * Menyimpan modul baru ke database (FR-ADMIN-01).
     */
    public function store(Request $request): RedirectResponse
    {
        if (! $request->filled('slug') && $request->filled('title')) {
            $request->merge(['slug' => Str::slug($request->input('title'))]);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:modules,slug'],
            'description' => ['required', 'string'],
            'order' => ['required', 'integer', 'min:1'],
            'is_published' => ['boolean'],
        ], [
            'title.required' => 'Judul modul wajib diisi.',
            'slug.required' => 'Slug modul wajib diisi.',
            'slug.unique' => 'Slug modul sudah digunakan.',
            'description.required' => 'Deskripsi modul wajib diisi.',
            'order.required' => 'Urutan modul wajib diisi.',
            'order.integer' => 'Urutan modul harus berupa angka bulat positif.',
        ]);

        $validated['is_published'] = $request->boolean('is_published');

        Module::create($validated);

        return redirect()->route('admin.modules.index')
            ->with('success', 'Modul berhasil ditambahkan.');
    }

    /**
     * Memperbarui modul yang sudah ada (FR-ADMIN-01).
     */
    public function update(Request $request, Module $module): RedirectResponse
    {
        if (! $request->filled('slug') && $request->filled('title')) {
            $request->merge(['slug' => Str::slug($request->input('title'))]);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('modules', 'slug')->ignore($module->id)],
            'description' => ['required', 'string'],
            'order' => ['required', 'integer', 'min:1'],
            'is_published' => ['boolean'],
        ], [
            'title.required' => 'Judul modul wajib diisi.',
            'slug.required' => 'Slug modul wajib diisi.',
            'slug.unique' => 'Slug modul sudah digunakan.',
            'description.required' => 'Deskripsi modul wajib diisi.',
            'order.required' => 'Urutan modul wajib diisi.',
            'order.integer' => 'Urutan modul harus berupa angka bulat positif.',
        ]);

        $validated['is_published'] = $request->boolean('is_published');

        $module->update($validated);

        return redirect()->route('admin.modules.index')
            ->with('success', 'Modul berhasil diperbarui.');
    }

    /**
     * Menghapus modul beserta seluruh data terkait sesuai aturan cascade (FR-ADMIN-01, SPEC Bagian 8.3).
     */
    public function destroy(Module $module): RedirectResponse
    {
        $module->delete();

        return redirect()->route('admin.modules.index')
            ->with('success', 'Modul berhasil dihapus beserta seluruh pelajaran dan latihannya.');
    }
}
