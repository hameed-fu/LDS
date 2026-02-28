<?php

namespace App\Http\Controllers;

use App\Models\LiveSession;
use App\Models\VirtualClass;
use Illuminate\Http\Request;

class LiveSessionController extends Controller
{
    public function index()
    {
        return view('admin.live-sessions.index');
    }

    public function show(LiveSession $session)
    {
        return view('admin.live-sessions.show', compact('session'));
    }

    public function create()
    {
        $classes = VirtualClass::all();
        return view('admin.live-sessions.create', compact('classes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'session_number' => 'required|integer',
            'scheduled_at' => 'required|date_format:Y-m-d H:i',
            'meeting_url' => 'nullable|url',
            'recording_url' => 'nullable|url',
            'status' => 'required|in:scheduled,ongoing,completed,cancelled',
        ]);

        $session = LiveSession::create($validated);

        return redirect()->route('admin.live-sessions.show', $session)->with('success', 'Session created successfully');
    }

    public function edit(LiveSession $session)
    {
        $classes = VirtualClass::all();
        return view('admin.live-sessions.edit', compact('session', 'classes'));
    }

    public function update(Request $request, LiveSession $session)
    {
        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'session_number' => 'required|integer',
            'scheduled_at' => 'required|date_format:Y-m-d H:i',
            'started_at' => 'nullable|date_format:Y-m-d H:i',
            'ended_at' => 'nullable|date_format:Y-m-d H:i',
            'meeting_url' => 'nullable|url',
            'recording_url' => 'nullable|url',
            'status' => 'required|in:scheduled,ongoing,completed,cancelled',
        ]);

        $session->update($validated);

        return redirect()->route('admin.live-sessions.show', $session)->with('success', 'Session updated successfully');
    }

    public function destroy(LiveSession $session)
    {
        $session->delete();
        return redirect()->route('admin.live-sessions.index')->with('success', 'Session deleted successfully');
    }

    public function start(LiveSession $session)
    {
        $session->update([
            'status' => 'ongoing',
            'started_at' => now(),
        ]);

        return back()->with('success', 'Session started');
    }

    public function end(LiveSession $session)
    {
        $session->update([
            'status' => 'completed',
            'ended_at' => now(),
        ]);

        return back()->with('success', 'Session ended');
    }
}
