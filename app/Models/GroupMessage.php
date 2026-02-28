<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupMessage extends Model
{
    protected $fillable = ['group_id', 'user_id', 'message', 'timestamp'];

    protected $casts = [
        'timestamp' => 'datetime',
    ];

    public function studyGroup(): BelongsTo
    {
        return $this->belongsTo(StudyGroup::class, 'group_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
