<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnswerVote extends Model
{
    use HasFactory;

    /**
     * Tabel tidak memiliki updated_at (tabel peristiwa, SPEC Bagian 8.1).
     */
    public $timestamps = false;

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var list<string>
     */
    protected $fillable = [
        'answer_id',
        'user_id',
        'created_at',
    ];

    /**
     * Konversi tipe data atribut.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    /**
     * Relasi ke jawaban yang di-upvote.
     */
    public function answer(): BelongsTo
    {
        return $this->belongsTo(Answer::class);
    }

    /**
     * Relasi ke pengguna pemberi upvote.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
