<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Question extends Model {
    protected $fillable = ['quiz_id','question_text','question_type', 'points', 'correct_answer', 'order'];

    public function quiz(): BelongsTo {
        return $this->belongsTo(Quiz::class);
    }

    public function options(): HasMany {
        return $this->hasMany(Option::class);
    }

    public function questionOptions(): HasMany {
        return $this->hasMany(QuestionOption::class);
    }

    public function studentAnswers(): HasMany {
        return $this->hasMany(StudentAnswer::class);
    }
}