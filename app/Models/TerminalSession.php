<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TerminalSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'exercise_id',
        'container_id',
        'status',
        'ws_token',
        'expires_at',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }


    /**
     * Scope untuk sesi yang masih aktif dan belum lewat waktu kedaluwarsa.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active')
            ->where('expires_at', '>', Carbon::now());
    }

    /**
     * Cek apakah sesi saat ini masih aktif dan belum kedaluwarsa.
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && $this->expires_at->isFuture();
    }
}
