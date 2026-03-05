<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudyGroup extends Model
{
    protected $fillable = [
        'name',
        'description',
        'class_id',
        'created_by',
        'max_members'
    ];

    public function members()
    {
        return $this->belongsToMany(
            User::class,
            'group_members',
            'group_id',
            'user_id'
        );
    }



    public function messages()
    {
        return $this->hasMany(GroupMessage::class, 'group_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /* FIXED RELATIONSHIP */
    public function virtualClass()
    {
        return $this->belongsTo(VirtualClass::class, 'class_id');
    }

    public function isFull()
    {
        return $this->members()->count() >= $this->max_members;
    }
}
