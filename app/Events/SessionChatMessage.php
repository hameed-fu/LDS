<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SessionChatMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $room;
    public $userId;
    public $userName;
    public $message;

    public function __construct($room, $userId, $userName, $message)
    {
        $this->room = $room;
        $this->userId = $userId;
        $this->userName = $userName;
        $this->message = $message;
    }

    public function broadcastOn()
    {
        return new Channel('webrtc.' . $this->room);
    }

    public function broadcastAs()
    {
        return 'session.chat';
    }

    public function broadcastWith()
    {
        return [
            'userId' => $this->userId,
            'userName' => $this->userName,
            'message' => $this->message,
            'timestamp' => now()->toISOString()
        ];
    }
}