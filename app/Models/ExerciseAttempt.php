<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExerciseAttempt extends Model
{
    use HasFactory;

    /**
     * Tabel peristiwa hanya memiliki created_at (SPEC Bagian 8.1).
     */
    public const UPDATED_AT = null;

    /**
     * Sesuai SPEC Bagian 8.1 & 8.2 (Tabel 7: exercise_attempts).
     */
    protected $fillable = [
        'user_id',
        'exercise_id',
        'terminal_session_id',
        'is_correct',
        'score',
        'feedback',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'score' => 'integer',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    public function terminalSession(): BelongsTo
    {
        return $this->belongsTo(TerminalSession::class);
    }
}
