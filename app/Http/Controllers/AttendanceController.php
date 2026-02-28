<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\LiveSession;
use App\Models\VirtualClass;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index()
    {
        return view('admin.attendance.index');
    }

    public function show(LiveSession $session)
    {
        $attendance = $session->attendance()->with(['student', 'virtualClass'])->paginate(50);
        return view('admin.attendance.show', compact('session', 'attendance'));
    }

    public function record(Request $request, LiveSession $session)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
            'status' => 'required|in:present,absent,late,excused',
            'date' => 'required|date',
        ]);

        Attendance::updateOrCreate(
            [
                'session_id' => $session->id,
                'student_id' => $validated['student_id'],
                'date' => $validated['date'],
            ],
            [
                'class_id' => $session->class_id,
                'status' => $validated['status'],
                'timestamp' => now(),
            ]
        );

        return back()->with('success', 'Attendance recorded successfully');
    }

    public function bulkUpdate(Request $request, LiveSession $session)
    {
        $validated = $request->validate([
            'students' => 'required|array',
            'students.*.id' => 'required|exists:users,id',
            'students.*.status' => 'required|in:present,absent,late,excused',
        ]);

        foreach ($validated['students'] as $student) {
            Attendance::updateOrCreate(
                [
                    'session_id' => $session->id,
                    'student_id' => $student['id'],
                    'date' => now()->date(),
                ],
                [
                    'class_id' => $session->class_id,
                    'status' => $student['status'],
                    'timestamp' => now(),
                ]
            );
        }

        return back()->with('success', 'Attendance updated successfully');
    }

    public function report(VirtualClass $class)
    {
        $startDate = request('start_date');
        $endDate = request('end_date');

        $attendance = Attendance::where('class_id', $class->id)
            ->when($startDate, function ($q) use ($startDate) {
                $q->whereDate('date', '>=', $startDate);
            })
            ->when($endDate, function ($q) use ($endDate) {
                $q->whereDate('date', '<=', $endDate);
            })
            ->with(['student', 'liveSession'])
            ->get()
            ->groupBy('student_id');

        return view('admin.attendance.report', compact('class', 'attendance', 'startDate', 'endDate'));
    }
}
