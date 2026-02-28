<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SessionChat extends Model
{
    use HasFactory;

    protected $table = 'session_chat';

    protected $fillable = [
        'session_id', 'user_id', 'message', 'timestamp'
    ];

    protected $dates = ['timestamp'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
