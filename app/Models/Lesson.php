<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lesson extends Model {
    protected $fillable = ['course_id','title','content','video_url'];

    public function course(): BelongsTo {
        return $this->belongsTo(Course::class);
    }

    public function exercises(): HasMany {
        return $this->hasMany(Exercise::class);
    }

    public function quizzes(): HasMany {
        return $this->hasMany(Quiz::class);
    }

    
}