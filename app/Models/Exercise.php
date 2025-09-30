<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Exercise extends Model {
    protected $fillable = ['lesson_id','title','description','sample_code','solution_code'];

    public function lesson(): BelongsTo {
        return $this->belongsTo(Lesson::class);
    }
}