<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});


Broadcast::channel('test-channel', function () {
    return true; 
});

Broadcast::channel('webrtc.room.{roomId}', function ($user, $roomId) {
    return ['id' => $user->id, 'name' => $user->name];
});
Broadcast::channel('webrtc.{room}', function ($user, $room) {
    return ['id' => $user->id, 'name' => $user->name];
});

Broadcast::channel('group.{groupId}', function ($user, $groupId) {
    return true;
});
