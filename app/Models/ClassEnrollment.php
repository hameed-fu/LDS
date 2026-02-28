<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassEnrollment extends Model
{
    protected $table = 'enrollments';
    protected $fillable = ['class_id', 'student_id', 'enrolled_at', 'status'];

    protected $casts = [
        'enrolled_at' => 'datetime',
    ];

    public function virtualClass(): BelongsTo
    {
        return $this->belongsTo(VirtualClass::class, 'class_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
