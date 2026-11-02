<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminUserController extends Controller
{
    /**
     * Menampilkan daftar pengguna terdaftar untuk dikelola administrator (FR-ADMIN-04).
     */
    public function index(Request $request): Response
    {
        $search = $request->query('search');
        $role = $request->query('role');
        $status = $request->query('status'); // 'active', 'inactive'

        $query = User::query()
            ->withCount(['exerciseAttempts', 'lessonProgress', 'badges']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role && in_array($role, ['user', 'admin'], true)) {
            $query->where('role', $role);
        }

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        $users = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => [
                'search' => $search,
                'role' => $role,
                'status' => $status,
            ],
            'currentAdminId' => $request->user()?->id,
        ]);
    }

    /**
     * Memperbarui status keaktifan atau peran pengguna (FR-ADMIN-04).
     * Administrator tidak dapat menonaktifkan atau mengubah peran akunnya sendiri.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $currentAdmin = $request->user();

        // FR-ADMIN-04 AC: Administrator tidak dapat menonaktifkan atau mengubah peran akunnya sendiri
        if ($user->id === $currentAdmin->id) {
            if ($request->has('is_active') && ! $request->boolean('is_active')) {
                abort(403, 'Administrator tidak dapat menonaktifkan akunnya sendiri.');
            }

            if ($request->has('role') && $request->input('role') !== 'admin') {
                abort(403, 'Administrator tidak dapat mengubah peran akunnya sendiri.');
            }
        }

        $validated = $request->validate([
            'is_active' => ['sometimes', 'boolean'],
            'role' => ['sometimes', 'in:user,admin'],
        ], [
            'role.in' => 'Peran pengguna harus user atau admin.',
        ]);

        // role/is_active di luar $fillable (anti mass-assignment) → tulis eksplisit
        $user->forceFill($validated)->save();

        $actionText = isset($validated['is_active'])
            ? ($user->is_active ? 'diaktifkan' : 'dinonaktifkan')
            : 'diperbarui';

        return redirect()->back()
            ->with('success', "Akun pengguna {$user->name} berhasil {$actionText}.");
    }
}
