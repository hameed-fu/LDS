<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LiveSession extends Model
{
    protected $fillable = [
        'class_id',
        'title',
        'description',
        'session_number',
        'scheduled_at',
        'started_at',
        'ended_at',
        'meeting_url',
        'recording_url',
        'meeting_code',
        'status'
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function virtualClass(): BelongsTo
    {
        return $this->belongsTo(VirtualClass::class, 'class_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(SessionParticipant::class, 'session_id');
    }

    public function chat(): HasMany
    {
        return $this->hasMany(SessionChat::class, 'session_id');
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(Attendance::class, 'session_id');
    }
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'session_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class, 'session_id');
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class, 'session_id');
    }

    public function getRouteKeyName()
    {
        return 'meeting_code';
    }

    public function chats()
    {
        return $this->hasMany(SessionChat::class, 'session_id');
    }
}
