<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exercise extends Model
{
    use HasFactory;

    /**
     * Sesuai SPEC Bagian 8.1 & 8.2 (Tabel 4: exercises).
     */
    protected $fillable = [
        'lesson_id',
        'title',
        'instruction',
        'validation_type',
        'validation_config',
        'points',
        'order',
    ];

    protected $casts = [
        'validation_config' => 'array',
        'points' => 'integer',
        'order' => 'integer',
    ];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function hints(): HasMany
    {
        return $this->hasMany(Hint::class)->orderBy('order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ExerciseAttempt::class);
    }

    public function terminalSessions(): HasMany
    {
        return $this->hasMany(TerminalSession::class);
    }
}
