<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Submission;
use App\Models\VirtualClass;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    public function index()
    {
        return view('admin.assignments.index');
    }

    public function show(Assignment $assignment)
    {
        $submissions = $assignment->submissions()->with('student')->paginate(15);
        return view('admin.assignments.show', compact('assignment', 'submissions'));
    }

    public function create()
    {
        $classes = VirtualClass::all();
        return view('admin.assignments.create', compact('classes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'session_id' => 'nullable|exists:live_sessions,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date_format:Y-m-d H:i',
            'max_score' => 'required|integer|min:0',
        ]);

        $validated['created_by'] = auth()->id();
        $assignment = Assignment::create($validated);

        return redirect()->route('admin.assignments.show', $assignment)->with('success', 'Assignment created successfully');
    }

    public function edit(Assignment $assignment)
    {
        $classes = VirtualClass::all();
        return view('admin.assignments.edit', compact('assignment', 'classes'));
    }

    public function update(Request $request, Assignment $assignment)
    {
        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'session_id' => 'nullable|exists:live_sessions,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date_format:Y-m-d H:i',
            'max_score' => 'required|integer|min:0',
        ]);

        $assignment->update($validated);

        return redirect()->route('admin.assignments.show', $assignment)->with('success', 'Assignment updated successfully');
    }

    public function destroy(Assignment $assignment)
    {
        $assignment->delete();
        return redirect()->route('admin.assignments.index')->with('success', 'Assignment deleted successfully');
    }

    public function gradeSubmission(Request $request, Submission $submission)
    {
        $validated = $request->validate([
            'score' => 'required|integer|min:0',
            'feedback' => 'nullable|string',
            'status' => 'required|in:pending,submitted,graded,late',
        ]);

        $submission->update($validated);

        return back()->with('success', 'Submission graded successfully');
    }
}
