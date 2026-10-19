<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use App\Models\TerminalSession;
use App\Services\ValidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExerciseController extends Controller
{
    public function __construct(
        protected ValidationService $validationService
    ) {}

    /**
     * Menilai jawaban latihan (SPEC Bagian 11 & Bagian 9.2).
     * Route: POST /exercises/{exercise}/check
     */
    public function check(Request $request, Exercise $exercise): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Cari sesi terminal aktif milik pengguna
        $session = TerminalSession::query()
            ->where('user_id', $user->id)
            ->active()
            ->first();

        // Ambil jumlah hint yang telah dibuka pengguna untuk latihan ini (FR-EX-03, D-06)
        $sessionKey = "exercise_{$exercise->id}_hints_user_{$user->id}";
        $unlockedCount = (int) $request->session()->get($sessionKey, 0);

        // Eksekusi penilaian melalui ValidationService
        $result = $this->validationService->validate($user, $exercise, $session, $unlockedCount);

        // VAL-06 & D-08: Jika tidak ada sesi terminal aktif, tolak dengan status 422
        if (! $result['success']) {
            return response()->json($result, 422);
        }

        return response()->json($result, 200);
    }

    /**
     * Meminta hint bertahap berikutnya untuk latihan ini (FR-EX-03, D-06).
     * Route: POST /exercises/{exercise}/hints
     */
    public function hint(Request $request, Exercise $exercise): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $allHints = $exercise->hints()->orderBy('order')->get();
        $totalHints = $allHints->count();

        $sessionKey = "exercise_{$exercise->id}_hints_user_{$user->id}";
        $currentCount = (int) $request->session()->get($sessionKey, 0);

        $newCount = min($totalHints, $currentCount + 1);
        $request->session()->put($sessionKey, $newCount);

        $unlockedHints = $allHints->take($newCount)->values();

        return response()->json([
            'success' => true,
            'unlocked_hints' => $unlockedHints,
            'remaining_hints' => max(0, $totalHints - $newCount),
            'total_hints' => $totalHints,
        ]);
    }
}
