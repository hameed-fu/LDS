<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizAttempt extends Model {
    protected $fillable = ['quiz_id','user_id','score','attempted_at','answers', 'started_at', 'completed_at', 'time_taken'];
    public $timestamps = false;

    public function quiz(): BelongsTo {
        return $this->belongsTo(Quiz::class);
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function studentAnswers(): HasMany {
        return $this->hasMany(StudentAnswer::class, 'attempt_id');
    }

    protected $casts = [
        'attempted_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'answers' => 'array',
    ];

    
}