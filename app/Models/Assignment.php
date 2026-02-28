<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignment extends Model
{
    protected $fillable = ['session_id', 'class_id', 'title', 'description', 'due_date', 'max_score', 'created_by'];

    protected $casts = [
        'due_date' => 'datetime',
    ];

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

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class, 'assignment_id');
    }
}
