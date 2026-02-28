<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        return view('admin.notifications.index');
    }

    public function show(Notification $notification)
    {
        $notification->update(['is_read' => true]);
        return view('admin.notifications.show', compact('notification'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:assignment,quiz,announcement,session,grade,message,other',
        ]);

        Notification::create($validated);

        return back()->with('success', 'Notification created successfully');
    }

    public function markAsRead(Notification $notification)
    {
        $notification->update(['is_read' => true]);
        return back()->with('success', 'Notification marked as read');
    }

    public function markAllAsRead()
    {
        Notification::where('is_read', false)->update(['is_read' => true]);
        return back()->with('success', 'All notifications marked as read');
    }

    public function delete(Notification $notification)
    {
        $notification->delete();
        return back()->with('success', 'Notification deleted successfully');
    }

    public function deleteAll()
    {
        Notification::truncate();
        return back()->with('success', 'All notifications deleted');
    }
}
