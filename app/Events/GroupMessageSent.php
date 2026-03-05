<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GroupMessageSent implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public $groupId;
    public $userId;
    public $userName;
    public $message;

    public function __construct($message)
    {
        $this->groupId = $message->group_id;
        $this->userId = $message->user_id;
        $this->userName = $message->user->name;
        $this->message = $message->message;
    }

    public function broadcastOn()
    {
        return new Channel('group.' . $this->groupId);
    }

    public function broadcastAs()
    {
        return 'group.chat';
    }

    public function broadcastWith()
    {
        return [
            'groupId' => $this->groupId,
            'userId' => $this->userId,
            'userName' => $this->userName,
            'message' => $this->message,
            'timestamp' => now()->toISOString(),
        ];
    }
}