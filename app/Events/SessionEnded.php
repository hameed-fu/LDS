<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SessionEnded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $room;

    public function __construct($room)
    {
        $this->room = $room;
    }

    public function broadcastOn()
    {
        return new Channel('session.' . $this->room);
    }

    public function broadcastAs()
    {
        return 'session.ended';
    }

    public function broadcastWith()
    {
        return [
            'message' => 'Session has been ended by the host',
            'redirect' => true
        ];
    }
}