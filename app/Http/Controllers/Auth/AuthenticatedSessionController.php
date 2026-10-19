<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Menampilkan halaman login.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
            'success' => session('success'),
        ]);
    }

    /**
     * Memproses permintaan login (sesi Laravel).
     * Sesuai FR-AUTH-02: login berhasil membuat sesi Laravel dan
     * mengarahkan user ke dashboard pengguna serta admin ke dashboard admin.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        if ($request->user()->isAdmin()) {
            return redirect()->intended(route('admin.dashboard', absolute: false));
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Mengakhiri sesi login (logout).
     * Sesuai FR-AUTH-02: logout menghapus sesi dan mengarahkan ke halaman utama.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
