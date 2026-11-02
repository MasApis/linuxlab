<?php

namespace App\Http\Controllers;

use App\Models\TerminalSession;
use App\Services\TerminalService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class TerminalSessionController extends Controller
{
    public function __construct(
        protected TerminalService $terminalService
    ) {}

    /**
     * Membuat sesi terminal baru untuk pengguna terautentikasi (FR-TERM-01, SEC-TERM-01 s.d. 08).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'exercise_id' => ['nullable', 'integer'],
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        try {
            $session = $this->terminalService->createSession($user, $validated['exercise_id'] ?? null);

            $remainingSeconds = max(0, Carbon::now()->diffInSeconds($session->expires_at, false));

            return response()->json([
                'success' => true,
                'session' => [
                    'id' => $session->id,
                    'status' => $session->status,
                    'expires_at' => $session->expires_at->toIso8601String(),
                    'remaining_seconds' => $remainingSeconds,
                ],
                'ws_token' => $session->ws_token,
                'ws_port' => (int) config('terminal.ws_port', 3001),
                'message' => 'Sesi terminal berhasil dibuat.',
            ], 201);
        } catch (RuntimeException $e) {
            $status = str_contains($e->getMessage(), 'penuh') ? 429 : 500;

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $status);
        }
    }

    /**
     * Memeriksa status sesi terminal aktif pengguna (SEC-TERM-09).
     */
    public function show(TerminalSession $session): JsonResponse
    {
        // Pemeriksaan kepemilikan sesi (SEC-TERM-09)
        abort_unless($session->user_id === Auth::id(), 403, 'Akses sesi terminal ditolak.');

        $now = Carbon::now();
        $isExpired = $session->expires_at->isPast();

        if ($session->status === 'active' && $isExpired) {
            $this->terminalService->destroySession($session, 'expired');
            $session->refresh();
        }

        $remainingSeconds = max(0, $now->diffInSeconds($session->expires_at, false));

        return response()->json([
            'session' => [
                'id' => $session->id,
                'status' => $session->status,
                'is_active' => $session->isActive(),
                'expires_at' => $session->expires_at->toIso8601String(),
                'remaining_seconds' => $remainingSeconds,
            ],
            'ws_token' => $session->isActive() ? $session->ws_token : null,
            'ws_port' => (int) config('terminal.ws_port', 3001),
        ]);
    }

    /**
     * Mengakhiri sesi terminal pengguna secara manual (FR-TERM-04, SEC-TERM-06, SEC-TERM-09).
     */
    public function destroy(TerminalSession $session): JsonResponse
    {
        // Pemeriksaan kepemilikan sesi (SEC-TERM-09)
        abort_unless($session->user_id === Auth::id(), 403, 'Akses sesi terminal ditolak.');

        $this->terminalService->destroySession($session, 'terminated');

        return response()->json([
            'success' => true,
            'message' => 'Sesi terminal berhasil diakhiri.',
        ]);
    }

    /**
     * Endpoint verifikasi internal untuk WebSocket Gateway (SEC-TERM-01).
     * Hanya dapat diakses dari localhost (127.0.0.1 / ::1).
     */
    public function verifyInternal(Request $request): JsonResponse
    {
        // Pastikan hanya request dari loopback interface (localhost)
        $ip = $request->ip();
        if ($ip !== '127.0.0.1' && $ip !== '::1') {
            abort(403, 'Akses hanya diizinkan dari internal localhost.');
        }

        // Jika secret gateway dikonfigurasi, wajib cocok (lapis kedua selain cek IP)
        $secret = (string) config('terminal.gateway_secret', '');
        if ($secret !== '' && ! hash_equals($secret, (string) $request->header('X-Gateway-Secret'))) {
            abort(403, 'Kredensial gateway internal tidak valid.');
        }

        $token = (string) $request->query('token');
        if (empty($token)) {
            return response()->json(['valid' => false, 'message' => 'Token wajib disertakan.'], 400);
        }

        /** @var TerminalSession|null $session */
        $session = TerminalSession::query()
            ->where('ws_token', $token)
            ->active()
            ->first();

        if (! $session) {
            return response()->json(['valid' => false, 'message' => 'Token sesi tidak valid atau sudah berakhir.'], 404);
        }

        return response()->json([
            'valid' => true,
            'session_id' => $session->id,
            'user_id' => $session->user_id,
            'container_id' => $session->container_id,
            'expires_at' => $session->expires_at->toIso8601String(),
        ]);
    }
}
