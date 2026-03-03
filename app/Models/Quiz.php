<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Quiz extends Model
{
    protected $fillable = ['session_id', 'title'];
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(LiveSession::class);
    }

    public function liveSession(): BelongsTo
    {
        return $this->belongsTo(LiveSession::class, 'session_id');
    }

    public function virtualClass(): BelongsTo
    {
        return $this->belongsTo(VirtualClass::class, 'class_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }



    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }
}
