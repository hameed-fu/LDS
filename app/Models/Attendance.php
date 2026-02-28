<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $table = 'attendance';
    protected $fillable = ['session_id', 'class_id', 'student_id', 'date', 'status', 'timestamp'];

    protected $casts = [
        'date' => 'date',
        'timestamp' => 'datetime',
    ];

    public function liveSession(): BelongsTo
    {
        return $this->belongsTo(LiveSession::class, 'session_id');
    }

    public function virtualClass(): BelongsTo
    {
        return $this->belongsTo(VirtualClass::class, 'class_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
