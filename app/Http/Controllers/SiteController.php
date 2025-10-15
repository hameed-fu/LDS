<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function index()
    {
        $courses = Course::all();
        return view('site.index', compact('courses'));
    }

    public function about()
    {
        return view('site.about');
    }

    public function courses()
    {
        $courses = Course::all();
        return view('site.courses', compact('courses'));
    }

     public function course_detail($id)
    {
        $course = Course::find($id);
        return view('site.course_detail', compact('course'));
    }

    public function contact()
    {
        return view('site.contact');
    }
    public function enroll()
    {
        if(!auth()->check()){
            return redirect('login');
        }
        $courses = Course::all();
        return view('site.enroll', compact('courses'));
    }

    public function enroll_save(Request $request)
    {
        $userId = auth()->id();  

        // Validate request input
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
        ]);

        // Check if the user is already enrolled in this course
        $existing =  Enrollment::where('user_id', $userId)
            ->where('course_id', $validated['course_id'])
            ->first();

        if ($existing) {
            return redirect()->back()->with('error', 'You are already enrolled in this course.');
        }

        // Save new enrollment
         Enrollment::create([
            'user_id' => $userId,
            'course_id' => $validated['course_id'],
            'enrolled_at' => now(),
        ]);

        return redirect()->back()->with('success', 'You have successfully enrolled in the course!');
    }


    public function register()
    {
        return view('site.register');
    }
}
