<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudyGroup extends Model
{
    protected $fillable = ['name', 'description', 'class_id', 'created_by', 'max_members'];

    public function virtualClass(): BelongsTo
    {
        return $this->belongsTo(VirtualClass::class, 'class_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members(): HasMany
    {
        return $this->hasMany(GroupMember::class, 'group_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(GroupMessage::class, 'group_id');
    }
}
