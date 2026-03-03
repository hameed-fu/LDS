<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'class_id',
        'title',
        'description',
        'due_date',
        'max_score',
        'created_by',
    ];

    protected $casts = [
        'due_date' => 'datetime',
    ];

    // Relationships
    public function session()
    {
        return $this->belongsTo(LiveSession::class, 'session_id');
    }

    public function class()
    {
        return $this->belongsTo(VirtualClass::class, 'class_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }
}