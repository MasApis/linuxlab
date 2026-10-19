<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exercise;
use App\Models\Lesson;
use App\Models\Module;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AdminExerciseController extends Controller
{
    /**
     * Menampilkan daftar latihan beserta hint-nya untuk dikelola administrator (FR-ADMIN-01).
     */
    public function index(Request $request): Response
    {
        $lessonId = $request->query('lesson_id');
        $moduleId = $request->query('module_id');

        $query = Exercise::query()
            ->with(['lesson:id,module_id,title,slug', 'lesson.module:id,title,slug', 'hints'])
            ->withCount(['hints', 'attempts']);

        if ($lessonId) {
            $query->where('lesson_id', $lessonId);
        } elseif ($moduleId) {
            $query->whereHas('lesson', function ($q) use ($moduleId) {
                $q->where('module_id', $moduleId);
            });
        }

        $exercises = $query->orderBy('lesson_id')
            ->orderBy('order')
            ->get();

        $modules = Module::query()
            ->with(['lessons' => function ($q) {
                $q->orderBy('order')->select(['id', 'module_id', 'title', 'slug', 'order']);
            }])
            ->orderBy('order')
            ->get(['id', 'title', 'slug', 'order']);

        return Inertia::render('Admin/Exercises/Index', [
            'exercises' => $exercises,
            'modules' => $modules,
            'selectedLessonId' => $lessonId ? (int) $lessonId : null,
            'selectedModuleId' => $moduleId ? (int) $moduleId : null,
        ]);
    }

    /**
     * Menyimpan latihan baru dengan editor validation_type & validation_config (FR-ADMIN-01).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateExercise($request);

        Exercise::create($validated);

        return redirect()->route('admin.exercises.index', ['lesson_id' => $validated['lesson_id']])
            ->with('success', 'Latihan berhasil dibuat.');
    }

    /**
     * Memperbarui latihan yang ada (FR-ADMIN-01).
     */
    public function update(Request $request, Exercise $exercise): RedirectResponse
    {
        $validated = $this->validateExercise($request, $exercise);

        $exercise->update($validated);

        return redirect()->route('admin.exercises.index', ['lesson_id' => $validated['lesson_id']])
            ->with('success', 'Latihan berhasil diperbarui.');
    }

    /**
     * Menghapus latihan beserta hint dan riwayat percobaannya (FR-ADMIN-01, SPEC Bagian 8.3).
     */
    public function destroy(Exercise $exercise): RedirectResponse
    {
        $lessonId = $exercise->lesson_id;
        $exercise->delete();

        return redirect()->route('admin.exercises.index', ['lesson_id' => $lessonId])
            ->with('success', 'Latihan berhasil dihapus beserta seluruh hint-nya.');
    }

    /**
     * Validasi input latihan termasuk format JSON validation_config.
     * JSON rusak ditolak dengan pesan jelas.
     *
     * @return array<string, mixed>
     * @throws ValidationException
     */
    protected function validateExercise(Request $request, ?Exercise $exercise = null): array
    {
        $validated = $request->validate([
            'lesson_id' => ['required', 'exists:lessons,id'],
            'title' => ['required', 'string', 'max:255'],
            'instruction' => ['required', 'string'],
            'validation_type' => ['required', 'in:system_state,output,combined'],
            'validation_config' => ['required'],
            'points' => ['required', 'integer', 'min:1'],
            'order' => ['required', 'integer', 'min:1'],
        ], [
            'lesson_id.required' => 'Pelajaran wajib dipilih.',
            'lesson_id.exists' => 'Pelajaran yang dipilih tidak valid.',
            'title.required' => 'Judul latihan wajib diisi.',
            'instruction.required' => 'Instruksi latihan wajib diisi.',
            'validation_type.required' => 'Tipe validasi autograder wajib dipilih.',
            'validation_type.in' => 'Tipe validasi harus salah satu dari: system_state, output, atau combined.',
            'validation_config.required' => 'Konfigurasi validator JSON wajib diisi.',
            'points.required' => 'Poin latihan wajib diisi.',
            'points.min' => 'Poin latihan minimal bernilai 1.',
            'order.required' => 'Urutan latihan wajib diisi.',
            'order.min' => 'Urutan latihan minimal bernilai 1.',
        ]);

        $rawConfig = $validated['validation_config'];

        // Jika validation_config dikirim sebagai string JSON, validasi format sintaksnya
        if (is_string($rawConfig)) {
            $decoded = json_decode($rawConfig, true);
            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
                throw ValidationException::withMessages([
                    'validation_config' => 'Format JSON pada validation_config tidak valid atau rusak: ' . json_last_error_msg() . '. Pastikan sintaks JSON benar.',
                ]);
            }
            $validated['validation_config'] = $decoded;
        } elseif (! is_array($rawConfig)) {
            throw ValidationException::withMessages([
                'validation_config' => 'Format JSON pada validation_config harus berupa objek atau array JSON yang valid.',
            ]);
        }

        return $validated;
    }
}
