<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizAttempt extends Model {
    protected $fillable = ['quiz_id','user_id','score','attempted_at'];
    public $timestamps = false;

    public function quiz(): BelongsTo {
        return $this->belongsTo(Quiz::class);
    }

     protected $casts = [
        'attempted_at' => 'datetime',
    ];
    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }
}