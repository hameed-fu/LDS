<?php

namespace App\Http\Controllers;

use App\Events\WebRTCSignal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Events\SessionChatMessage;

class WebRTCController extends Controller
{
    public function signal(Request $request)
    {
        $request->validate([
            'room' => 'required|string',
            'data' => 'required|array',
            'type' => 'required|string'
        ]);

        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // If this is a screen share signal, broadcast to everyone
        if (isset($request->data['screenShare'])) {
            broadcast(new WebRTCSignal(
                $request->room,
                $request->data,
                auth()->id(),
                auth()->user()->name,
                'screen_share'
            ));
            
            return response()->json(['success' => true]);
        }

        // Handle regular WebRTC signals
        broadcast(new WebRTCSignal(
            $request->room,
            $request->data,
            auth()->id(),
            auth()->user()->name,
            $request->type
        ));

        return response()->json(['success' => true]);
    }

    public function join(Request $request)
    {
        $request->validate([
            'room' => 'required|string'
        ]);

        // Broadcast join notification to everyone
        broadcast(new WebRTCSignal(
            $request->room,
            ['action' => 'joined'],
            auth()->id(),
            auth()->user()->name,
            'presence'
        ));

        return response()->json(['success' => true]);
    }

    public function leave(Request $request)
    {
        $request->validate([
            'room' => 'required|string'
        ]);

        // Broadcast leave notification to everyone
        broadcast(new WebRTCSignal(
            $request->room,
            ['action' => 'left'],
            auth()->id(),
            auth()->user()->name,
            'presence'
        ));

        return response()->json(['success' => true]);
    }

    public function chat(Request $request)
    {
        $request->validate([
            'room' => 'required|string',
            'message' => 'required|string|max:500'
        ]);

        // Store chat in database
        $session = \App\Models\LiveSession::where('meeting_code', $request->room)->first();
        
        if ($session) {
            $chat = \App\Models\SessionChat::create([
                'session_id' => $session->id,
                'user_id' => auth()->id(),
                'message' => $request->message,
                'timestamp' => now()
            ]);
            
            // Broadcast to everyone except sender
            broadcast(new SessionChatMessage(
                $request->room,
                auth()->id(),
                auth()->user()->name,
                $request->message
            ))->toOthers();
            
            return response()->json(['success' => true, 'chat' => $chat]);
        }

        return response()->json(['error' => 'Session not found'], 404);
    }
}