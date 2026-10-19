<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AdminBadgeController extends Controller
{
    /**
     * Menampilkan daftar master lencana untuk dikelola administrator (FR-ADMIN-02).
     */
    public function index(Request $request): Response
    {
        $badges = Badge::query()
            ->withCount('userBadges')
            ->orderBy('id')
            ->get();

        return Inertia::render('Admin/Badges/Index', [
            'badges' => $badges,
        ]);
    }

    /**
     * Menyimpan lencana baru (FR-ADMIN-02).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateBadge($request);

        Badge::create($validated);

        return redirect()->route('admin.badges.index')
            ->with('success', 'Lencana baru berhasil dibuat.');
    }

    /**
     * Memperbarui lencana yang ada (FR-ADMIN-02).
     */
    public function update(Request $request, Badge $badge): RedirectResponse
    {
        $validated = $this->validateBadge($request);

        $badge->update($validated);

        return redirect()->route('admin.badges.index')
            ->with('success', 'Lencana berhasil diperbarui.');
    }

    /**
     * Menghapus lencana (FR-ADMIN-02).
     */
    public function destroy(Badge $badge): RedirectResponse
    {
        $badge->delete();

        return redirect()->route('admin.badges.index')
            ->with('success', 'Lencana berhasil dihapus.');
    }

    /**
     * Memvalidasi atribut lencana.
     *
     * @return array<string, mixed>
     * @throws ValidationException
     */
    protected function validateBadge(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'icon' => ['required', 'string', 'max:255'],
            'criteria_type' => [
                'required',
                'in:first_exercise,lesson_completed,module_completed,exercises_count,first_question,best_answer',
            ],
            'criteria_value' => ['nullable'],
        ], [
            'name.required' => 'Nama lencana wajib diisi.',
            'description.required' => 'Deskripsi lencana wajib diisi.',
            'icon.required' => 'Ikon lencana wajib diisi.',
            'criteria_type.required' => 'Tipe kriteria lencana wajib dipilih.',
            'criteria_type.in' => 'Tipe kriteria yang dipilih tidak valid.',
        ]);

        $val = $validated['criteria_value'] ?? null;
        if (is_string($val) && trim($val) !== '') {
            $decoded = json_decode($val, true);
            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
                throw ValidationException::withMessages([
                    'criteria_value' => 'Format JSON kriteria lencana tidak valid: ' . json_last_error_msg(),
                ]);
            }
            $validated['criteria_value'] = $decoded;
        } elseif (empty($val)) {
            $validated['criteria_value'] = null;
        }

        return $validated;
    }
}
