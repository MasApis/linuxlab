<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\ExerciseAttempt;
use App\Models\LessonProgress;
use App\Models\ModuleProgress;
use App\Models\User;
use App\Models\UserBadge;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BadgeService
{
    /**
     * Mengevaluasi seluruh lencana dan memberikan lencana baru yang memenuhi syarat.
     * Sesuai SPEC Bagian 12 dan FR-PROG-04.
     *
     * @return Collection<int, Badge> Daftar lencana baru yang berhasil didapatkan
     */
    public function evaluateBadges(User $user): Collection
    {
        // Dapatkan ID lencana yang sudah dimiliki pengguna
        $existingBadgeIds = $user->userBadges()->pluck('badge_id')->all();

        // Ambil lencana yang belum dimiliki
        $availableBadges = Badge::query()
            ->whereNotIn('id', $existingBadgeIds)
            ->get();

        $newlyAwarded = collect();

        foreach ($availableBadges as $badge) {
            if ($this->checkCriteria($user, $badge)) {
                $awarded = $this->awardBadge($user, $badge);
                if ($awarded) {
                    $newlyAwarded->push($badge);
                }
            }
        }

        return $newlyAwarded;
    }

    /**
     * Memeriksa apakah pengguna memenuhi kriteria lencana tertentu.
     */
    public function checkCriteria(User $user, Badge $badge): bool
    {
        $criteriaType = $badge->criteria_type;
        $criteriaValue = $badge->criteria_value;

        return match ($criteriaType) {
            'first_exercise' => $this->checkFirstExercise($user),
            'lesson_completed' => $this->checkLessonCompleted($user, $criteriaValue),
            'module_completed' => $this->checkModuleCompleted($user, $criteriaValue),
            'exercises_count' => $this->checkExercisesCount($user, $criteriaValue),
            'first_question' => $this->checkFirstQuestion($user),
            'best_answer' => $this->checkBestAnswer($user, $criteriaValue),
            default => false,
        };
    }

    /**
     * Memberikan lencana kepada pengguna dengan jaminan tepat satu kali (FR-PROG-04).
     */
    public function awardBadge(User $user, Badge $badge): bool
    {
        $userBadge = UserBadge::firstOrCreate(
            [
                'user_id' => $user->id,
                'badge_id' => $badge->id,
            ],
            [
                'awarded_at' => now(),
            ]
        );

        return $userBadge->wasRecentlyCreated;
    }

    /**
     * Kriteria first_exercise: Menyelesaikan latihan pertama dengan benar.
     */
    protected function checkFirstExercise(User $user): bool
    {
        return ExerciseAttempt::query()
            ->where('user_id', $user->id)
            ->where('is_correct', true)
            ->exists();
    }

    /**
     * Kriteria lesson_completed: Menyelesaikan satu pelajaran tertentu.
     */
    protected function checkLessonCompleted(User $user, mixed $criteriaValue): bool
    {
        $lessonId = is_array($criteriaValue) ? ($criteriaValue['lesson_id'] ?? null) : null;
        if (! $lessonId) {
            return false;
        }

        return LessonProgress::query()
            ->where('user_id', $user->id)
            ->where('lesson_id', $lessonId)
            ->where('status', 'completed')
            ->exists();
    }

    /**
     * Kriteria module_completed: Menyelesaikan satu modul tertentu (progres 100%).
     */
    protected function checkModuleCompleted(User $user, mixed $criteriaValue): bool
    {
        $moduleId = is_array($criteriaValue) ? ($criteriaValue['module_id'] ?? null) : null;
        if (! $moduleId) {
            return false;
        }

        return ModuleProgress::query()
            ->where('user_id', $user->id)
            ->where('module_id', $moduleId)
            ->where('status', 'completed')
            ->exists();
    }

    /**
     * Kriteria exercises_count: Telah menyelesaikan benar sekurang-kurangnya n latihan berbeda.
     */
    protected function checkExercisesCount(User $user, mixed $criteriaValue): bool
    {
        $requiredCount = is_array($criteriaValue) ? (int) ($criteriaValue['count'] ?? 1) : 1;

        $distinctCorrectCount = ExerciseAttempt::query()
            ->where('user_id', $user->id)
            ->where('is_correct', true)
            ->distinct('exercise_id')
            ->count('exercise_id');

        return $distinctCorrectCount >= $requiredCount;
    }

    /**
     * Kriteria first_question: Membuat pertanyaan pertama di forum.
     */
    protected function checkFirstQuestion(User $user): bool
    {
        if (! Schema::hasTable('questions')) {
            return false;
        }

        return DB::table('questions')
            ->where('user_id', $user->id)
            ->exists();
    }

    /**
     * Kriteria best_answer: Jawabannya ditandai sebagai jawaban terbaik sekurang-kurangnya n kali.
     */
    protected function checkBestAnswer(User $user, mixed $criteriaValue): bool
    {
        if (! Schema::hasTable('answers')) {
            return false;
        }

        $requiredCount = is_array($criteriaValue) ? (int) ($criteriaValue['count'] ?? 1) : 1;

        $bestCount = DB::table('answers')
            ->where('user_id', $user->id)
            ->where('is_best_answer', true)
            ->count();

        return $bestCount >= $requiredCount;
    }
}
