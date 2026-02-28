<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WebRTCSignal implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $room;
    public $data;
    public $userId;
    public $userName;
    public $type;

    public function __construct($room, $data, $userId, $userName = null, $type = 'signal')
    {
        $this->room = $room;
        $this->data = $data;
        $this->userId = $userId;
        $this->userName = $userName;
        $this->type = $type;
    }

    public function broadcastOn()
    {
        return new Channel('webrtc.' . $this->room);
    }

    public function broadcastWith()
    {
        return [
            'type' => $this->type,
            'data' => $this->data,
            'userId' => $this->userId,
            'userName' => $this->userName,
            'timestamp' => now()->toISOString()
        ];
    }

    public function broadcastAs()
    {
        return 'webrtc.signal';
    }
}