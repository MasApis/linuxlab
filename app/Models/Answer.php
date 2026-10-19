<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Answer extends Model
{
    use HasFactory;

    /**
     * Atribut yang dapat diisi secara massal (SPEC Bagian 8.2).
     *
     * @var list<string>
     */
    protected $fillable = [
        'question_id',
        'user_id',
        'body',
        'is_best_answer',
    ];

    /**
     * Konversi tipe data atribut.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_best_answer' => 'boolean',
        ];
    }

    /**
     * Relasi ke pertanyaan induk.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Relasi ke pembuat jawaban (nullable jika akun dinonaktifkan/dihapus).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke upvote jawaban ini.
     */
    public function votes(): HasMany
    {
        return $this->hasMany(AnswerVote::class);
    }

    /**
     * Memeriksa apakah pengguna telah meng-upvote jawaban ini.
     */
    public function isVotedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->votes()->where('user_id', $user->id)->exists();
    }
}
