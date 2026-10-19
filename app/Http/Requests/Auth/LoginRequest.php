<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Menentukan apakah pengguna diizinkan membuat permintaan ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi login.
     * Mendukung 'login' (email/username) atau fallback 'email'.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'login' => ['required_without:email', 'string'],
            'email' => ['required_without:login', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Pesan kesalahan validasi dalam Bahasa Indonesia.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'login.required_without' => 'Email atau username wajib diisi.',
            'email.required_without' => 'Email atau username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ];
    }

    /**
     * Autentikasi kredensial pengguna.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $identifier = $this->filled('login') ? trim((string) $this->input('login')) : trim((string) $this->input('email'));
        $loginField = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // Periksa keberadaan akun dan status keaktifan (FR-ADMIN-04)
        $user = User::where($loginField, $identifier)->first();

        if ($user && ! $user->is_active) {
            RateLimiter::hit($this->throttleKey());

            $errorKey = $this->filled('login') ? 'login' : 'email';
            throw ValidationException::withMessages([
                $errorKey => 'Akun Anda dinonaktifkan oleh administrator. Silakan hubungi admin.',
            ]);
        }

        $credentials = [
            $loginField => $identifier,
            'password' => $this->input('password'),
            'is_active' => true,
        ];

        if (! Auth::attempt($credentials, $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            // FR-AUTH-02 AC: pesan kesalahan yang tidak membocorkan apakah email atau password yang salah
            $errorKey = $this->filled('login') ? 'login' : 'email';
            throw ValidationException::withMessages([
                $errorKey => 'Email/username atau password yang Anda masukkan salah.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Memastikan percobaan login tidak melebihi batas (rate limited).
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        $errorKey = $this->filled('login') ? 'login' : 'email';
        throw ValidationException::withMessages([
            $errorKey => "Terlalu banyak percobaan masuk. Silakan coba lagi dalam {$seconds} detik.",
        ]);
    }

    /**
     * Kunci throttle rate limiter untuk permintaan ini.
     */
    public function throttleKey(): string
    {
        $identifier = $this->filled('login') ? (string) $this->input('login') : (string) $this->input('email');

        return Str::transliterate(Str::lower($identifier).'|'.$this->ip());
    }
}
