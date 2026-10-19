<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\AnswerVote;
use App\Models\Lesson;
use App\Models\Question;
use App\Services\BadgeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ForumController extends Controller
{
    public function __construct(
        protected BadgeService $badgeService
    ) {}

    /**
     * Menampilkan daftar pertanyaan forum untuk suatu pelajaran (FR-FORUM-01 & FR-FORUM-05).
     * Route: GET /lessons/{lesson}/forum
     */
    public function index(Lesson $lesson): Response
    {
        $module = $lesson->module;
        abort_unless($module && $module->is_published, 404);

        // Daftar pertanyaan diurutkan dari yang terbaru (FR-FORUM-05)
        $questions = $lesson->questions()
            ->with(['user:id,name,username'])
            ->withCount('answers')
            ->latest()
            ->get()
            ->map(fn (Question $q) => [
                'id' => $q->id,
                'title' => $q->title,
                'body' => $q->body,
                'is_resolved' => (bool) $q->is_resolved,
                'answers_count' => (int) $q->answers_count,
                'created_at' => $q->created_at->translatedFormat('d F Y, H:i'),
                'created_at_human' => $q->created_at->diffForHumans(),
                'user' => $q->user ? [
                    'id' => $q->user->id,
                    'name' => $q->user->name,
                    'username' => $q->user->username,
                ] : [
                    'name' => 'Pengguna nonaktif',
                    'username' => 'nonaktif',
                ],
            ]);

        return Inertia::render('Forum/Index', [
            'lesson' => [
                'id' => $lesson->id,
                'title' => $lesson->title,
                'slug' => $lesson->slug,
                'order' => $lesson->order,
                'module' => [
                    'id' => $module->id,
                    'title' => $module->title,
                    'slug' => $module->slug,
                    'order' => $module->order,
                ],
            ],
            'questions' => $questions,
        ]);
    }

    /**
     * Membuat pertanyaan baru pada pelajaran (FR-FORUM-01).
     * lesson_id diambil dari konteks pelajaran dan tidak dapat diubah oleh pengguna.
     * Route: POST /lessons/{lesson}/forum
     */
    public function storeQuestion(Request $request, Lesson $lesson): RedirectResponse
    {
        $module = $lesson->module;
        abort_unless($module && $module->is_published, 404);

        $validated = $request->validate([
            'title' => ['required', 'string', 'min:5', 'max:255'],
            'body' => ['required', 'string', 'min:10'],
        ], [
            'title.required' => 'Judul pertanyaan wajib diisi.',
            'title.min' => 'Judul pertanyaan minimal 5 karakter.',
            'title.max' => 'Judul pertanyaan maksimal 255 karakter.',
            'body.required' => 'Isi pertanyaan wajib diisi.',
            'body.min' => 'Isi pertanyaan minimal 10 karakter.',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // lesson_id otomatis terikat pada konteks pelajaran
        $question = $lesson->questions()->create([
            'user_id' => $user->id,
            'title' => $validated['title'],
            'body' => $validated['body'],
            'is_resolved' => false,
        ]);

        // Evaluasi lencana 'first_question' (SPEC Bagian 12)
        $this->badgeService->evaluateBadges($user);

        return redirect()
            ->route('forum.show', $question)
            ->with('success', 'Pertanyaan berhasil dipublikasikan!');
    }

    /**
     * Menampilkan detail pertanyaan beserta jawaban-jawabannya (FR-FORUM-02 & FR-FORUM-05).
     * Route: GET /forum/{question}
     */
    public function show(Question $question): Response
    {
        $lesson = $question->lesson;
        $module = $lesson->module;
        $currentUser = Auth::user();

        // Jawaban: jawaban terbaik di posisi teratas, lalu upvote terbanyak (FR-FORUM-05)
        $answers = $question->answers()
            ->with(['user:id,name,username'])
            ->withCount('votes')
            ->orderByDesc('is_best_answer')
            ->orderByDesc('votes_count')
            ->orderBy('created_at')
            ->get();

        $votedAnswerIds = $currentUser
            ? $currentUser->answerVotes()->whereIn('answer_id', $answers->pluck('id'))->pluck('answer_id')->all()
            : [];

        $answersData = $answers->map(fn (Answer $ans) => [
            'id' => $ans->id,
            'body' => $ans->body,
            'is_best_answer' => (bool) $ans->is_best_answer,
            'votes_count' => (int) $ans->votes_count,
            'has_voted' => in_array($ans->id, $votedAnswerIds),
            'is_own_answer' => $currentUser && $ans->user_id === $currentUser->id,
            'created_at' => $ans->created_at->translatedFormat('d F Y, H:i'),
            'created_at_human' => $ans->created_at->diffForHumans(),
            'user' => $ans->user ? [
                'id' => $ans->user->id,
                'name' => $ans->user->name,
                'username' => $ans->user->username,
            ] : [
                'name' => 'Pengguna nonaktif',
                'username' => 'nonaktif',
            ],
        ]);

        return Inertia::render('Forum/Show', [
            'question' => [
                'id' => $question->id,
                'title' => $question->title,
                'body' => $question->body,
                'is_resolved' => (bool) $question->is_resolved,
                'created_at' => $question->created_at->translatedFormat('d F Y, H:i'),
                'created_at_human' => $question->created_at->diffForHumans(),
                'is_owner' => $currentUser && $question->user_id === $currentUser->id,
                'user' => $question->user ? [
                    'id' => $question->user->id,
                    'name' => $question->user->name,
                    'username' => $question->user->username,
                ] : [
                    'name' => 'Pengguna nonaktif',
                    'username' => 'nonaktif',
                ],
            ],
            'lesson' => [
                'id' => $lesson->id,
                'title' => $lesson->title,
                'slug' => $lesson->slug,
                'module' => [
                    'id' => $module->id,
                    'title' => $module->title,
                    'slug' => $module->slug,
                ],
            ],
            'answers' => $answersData,
        ]);
    }

    /**
     * Menulis jawaban pada suatu pertanyaan (FR-FORUM-02).
     * Route: POST /forum/{question}/answers
     */
    public function storeAnswer(Request $request, Question $question): RedirectResponse
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'min:5'],
        ], [
            'body.required' => 'Isi jawaban wajib diisi.',
            'body.min' => 'Isi jawaban minimal 5 karakter.',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $question->answers()->create([
            'user_id' => $user->id,
            'body' => $validated['body'],
            'is_best_answer' => false,
        ]);

        return back()->with('success', 'Jawaban berhasil dikirim!');
    }

    /**
     * Memberikan upvote pada jawaban (FR-FORUM-03).
     * Route: POST /answers/{answer}/vote
     */
    public function voteAnswer(Request $request, Answer $answer): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Pengguna tidak dapat meng-upvote jawabannya sendiri (FR-FORUM-03)
        if ($answer->user_id === $user->id) {
            abort(403, 'Anda tidak dapat memberikan upvote pada jawaban Anda sendiri.');
        }

        // Maksimal satu upvote per pengguna per jawaban
        if ($answer->votes()->where('user_id', $user->id)->exists()) {
            abort(422, 'Anda sudah memberikan upvote pada jawaban ini.');
        }

        $answer->votes()->create([
            'user_id' => $user->id,
        ]);

        return back()->with('success', 'Upvote berhasil diberikan!');
    }

    /**
     * Menandai jawaban terbaik oleh penanya (FR-FORUM-04).
     * Route: POST /answers/{answer}/best
     */
    public function markBestAnswer(Request $request, Answer $answer): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $question = $answer->question;

        // Hanya penanya yang dapat menandai jawaban terbaik (FR-FORUM-04)
        if ($question->user_id !== $user->id) {
            abort(403, 'Hanya pembuat pertanyaan yang dapat menandai jawaban terbaik.');
        }

        DB::transaction(function () use ($question, $answer) {
            // Menandai yang lain menggantikan yang lama
            $question->answers()->update(['is_best_answer' => false]);

            // Set jawaban terpilih sebagai terbaik
            $answer->update(['is_best_answer' => true]);

            // Pertanyaan ditandai terselesaikan
            $question->update(['is_resolved' => true]);
        });

        // Evaluasi lencana 'best_answer' untuk pembuat jawaban (SPEC Bagian 12)
        if ($answer->user) {
            $this->badgeService->evaluateBadges($answer->user);
        }

        return back()->with('success', 'Jawaban berhasil ditandai sebagai jawaban terbaik!');
    }
}
