<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exercise;
use App\Models\Hint;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminHintController extends Controller
{
    /**
     * Menyimpan hint baru untuk suatu latihan (FR-ADMIN-01).
     */
    public function store(Request $request, Exercise $exercise): RedirectResponse
    {
        $validated = $request->validate([
            'content' => ['required', 'string'],
            'order' => ['required', 'integer', 'min:1'],
        ], [
            'content.required' => 'Isi petunjuk (hint) wajib diisi.',
            'order.required' => 'Urutan hint wajib diisi.',
            'order.min' => 'Urutan hint minimal bernilai 1.',
        ]);

        $exercise->hints()->create($validated);

        return redirect()->back()->with('success', 'Petunjuk (hint) berhasil ditambahkan.');
    }

    /**
     * Memperbarui hint yang ada (FR-ADMIN-01).
     */
    public function update(Request $request, Hint $hint): RedirectResponse
    {
        $validated = $request->validate([
            'content' => ['required', 'string'],
            'order' => ['required', 'integer', 'min:1'],
        ], [
            'content.required' => 'Isi petunjuk (hint) wajib diisi.',
            'order.required' => 'Urutan hint wajib diisi.',
            'order.min' => 'Urutan hint minimal bernilai 1.',
        ]);

        $hint->update($validated);

        return redirect()->back()->with('success', 'Petunjuk (hint) berhasil diperbarui.');
    }

    /**
     * Menghapus hint (FR-ADMIN-01).
     */
    public function destroy(Hint $hint): RedirectResponse
    {
        $hint->delete();

        return redirect()->back()->with('success', 'Petunjuk (hint) berhasil dihapus.');
    }
}
