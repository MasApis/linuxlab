<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Hint extends Model
{
    use HasFactory;

    /**
     * Sesuai SPEC Bagian 8.1 & 8.2 (Tabel 5: hints).
     */
    protected $fillable = [
        'exercise_id',
        'content',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }
}
