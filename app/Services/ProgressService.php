<?php

namespace App\Services;

use App\Models\Exercise;
use App\Models\ExerciseAttempt;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Module;
use App\Models\ModuleProgress;
use App\Models\User;

class ProgressService
{
    /**
     * Memperbarui progres pelajaran dan modul setelah latihan diselesaikan.
     * Sesuai FR-PROG-01 dan FR-PROG-02.
     *
     * @return array{lesson_completed: bool, module_completed: bool}
     */
    public function updateProgressForExercise(User $user, Exercise $exercise): array
    {
        $lesson = $exercise->lesson;
        if (! $lesson) {
            return ['lesson_completed' => false, 'module_completed' => false];
        }

        $lessonCompleted = $this->updateLessonProgress($user, $lesson);
        $module = $lesson->module;
        $moduleCompleted = false;

        if ($module) {
            $moduleCompleted = $this->updateModuleProgress($user, $module);
        }

        return [
            'lesson_completed' => $lessonCompleted,
            'module_completed' => $moduleCompleted,
        ];
    }

    /**
     * Memperbarui status lesson_progress pengguna.
     * Sesuai FR-PROG-01: Pelajaran dinyatakan completed apabila seluruh latihan wajib
     * pada pelajaran tersebut telah dijawab benar sekurang-kurangnya satu kali.
     */
    public function updateLessonProgress(User $user, Lesson $lesson): bool
    {
        $totalExercises = $lesson->exercises()->count();

        // Ambil jumlah latihan berbeda yang sudah dijawab benar oleh pengguna
        $correctExercisesCount = ExerciseAttempt::query()
            ->where('user_id', $user->id)
            ->whereIn('exercise_id', $lesson->exercises()->pluck('id'))
            ->where('is_correct', true)
            ->distinct('exercise_id')
            ->count('exercise_id');

        $isCompleted = ($totalExercises > 0 && $correctExercisesCount >= $totalExercises);

        $progress = LessonProgress::firstOrNew([
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
        ]);

        if ($isCompleted) {
            $progress->status = 'completed';
            if (! $progress->completed_at) {
                $progress->completed_at = now();
            }
        } else {
            $progress->status = 'in_progress';
        }

        $progress->save();

        return $isCompleted;
    }

    /**
     * Memperbarui module_progress pengguna berdasarkan persentase pelajaran completed.
     * Sesuai FR-PROG-02: completion_percentage dihitung dari persentase pelajaran completed
     * terhadap seluruh pelajaran dalam modul. Status modul menjadi completed pada 100%.
     */
    public function updateModuleProgress(User $user, Module $module): bool
    {
        $totalLessons = $module->lessons()->count();

        if ($totalLessons === 0) {
            $percentage = 0;
            $status = 'not_started';
        } else {
            $completedLessons = LessonProgress::query()
                ->where('user_id', $user->id)
                ->whereIn('lesson_id', $module->lessons()->pluck('id'))
                ->where('status', 'completed')
                ->count();

            $percentage = (int) round(($completedLessons / $totalLessons) * 100);

            if ($percentage >= 100) {
                $percentage = 100;
                $status = 'completed';
            } elseif ($percentage > 0) {
                $status = 'in_progress';
            } else {
                // Periksa apakah ada pelajaran yang in_progress
                $hasInProgress = LessonProgress::query()
                    ->where('user_id', $user->id)
                    ->whereIn('lesson_id', $module->lessons()->pluck('id'))
                    ->where('status', 'in_progress')
                    ->exists();

                $status = $hasInProgress ? 'in_progress' : 'not_started';
            }
        }

        $moduleProgress = ModuleProgress::firstOrNew([
            'user_id' => $user->id,
            'module_id' => $module->id,
        ]);

        $moduleProgress->completion_percentage = $percentage;
        $moduleProgress->status = $status;
        $moduleProgress->save();

        return $status === 'completed';
    }

    /**
     * Menghitung progres keseluruhan belajar pengguna terhadap semua modul yang diterbitkan.
     * Sesuai FR-PROG-03.
     *
     * @return array{
     *     percentage: int,
     *     completed_lessons_count: int,
     *     total_lessons_count: int,
     *     completed_modules_count: int,
     *     total_modules_count: int
     * }
     */
    public function getOverallProgress(User $user): array
    {
        $publishedModules = Module::query()
            ->where('is_published', true)
            ->with('lessons')
            ->get();

        $totalModulesCount = $publishedModules->count();
        $totalLessonsCount = $publishedModules->sum(fn ($m) => $m->lessons->count());

        $publishedLessonIds = $publishedModules->flatMap(fn ($m) => $m->lessons->pluck('id'))->all();

        $completedLessonsCount = LessonProgress::query()
            ->where('user_id', $user->id)
            ->whereIn('lesson_id', $publishedLessonIds)
            ->where('status', 'completed')
            ->count();

        $publishedModuleIds = $publishedModules->pluck('id')->all();

        $completedModulesCount = ModuleProgress::query()
            ->where('user_id', $user->id)
            ->whereIn('module_id', $publishedModuleIds)
            ->where('status', 'completed')
            ->count();

        $percentage = $totalLessonsCount > 0
            ? (int) round(($completedLessonsCount / $totalLessonsCount) * 100)
            : 0;

        return [
            'percentage' => min(100, $percentage),
            'completed_lessons_count' => $completedLessonsCount,
            'total_lessons_count' => $totalLessonsCount,
            'completed_modules_count' => $completedModulesCount,
            'total_modules_count' => $totalModulesCount,
        ];
    }

    /**
     * Mendapatkan pelajaran berikutnya untuk tombol "Lanjutkan Belajar" (FR-PROG-03).
     * Mengembalikan pelajaran pertama dari modul terbitan yang belum berstatus completed.
     */
    public function getNextLessonToLearn(User $user): ?Lesson
    {
        $publishedModules = Module::query()
            ->where('is_published', true)
            ->orderBy('order')
            ->with(['lessons' => fn ($q) => $q->orderBy('order')])
            ->get();

        $completedLessonIds = LessonProgress::query()
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->pluck('lesson_id')
            ->all();

        foreach ($publishedModules as $module) {
            foreach ($module->lessons as $lesson) {
                if (! in_array($lesson->id, $completedLessonIds, true)) {
                    return $lesson;
                }
            }
        }

        // Jika semua selesai, kembalikan pelajaran pertama sebagai peninjauan materi
        return $publishedModules->first()?->lessons->first();
    }
}
