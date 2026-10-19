<?php

namespace App\Http\Controllers;

use App\Services\CertificateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class LeaderboardController extends Controller
{
    public function __construct(
        protected CertificateService $certificateService
    ) {}

    /**
     * Menampilkan peringkat leaderboard publik (SPEC Bagian 6.H FR-LEAD-01 s.d. 04).
     */
    public function index(Request $request): Response
    {
        $currentUser = Auth::user();
        $leaderboard = $this->certificateService->getLeaderboard();

        $currentUserId = $currentUser?->id;
        $currentUserRank = $currentUser ? $this->certificateService->getUserRank($currentUser) : null;

        $rankings = $leaderboard->map(function ($entry) use ($currentUserId) {
            return [
                'rank' => (int) $entry->rank,
                'user_id' => (int) $entry->user_id,
                'name' => (string) $entry->name,
                'username' => (string) $entry->username,
                'total_score' => (int) $entry->total_score,
                'correct_exercises_count' => (int) $entry->correct_exercises_count,
                'completed_modules_count' => (int) $entry->completed_modules_count,
                'badges_count' => (int) $entry->badges_count,
                'is_current_user' => $currentUserId && $entry->user_id === $currentUserId,
            ];
        });

        return Inertia::render('Leaderboard/Index', [
            'rankings' => $rankings,
            'currentUserRank' => $currentUserRank,
        ]);
    }
}
