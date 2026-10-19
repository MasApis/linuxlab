<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Question extends Model
{
    use HasFactory;

    /**
     * Atribut yang dapat diisi secara massal (SPEC Bagian 8.2).
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'lesson_id',
        'title',
        'body',
        'is_resolved',
    ];

    /**
     * Konversi tipe data atribut.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_resolved' => 'boolean',
        ];
    }

    /**
     * Relasi ke pembuat pertanyaan (nullable jika akun dinonaktifkan/dihapus).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke pelajaran yang terkait.
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    /**
     * Relasi ke seluruh jawaban atas pertanyaan ini.
     */
    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    /**
     * Relasi ke jawaban terbaik (bila ada).
     */
    public function bestAnswer(): HasOne
    {
        return $this->hasOne(Answer::class)->where('is_best_answer', true);
    }
}
