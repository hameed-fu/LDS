<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Enrollment;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonView;
use App\Models\QuizAttempt;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    /**
     * Show student dashboard with analytics.
     */
    public function dashboard()
    {
        $user = Auth::user();

        $enrollments = Enrollment::with('course.lessons')
            ->where('user_id', $user->id)
            ->get();

        foreach ($enrollments as $enrollment) {
            $totalLessons = $enrollment->course->lessons->count();
            $completedLessons = LessonView::where('user_id', $user->id)
                ->whereIn('lesson_id', $enrollment->course->lessons->pluck('id'))
                ->count();

            $enrollment->completed_lessons = $completedLessons;
            $enrollment->progress = $totalLessons > 0
                ? round(($completedLessons / $totalLessons) * 100)
                : 0;
        }

        $totalCourses = $enrollments->count();
        $totalLessons = LessonView::where('user_id', $user->id)->count();

        return view('student.dashboard', compact(
            'user',
            'enrollments',
            'totalCourses',
            'totalLessons'
        ));
    }



    /**
     * Display user's enrolled courses with progress.
     */
    public function myCourses()
    {
        $user = Auth::user();

        $enrollments = Enrollment::with(['course.lessons'])
            ->where('user_id', $user->id)
            ->get();

        foreach ($enrollments as $enrollment) {
            $totalLessons = $enrollment->course->lessons->count();
            $completedLessons = LessonView::where('user_id', $user->id)
                ->whereIn('lesson_id', $enrollment->course->lessons->pluck('id'))
                ->count();

            $enrollment->completed_lessons = $completedLessons;
            $enrollment->progress = $totalLessons > 0
                ? round(($completedLessons / $totalLessons) * 100)
                : 0;
        }

        return view('student.my_courses', compact('enrollments'));
    }


    /**
     * Show course details page with lessons & progress.
     */
    public function showCourse(Course $course)
    {
        $user = Auth::user();

        // Load related data (lessons + language)
        $course->load(['lessons', 'language']);

        $totalLessons = $course->lessons->count();
        $completedLessons = LessonView::where('user_id', $user->id)
            ->whereIn('lesson_id', $course->lessons->pluck('id'))
            ->count();

        $progress = $totalLessons > 0
            ? round(($completedLessons / $totalLessons) * 100)
            : 0;

        return view('student.course_show', compact('course', 'progress'));
    }


    /**
     * Continue a course (auto-redirect to next unviewed lesson)
     */
    public function continueCourse(Course $course)
    {
        $user = Auth::user();
        $course->load('lessons');

        $viewedLessonIds = LessonView::where('user_id', $user->id)
            ->pluck('lesson_id')
            ->toArray();

        $nextLesson = $course->lessons()
            ->whereNotIn('id', $viewedLessonIds)
            ->first();

        if (!$nextLesson) {
            return redirect()->route('student.course.show', $course->id)
                ->with('message', '🎉 You have completed this course!');
        }

        // redirect internally to lessonShow method
        return redirect()->route('student.lesson.show', $nextLesson->id);
    }

    /**
     * Show a single lesson (and mark it as viewed)
     */
    public function lessonShow(Lesson $lesson)
    {
        $user = Auth::user();

        // Record lesson view (no duplicates)
        LessonView::firstOrCreate([
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
        ]);

        // Load related data
        $lesson->load([
            'course.lessons',
            'exercises',
            'quizzes.questions.options',
        ]);

        // Viewed lessons for progress tracking
        $viewedLessonIds = LessonView::where('user_id', $user->id)
            ->whereIn('lesson_id', $lesson->course->lessons->pluck('id'))
            ->pluck('lesson_id')
            ->toArray();

        // Calculate progress
        $totalLessons = $lesson->course->lessons->count();
        $completed = count($viewedLessonIds);
        $progress = $totalLessons > 0 ? round(($completed / $totalLessons) * 100) : 0;

        return view('student.lesson_show', compact(
            'lesson',
            'viewedLessonIds',
            'progress'
        ));
    }


    public function exerciseShow($exerciseId)
    {
        $exercise = \App\Models\Exercise::findOrFail($exerciseId);

        return view('student.exercise_show', compact('exercise'));
    }

    /**
     * Show quiz page for a lesson
     */
    public function quizShow($quizId)
    {
        $quiz = \App\Models\Quiz::with('questions.options')->findOrFail($quizId);
        $user = Auth::user();

        // Fetch all previous attempts by this user for this quiz
        $attempts = \App\Models\QuizAttempt::where('quiz_id', $quiz->id)
            ->where('user_id', $user->id)
            ->orderByDesc('attempted_at')
            ->get();

        return view('student.quiz_show', compact('quiz', 'attempts'));
    }


    /**
     * Show user's quiz attempts.
     */
    public function myQuizAttempts()
    {
        $user = Auth::user();

        $quizAttempts = QuizAttempt::with('quiz')
            ->where('user_id', $user->id)
            ->latest('attempted_at')
            ->get();

        return view('student.my_quiz_attempts', compact('quizAttempts'));
    }

    /**
     * Handle quiz submission and record attempt
     */
    public function submitQuiz(Request $request, $quizId)
    {
        $quiz = \App\Models\Quiz::with('questions.options')->findOrFail($quizId);
        $user = Auth::user();

        $score = 0;
        $totalQuestions = $quiz->questions->count();

        foreach ($quiz->questions as $question) {
            $selected = $request->input("question_{$question->id}");
            $correct = $question->options->where('is_correct', true)->pluck('id')->toArray();

            if (in_array($selected, $correct)) {
                $score++;
            }
        }

        $percentage = $totalQuestions > 0 ? round(($score / $totalQuestions) * 100) : 0;

        \App\Models\QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'user_id' => $user->id,
            'score' => $percentage,
            'attempted_at' => now(),
        ]);

        return redirect()->route('student.my_quiz_attempts')
            ->with('message', "You scored {$percentage}% on the quiz!");
    }

    public function quizStart($quizId)
    {
        $quiz = \App\Models\Quiz::with('lesson')->findOrFail($quizId);
        return view('student.quiz_start', compact('quiz'));
    }

    
}
