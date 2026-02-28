<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LiveSession;
use App\Models\VirtualClass;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function index()
    {
        $classes = VirtualClass::all();
        return view('site.index', compact('classes'));
    }

    public function about()
    {
        return view('site.about');
    }

    public function classes()
    {
        $classes = VirtualClass::all();
        return view('site.classes', compact('classes'));
    }

    public function class_detail($id)
    {
        $enrolledStudents = Enrollment::where('class_id', $id)->count();
        $class = VirtualClass::find($id);
        return view('site.course_detail', compact('class', 'enrolledStudents'));
    }

    public function contact()
    {
        return view('site.contact');
    }
    public function enroll()
    {
        if (!auth()->check()) {
            return redirect('login');
        }
        $classes = VirtualClass::all();
        return view('site.enroll', compact('classes'));
    }

    public function enroll_save(Request $request)
    {
        $userId = auth()->id();

        // Validate request input
        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
        ]);

        // Check if the user is already enrolled in this course
        $existing = Enrollment::where('student_id', $userId)
            ->where('class_id', $validated['class_id'])
            ->first();

        if ($existing) {
            return redirect()->back()->with('error', 'You are already enrolled in this course.');
        }

        // Check if the user has already enrolled in 2 courses
        $enrolledCount = Enrollment::where('student_id', $userId)->count();
        if ($enrolledCount >= 3) {
            return redirect()->back()->with('error', 'You cannot enroll in more than 3 courses.');
        }

        // Save new enrollment
        Enrollment::create([
            'student_id' => $userId,
            'class_id' => $validated['class_id'],
            'enrolled_at' => now(),
        ]);

        return redirect()->back()->with('success', 'You have successfully enrolled in the course!');
    }



    public function register()
    {
        return view('site.register');
    }

    
}
