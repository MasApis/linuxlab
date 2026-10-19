<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'username', 'email', 'password', 'role', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Memeriksa apakah pengguna memiliki hak akses administrator.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Sesi terminal milik pengguna (SPEC Bagian 8.3).
     */
    public function terminalSessions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TerminalSession::class);
    }

    /**
     * Percobaan latihan milik pengguna (SPEC Bagian 8.3).
     */
    public function exerciseAttempts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ExerciseAttempt::class);
    }

    /**
     * Progres pelajaran milik pengguna (SPEC Bagian 8.3).
     */
    public function lessonProgress(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }

    /**
     * Progres modul milik pengguna (SPEC Bagian 8.3).
     */
    public function moduleProgress(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ModuleProgress::class);
    }

    /**
     * Baris relasi lencana pengguna (SPEC Bagian 8.3).
     */
    public function userBadges(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserBadge::class);
    }

    /**
     * Seluruh lencana yang telah diperoleh pengguna.
     */
    public function badges(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Badge::class, 'user_badges')
            ->withPivot('awarded_at');
    }

    /**
     * Sertifikat penyelesaian milik pengguna (relasi 1:1, SPEC Bagian 8.3).
     */
    public function certificate(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Certificate::class);
    }

    /**
     * Seluruh pertanyaan forum yang diajukan pengguna (SPEC Bagian 8.3).
     */
    public function questions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Question::class);
    }

    /**
     * Seluruh jawaban forum yang ditulis pengguna (SPEC Bagian 8.3).
     */
    public function answers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Answer::class);
    }

    /**
     * Seluruh upvote jawaban yang diberikan pengguna (SPEC Bagian 8.3).
     */
    public function answerVotes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AnswerVote::class);
    }
}

