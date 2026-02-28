<?php

namespace App\Http\Controllers;

use App\Models\LiveSession;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MeetingController extends Controller
{
    public function join(LiveSession $session)
    {
        $virtualClass = $session->virtualClass;

        return view('site.meeting', compact('session', 'virtualClass')); 
    }


    public function end(LiveSession $session)
    {
        if (!auth()->user()->isTeacherOf($session->class_id)) {
            abort(403, 'Only teachers can end sessions');
        }

        $session->update([
            'status' => 'ended',
            'ended_at' => now()
        ]);

        // Broadcast session ended to all participants
        broadcast(new \App\Events\SessionEnded($session->meeting_code));

        return redirect()->route('teacher.sessions.index')
            ->with('success', 'Session ended successfully');
    }
}
