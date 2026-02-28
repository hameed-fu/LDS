<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VirtualClass extends Model
{
    protected $table = 'classes';
    protected $fillable = ['name', 'teacher_id', 'description', 'schedule'];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(ClassEnrollment::class, 'class_id');
    }

    public function liveSessions(): HasMany
    {
        return $this->hasMany(LiveSession::class, 'class_id');
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(Attendance::class, 'class_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class, 'class_id');
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class, 'class_id');
    }

    public function studyGroups(): HasMany
    {
        return $this->hasMany(StudyGroup::class, 'class_id');
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class, 'class_id');
    }
}
