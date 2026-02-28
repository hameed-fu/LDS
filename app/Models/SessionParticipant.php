<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionParticipant extends Model
{
    protected $fillable = ['session_id', 'user_id', 'joined_at', 'left_at', 'duration'];

    protected $casts = [
        'joined_at' => 'datetime',
        'left_at' => 'datetime',
    ];

    public function liveSession(): BelongsTo
    {
        return $this->belongsTo(LiveSession::class, 'session_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
