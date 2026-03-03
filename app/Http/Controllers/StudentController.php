<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\VirtualClass;
use App\Models\ClassEnrollment;
use App\Models\Lesson;
use App\Models\LessonView;
use App\Models\QuizAttempt;
use App\Models\Certificate;
use App\Models\LiveSession;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class StudentController extends Controller
{
    /**
     * Show student dashboard with analytics.
     */
    public function dashboard()
    {
        $user = Auth::user();

        // Get enrollments for virtual classes with liveSessions
        $enrollments = ClassEnrollment::with('virtualClass.liveSessions')
            ->where('student_id', $user->id)
            ->get();

        foreach ($enrollments as $enrollment) {
            $totalSessions = $enrollment->virtualClass->liveSessions->count();

            // Count completed sessions (assuming you track attendance or completion)
            // You might need to adjust this based on how you track session completion
            $completedSessions = Attendance::where('student_id', $user->id)
                ->whereIn('session_id', $enrollment->virtualClass->liveSessions->pluck('id'))
                ->where('status', 'present') // or 'completed'
                ->count();

            $enrollment->completed_sessions = $completedSessions;
            $enrollment->progress = $totalSessions > 0
                ? round(($completedSessions / $totalSessions) * 100)
                : 0;
        }

        $totalClasses = $enrollments->count();

        // Count total sessions attended/completed
        $totalSessionsCompleted = Attendance::where('student_id', $user->id)
            ->where('status', 'present') // or 'completed'
            ->count();

        return view('student.dashboard', compact(
            'user',
            'enrollments',
            'totalClasses',
            'totalSessionsCompleted' // Updated variable name
        ));
    }

    /**
     * Display user's enrolled virtual classes with progress.
     */
    public function myCourses()
    {
        $user = Auth::user();

        $enrollments = ClassEnrollment::with(['virtualClass.liveSessions'])
            ->where('student_id', $user->id)
            ->get();

        foreach ($enrollments as $enrollment) {
            $totalSessions = $enrollment->virtualClass->liveSessions->count();

            // Count completed sessions - adjust based on your attendance tracking
            $completedSessions = Attendance::where('student_id', $user->id)
                ->whereIn('session_id', $enrollment->virtualClass->liveSessions->pluck('id'))
                ->where('status', 'present')
                ->count();

            $enrollment->completed_sessions = $completedSessions;
            $enrollment->progress = $totalSessions > 0
                ? round(($completedSessions / $totalSessions) * 100)
                : 0;
        }

        return view('student.my_courses', compact('enrollments'));
    }

    /**
     * Show virtual class details page with lessons & progress.
     */
    public function showCourse($class_id)
    {
        $user = Auth::user();

        $virtualClass = VirtualClass::with([
            'liveSessions.quizzes.questions.options',
            'teacher'
        ])->findOrFail($class_id);

        // Check enrollment
        $enrollment = ClassEnrollment::where('student_id', $user->id)
            ->where('class_id', $virtualClass->id)
            ->first();

        if (!$enrollment) {
            return redirect()->route('student.my_courses')
                ->with('error', 'You are not enrolled in this class.');
        }

        $totalSessions = $virtualClass->liveSessions->count();

        $sessionIds = $virtualClass->liveSessions->pluck('id');

        // Count UNIQUE sessions attended
        $completedSessions = Attendance::where('student_id', $user->id)
            ->whereIn('session_id', $sessionIds)
            ->where('status', 'present')
            ->distinct('session_id')
            ->count('session_id');

        $completedSessions = min($completedSessions, $totalSessions);

        $progress = $totalSessions > 0
            ? round(($completedSessions / $totalSessions) * 100)
            : 0;

        return view('student.course_show', compact('virtualClass', 'progress'));
    }

    /**
     * Continue a virtual class (auto-redirect to next unviewed lesson)
     */
    public function continueCourse(VirtualClass $virtualClass)
    {
        $user = Auth::user();

        // Check enrollment
        $enrollment = ClassEnrollment::where('student_id', $user->id)
            ->where('class_id', $virtualClass->id)
            ->first();

        if (!$enrollment) {
            return redirect()->route('student.my_courses')
                ->with('error', 'You are not enrolled in this class.');
        }

        $virtualClass->load('lessons');

        $viewedLessonIds = LessonView::where('user_id', $user->id)
            ->pluck('lesson_id')
            ->toArray();

        $nextLesson = $virtualClass->lessons()
            ->whereNotIn('id', $viewedLessonIds)
            ->orderBy('order', 'asc')
            ->first();

        if (!$nextLesson) {
            return redirect()->route('student.course.show', $virtualClass->id)
                ->with('message', '🎉 You have completed this virtual class!');
        }

        // redirect to lessonShow method
        return redirect()->route('student.session.show', $nextLesson->id);
    }

    /**
     * Show a single lesson (and mark it as viewed)
     */
    public function sessionShow($liveSession)
    {
        $user = Auth::user();

        $liveSession = LiveSession::with('virtualClass')->findOrFail($liveSession);

        // Safety check
        if (!$liveSession->class_id) {
            abort(404, 'Session not linked to a class.');
        }

        /*
        |--------------------------------------------------------------------------
        | Check Enrollment (USES enrollments TABLE)
        |--------------------------------------------------------------------------
        */
        $enrollment = ClassEnrollment::where('student_id', $user->id)
            ->where('class_id', $liveSession->class_id)
            ->first();

        if (!$enrollment) {
            return redirect()->route('student.my_courses')
                ->with('error', 'You are not enrolled in this class.');
        }

        /*
        |--------------------------------------------------------------------------
        | Mark Attendance (ONLY ONCE)
        |--------------------------------------------------------------------------
        */
        if ($liveSession->status === 'ongoing') {

            Attendance::firstOrCreate(
                [
                    'session_id' => $liveSession->id,
                    'student_id' => $user->id,
                ],
                [
                    'class_id'  => $liveSession->class_id,
                    'date'      => now()->toDateString(),
                    'status'    => 'present',
                    'timestamp' => now(),
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Load Relations
        |--------------------------------------------------------------------------
        */
        $liveSession->load([
            'virtualClass',
            'virtualClass.assignments',
            'virtualClass.liveSessions.quizzes.questions.options',
            'attendances' => fn($q) => $q->where('student_id', $user->id),
        ]);

        $hasAttended = $liveSession->attendances->isNotEmpty();

        /*
        |--------------------------------------------------------------------------
        | Upcoming Sessions
        |--------------------------------------------------------------------------
        */
        $upcomingSessions = LiveSession::where('class_id', $liveSession->class_id)
            ->where('status', 'scheduled')
            ->where('scheduled_at', '>', now())
            ->orderBy('scheduled_at')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Past Sessions (FIXED GROUPING)
        |--------------------------------------------------------------------------
        */
        $pastSessions = LiveSession::where('class_id', $liveSession->class_id)
            ->where(function ($query) {
                $query->whereIn('status', ['completed', 'cancelled'])
                    ->orWhere(function ($q) {
                        $q->where('status', 'scheduled')
                            ->where('scheduled_at', '<', now());
                    });
            })
            ->orderByDesc('scheduled_at')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Progress (COUNT UNIQUE ATTENDED SESSIONS)
        |--------------------------------------------------------------------------
        */
        $totalSessions = LiveSession::where('class_id', $liveSession->class_id)->count();

        $attendedSessions = Attendance::where('student_id', $user->id)
            ->where('class_id', $liveSession->class_id)
            ->where('status', 'present')
            ->distinct('session_id')
            ->count('session_id');

        $attendedSessions = min($attendedSessions, $totalSessions);

        $progress = $totalSessions > 0
            ? round(($attendedSessions / $totalSessions) * 100)
            : 0;

        $virtualClass = $liveSession->virtualClass;

        /*
|--------------------------------------------------------------------------
| Assignment Stats
|--------------------------------------------------------------------------
*/
        $assignments = $virtualClass->assignments;

        $totalAssignments = $assignments->count();

        $submittedAssignments = $assignments->filter(function ($assignment) use ($user) {
            return $assignment->submissions
                ->where('student_id', $user->id)
                ->count() > 0;
        })->count();

        /*
|--------------------------------------------------------------------------
| Quiz Stats
|--------------------------------------------------------------------------
*/
        $quizzes = $virtualClass->liveSessions
            ->flatMap->quizzes;

        $totalQuizzes = $quizzes->count();

        $attemptedQuizzes = $quizzes->filter(function ($quiz) use ($user) {
            return $quiz->attempts
                ->where('user_id', $user->id)
                ->count() > 0;
        })->count();

        return view('student.session_show', [
            'session' => $liveSession,
            'virtualClass' => $virtualClass,
            'assignments' => $assignments,
            'quizzes' => $quizzes,
            'totalAssignments' => $totalAssignments,
            'submittedAssignments' => $submittedAssignments,
            'totalQuizzes' => $totalQuizzes,
            'attemptedQuizzes' => $attemptedQuizzes,
            'hasAttended' => $hasAttended,
            'upcomingSessions' => $upcomingSessions,
            'pastSessions' => $pastSessions,
            'progress' => $progress,
        ]);
    }

    // Mark attendance
    public function markAttendance(LiveSession $liveSession, Request $request)
    {
        $user = Auth::user();

        $attendance = Attendance::updateOrCreate(
            [
                'session_id' => $liveSession->id,
                'student_id' => $user->id,
            ],
            [
                'status' => 'present',
                'joined_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Attendance marked successfully',
            'attendance' => $attendance
        ]);
    }

    /**
     * Download lesson PDF
     */
    public function downloadLessonPdf(Lesson $lesson)
    {
        $user = Auth::user();

        // Check enrollment
        $lesson->load('virtualClass');
        $enrollment = ClassEnrollment::where('student_id', $user->id)
            ->where('class_id', $lesson->virtualClass->id)
            ->first();

        if (!$enrollment) {
            abort(403, 'You are not enrolled in this class.');
        }

        // Record lesson view (if needed)
        LessonView::firstOrCreate([
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
        ]);

        // Generate PDF view
        $pdf = Pdf::loadView('student.lesson_pdf', [
            'lesson' => $lesson
        ])->setPaper('a4', 'portrait');

        // Download file
        return $pdf->download(\Str::slug($lesson->virtualClass->name . '-' . $lesson->title) . '.pdf');
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
        $quiz = \App\Models\Quiz::with([
            'questions' => function ($q) {
                $q->has('options');
            },
            'questions.options',
            'session.virtualClass'
        ])->findOrFail($quizId);

        $user = Auth::user();

        // Enrollment check
        $enrollment = ClassEnrollment::where('student_id', $user->id)
            ->where('class_id', $quiz->session->virtualClass->id)
            ->first();

        if (!$enrollment) {
            abort(403, 'You are not enrolled in this class.');
        }

        // Total valid questions (ONLY questions with options)
        $totalQuestions = $quiz->questions->count();

        $attempts = \App\Models\QuizAttempt::where('quiz_id', $quiz->id)
            ->where('user_id', $user->id)
            ->orderByDesc('attempted_at')
            ->get()
            ->map(function ($attempt) use ($quiz, $totalQuestions) {

                $correct = 0;
                $wrong   = 0;

                foreach ($quiz->questions as $question) {

                    $answerData = $attempt->answers[$question->id] ?? null;

                    if (!$answerData) {
                        continue;
                    }

                    $selected   = $answerData['selected'] ?? null;
                    $correctIds = $answerData['correct'] ?? [];

                    if (in_array($selected, $correctIds)) {
                        $correct++;
                    } else {
                        $wrong++;
                    }
                }

                // Calculate percentage correctly
                $percentage = $totalQuestions > 0
                    ? round(($correct / $totalQuestions) * 100)
                    : 0;

                $attempt->correct_count = $correct;
                $attempt->wrong_count   = $wrong;
                $attempt->score         = $percentage;

                return $attempt;
            });

        return view('student.quiz_show', compact('quiz', 'attempts'));
    }

    /**
     * Show user's quiz attempts.
     */
    public function myQuizAttempts()
    {
        $user = Auth::user();

        $quizAttempts = QuizAttempt::with([
            'quiz.questions' => function ($q) {
                $q->has('options');
            },
            'quiz.questions.options'
        ])
            ->where('user_id', $user->id)
            ->latest('attempted_at')
            ->get()
            ->map(function ($attempt) {

                $quiz = $attempt->quiz;
                $totalQuestions = $quiz->questions->count();

                $correct = 0;
                $wrong   = 0;

                foreach ($quiz->questions as $question) {

                    $answerData = $attempt->answers[$question->id] ?? null;

                    if (!$answerData) {
                        continue;
                    }

                    $selected   = $answerData['selected'] ?? null;
                    $correctIds = $answerData['correct'] ?? [];

                    if (in_array($selected, $correctIds)) {
                        $correct++;
                    } else {
                        $wrong++;
                    }
                }

                $percentage = $totalQuestions > 0
                    ? round(($correct / $totalQuestions) * 100)
                    : 0;

                $attempt->correct_count = $correct;
                $attempt->wrong_count   = $wrong;
                $attempt->score         = $percentage;

                return $attempt;
            });

        return view('student.my_quiz_attempts', compact('quizAttempts'));
    }
    /**
     * Handle quiz submission and record attempt
     */
    public function submitQuiz(Request $request, $quizId)
    {
        $quiz = \App\Models\Quiz::with('questions.options')->findOrFail($quizId);
        $user = Auth::user();

        // Check enrollment
        $quiz->load('session.virtualClass');
        $enrollment = ClassEnrollment::where('student_id', $user->id)
            ->where('class_id', $quiz->session->virtualClass->id)
            ->first();

        if (!$enrollment) {
            abort(403, 'You are not enrolled in this class.');
        }

        $score = 0;
        $totalQuestions = $quiz->questions->count();
        $answers = [];

        foreach ($quiz->questions as $question) {
            $selected = $request->input("question_{$question->id}");
            $correct = $question->options->where('is_correct', true)->pluck('id')->toArray();

            $answers[$question->id] = [
                'selected' => $selected,
                'correct' => $correct,
            ];

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
            'answers' => $answers, // store JSON data
        ]);

        return redirect()->route('student.quiz.show', $quiz->id)
            ->with('message', "You scored {$percentage}% on this quiz!");
    }

    public function quizStart($quizId)
    {
        $quiz = \App\Models\Quiz::with('session')->findOrFail($quizId);

        // Check enrollment
        $quiz->load('session.virtualClass');
        $user = Auth::user();

        $enrollment = ClassEnrollment::where('student_id', $user->id)
            ->where('class_id', $quiz->session->virtualClass->id)
            ->first();

        if (!$enrollment) {
            abort(403, 'You are not enrolled in this class.');
        }

        return view('student.quiz_start', compact('quiz'));
    }

    /**
     * Show student certificates
     */
    public function studentCertificates()
    {
        $userId = Auth::id();

        // Get virtual classes where user is enrolled
        $enrolledClasses = VirtualClass::whereHas('enrollments', function ($query) use ($userId) {
            $query->where('student_id', $userId);
        })->with('lessons')->get();

        // Filter classes where all lessons are completed
        $completedClasses = $enrolledClasses->filter(function ($virtualClass) use ($userId) {
            $totalLessons = $virtualClass->lessons->count();
            $completedLessons = LessonView::where('user_id', $userId)
                ->whereIn('lesson_id', $virtualClass->lessons->pluck('id'))
                ->count();
            return $totalLessons > 0 && $totalLessons === $completedLessons;
        });

        // Get certificates already generated
        $certificates = Certificate::where('user_id', $userId)
            ->whereIn('class_id', $completedClasses->pluck('id'))
            ->get();

        return view('student.certificates', compact('completedClasses', 'certificates'));
    }

    /**
     * Generate certificate for a completed virtual class
     */
    public function generateCertificate($classId)
    {
        $user = Auth::user();
        $virtualClass = VirtualClass::with('lessons')->findOrFail($classId);

        // Check enrollment
        $enrollment = ClassEnrollment::where('student_id', $user->id)
            ->where('class_id', $virtualClass->id)
            ->first();

        if (!$enrollment) {
            abort(403, 'You are not enrolled in this class.');
        }

        // Check if all lessons are completed
        $totalLessons = $virtualClass->lessons->count();
        $viewedLessons = LessonView::where('user_id', $user->id)
            ->whereIn('lesson_id', $virtualClass->lessons->pluck('id'))
            ->count();

        if ($totalLessons === 0 || $viewedLessons < $totalLessons) {
            abort(403, 'You have not completed all lessons of this virtual class.');
        }

        // Check if certificate already exists
        $certificate = Certificate::firstOrCreate(
            [
                'user_id' => $user->id,
                'class_id' => $virtualClass->id,
            ],
            [
                'certificate_url' => '', // Will update after PDF generation
                'issued_at' => now(),
            ]
        );

        // Generate PDF only if certificate URL not set
        if (!$certificate->certificate_url) {
            $certificateUrl = $this->createCertificatePdf($user, $virtualClass);
            $certificate->update(['certificate_url' => $certificateUrl]);
        }

        return redirect($certificate->certificate_url);
    }



    public function showAssignment($id)
    {
        $assignment = Assignment::with('submissions')
            ->findOrFail($id);

        $submission = $assignment->submissions
            ->where('student_id', auth()->id())
            ->first();

        return view('student.assignment_show', compact('assignment', 'submission'));
    }


    public function submitAssignment(Request $request, $assignmentId)
{
    $assignment = Assignment::findOrFail($assignmentId);

    $existingSubmission = Submission::where('assignment_id', $assignment->id)
        ->where('student_id', auth()->id())
        ->first();

    
    if ($existingSubmission && $existingSubmission->status !== 'late') {
        return back()->with('error', 'You have already submitted this assignment.');
    }

    $request->validate([
        'file' => 'required|file|max:10240',
    ]);

    $isLate = false;

    if ($assignment->due_date && now()->greaterThan($assignment->due_date)) {
        $isLate = true;
    }

    $filePath = $request->file('file')
        ->store('assignments', 'public');

    Submission::updateOrCreate(
        [
            'assignment_id' => $assignment->id,
            'student_id' => auth()->id(),
        ],
        [
            'file_path' => $filePath,
            'submitted_at' => now(),
            'status' => $isLate ? 'late' : 'submitted',
        ]
    );

    return back()->with('success', 'Assignment submitted successfully.');
}
    /**
     * PDF Generation using Dompdf for virtual class certificate
     */
    protected function createCertificatePdf($user, $virtualClass)
    {
        $pdf = Pdf::loadView('certificates.template', [
            'user' => $user,
            'virtualClass' => $virtualClass,
            'issued_at' => now(),
        ]);

        $fileName = 'certificates/' . $user->id . '_class_' . $virtualClass->id . '.pdf';
        $path = storage_path('app/public/' . $fileName);

        // Ensure directory exists
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $pdf->save($path);

        return asset('storage/' . $fileName);
    }
}
