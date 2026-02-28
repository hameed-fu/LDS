<?php

namespace App\Http\Controllers;

use App\Models\VirtualClass;
use App\Models\ClassEnrollment;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    public function index()
    {
        return view('admin.classes.index');
    }

    public function show(VirtualClass $class)
    {
        return view('admin.classes.show', compact('class'));
    }

    public function create()
    {
        return view('admin.classes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'teacher_id' => 'required|exists:users,id',
            'description' => 'nullable|string',
            'schedule' => 'nullable|string',
        ]);

        $class = VirtualClass::create($validated);

        return redirect()->route('admin.classes.show', $class)->with('success', 'Class created successfully');
    }

    public function edit(VirtualClass $class)
    {
        return view('admin.classes.edit', compact('class'));
    }

    public function update(Request $request, VirtualClass $class)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'teacher_id' => 'required|exists:users,id',
            'description' => 'nullable|string',
            'schedule' => 'nullable|string',
        ]);

        $class->update($validated);

        return redirect()->route('admin.classes.show', $class)->with('success', 'Class updated successfully');
    }

    public function destroy(VirtualClass $class)
    {
        $class->delete();
        return redirect()->route('admin.classes.index')->with('success', 'Class deleted successfully');
    }

    public function enrollStudent(Request $request, VirtualClass $class)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
        ]);

        ClassEnrollment::create([
            'class_id' => $class->id,
            'student_id' => $validated['student_id'],
            'enrolled_at' => now(),
            'status' => 'enrolled',
        ]);

        return back()->with('success', 'Student enrolled successfully');
    }
}
