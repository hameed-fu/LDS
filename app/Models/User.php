<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $guarded = ['id'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'author_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'author_id');
    }

    public function isMyself(): bool
    {
        return $this->is(auth()->user());
    }

    protected function firstName(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value) => ucfirst(str($this->name)->explode(' ')->first())
        );
    }

    public function enrollments()
    {
        return $this->hasMany(\App\Models\Enrollment::class);
    }

    public function classesTeaching()
    {
        return $this->hasMany(VirtualClass::class, 'teacher_id');
    }

    public function classEnrollments()
    {
        return $this->hasMany(ClassEnrollment::class, 'student_id');
    }

    public function attendance()
    {
        return $this->hasMany(Attendance::class, 'student_id');
    }

    public function sessionParticipations()
    {
        return $this->hasMany(SessionParticipant::class, 'user_id');
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class, 'student_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }
    public function isTeacherOf($classId)
    {
        return $this->virtualClasses()->where('virtual_classes.id', $classId)->exists();
    }

    public function isEnrolledIn($classId)
    {
        return $this->enrollments()->where('class_id', $classId)->exists();
    }
}
