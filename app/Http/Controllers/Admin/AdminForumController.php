<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\Question;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminForumController extends Controller
{
    /**
     * Menampilkan daftar pertanyaan dan jawaban forum untuk dimoderasi administrator (FR-ADMIN-03).
     */
    public function index(Request $request): Response
    {
        $status = $request->query('status'); // 'resolved', 'unresolved'
        $search = $request->query('search');

        $query = Question::query()
            ->with([
                'user:id,name,username',
                'lesson:id,title,slug',
                'answers' => function ($q) {
                    $q->with(['user:id,name,username'])->withCount('votes')->orderByDesc('is_best_answer')->latest();
                },
            ])
            ->withCount('answers');

        if ($status === 'resolved') {
            $query->where('is_resolved', true);
        } elseif ($status === 'unresolved') {
            $query->where('is_resolved', false);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('username', 'like', "%{$search}%");
                    });
            });
        }

        $questions = $query->latest()->paginate(15)->withQueryString();

        return Inertia::render('Admin/Forum/Index', [
            'questions' => $questions,
            'filters' => [
                'status' => $status,
                'search' => $search,
            ],
        ]);
    }

    /**
     * Menghapus pertanyaan forum yang tidak pantas beserta jawaban dan vote-nya (FR-ADMIN-03, SPEC Bagian 8.3).
     */
    public function destroyQuestion(Question $question): RedirectResponse
    {
        $question->delete();

        return redirect()->back()
            ->with('success', 'Pertanyaan forum berhasil dihapus beserta jawaban ikutannya.');
    }

    /**
     * Menghapus jawaban forum yang tidak pantas beserta vote-nya (FR-ADMIN-03, SPEC Bagian 8.3).
     */
    public function destroyAnswer(Answer $answer): RedirectResponse
    {
        $question = $answer->question;
        $wasBest = $answer->is_best_answer;

        $answer->delete();

        // Jika jawaban terbaik dihapus, periksa apakah masih ada jawaban terbaik lainnya
        if ($wasBest && $question) {
            $hasOtherBest = $question->answers()->where('is_best_answer', true)->exists();
            if (! $hasOtherBest) {
                $question->update(['is_resolved' => false]);
            }
        }

        return redirect()->back()
            ->with('success', 'Jawaban forum berhasil dihapus beserta vote ikutannya.');
    }
}
