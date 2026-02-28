<?php

use App\Events\TestSignalEvent;
use App\Events\WebrtcSignal;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CodeRunController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\WebRTCController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// Login
Volt::route('/login', 'login')->name('login');

//Logout
Route::get('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/');
})->name('logout');

Route::get('/register', [RegisterController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.store');


Route::get('/', [SiteController::class, 'index'])->name('home');
Route::get('/about', [SiteController::class, 'about'])->name('about');
Route::get('/classes', [SiteController::class, 'classes'])->name('site.classes');
Route::get('/classe-detail/{class_id}', [SiteController::class, 'class_detail'])->name('class.detail');
Route::get('/contact', [SiteController::class, 'contact'])->name('contact');
Route::get('/enroll/{class_id?}', [SiteController::class, 'enroll'])->name('enroll');
Route::post('/enroll/save', [SiteController::class, 'enroll_save'])->name('enroll.save');
Route::get('/register', [SiteController::class, 'register'])->name('register');


Route::get('/meeting/{session}', [MeetingController::class, 'join'])
    ->name('meeting');

Route::post('/webrtc/signal', [WebRTCController::class, 'signal']);
Route::post('/webrtc/join', [WebRTCController::class, 'join']);
Route::post('/webrtc/leave', [WebRTCController::class, 'leave']);
Route::post('/webrtc/chat', [WebRTCController::class, 'chat']);
 


Route::get('/webrtc/test', function (\Illuminate\Http\Request $request) {
    broadcast(new TestSignalEvent(
        "Hello from WebRTC signaling!",
    ))->toOthers();

    return response()->json(['ok' => true]);
});



Route::middleware(['auth'])->group(function () {
    // Dashboard & Courses
    Route::get('/student/dashboard', [StudentController::class, 'dashboard'])->name('student.dashboard');
    Route::get('/student/my-courses', [StudentController::class, 'myCourses'])->name('student.my_courses');
    Route::get('/student/quiz-attempts', [StudentController::class, 'myQuizAttempts'])->name('student.my_quiz_attempts');
    Route::get('/student/class/{class_id}', [StudentController::class, 'showCourse'])->name('student.course.show');
    Route::get('/student/class/{class}/continue', [StudentController::class, 'continueCourse'])->name('student.course.continue');
    Route::get('/student/lesson/{lesson}', [StudentController::class, 'lessonShow'])->name('student.lesson.show');


    // Quizzes
    Route::get('/student/quiz/{quiz}', [StudentController::class, 'quizShow'])->name('student.quiz.show');
    Route::get('/student/quiz/{quiz}/start', [StudentController::class, 'quizStart'])->name('student.quiz.start');
    Route::post('/student/quiz/{quiz}/attempt', [StudentController::class, 'submitQuiz'])->name('student.quiz.attempt');

    Route::get('/student/certificates', [StudentController::class, 'studentCertificates'])
        ->name('student.certificates');
    Route::get('/student/certificate/{course_id}', [StudentController::class, 'generateCertificate'])
        ->name('student.downloadCertificate');
});

Route::get('/student/lesson/{lesson}/download', [StudentController::class, 'downloadLessonPdf'])
    ->name('student.lesson.download');


// end student routes

Route::prefix('admin')->middleware('auth')->group(function () {
    Volt::route('/dashboard', 'dashboard')->name('dashboard');
    Volt::route('/users', 'users.index')->name('user.index');

    Volt::route('/languages', 'languages.index')->name('languages.index');
    Volt::route('/courses', 'courses.index')->name('course.index');

    Volt::route('/lessons', 'lessons/index')->name('lessons.index');

    Volt::route('/exercises', 'exercises.index')->name('exercises.index');
    Volt::route('/quizzes', 'quizzes.index')->name('quizzes.index');
    Volt::route('/questions', 'questions.index')->name('questions.index');
    Volt::route('/options', 'options.index')->name('options.index');

    Volt::route('/enrollments', 'enrollments.index')->name('enrollments.index');
    Volt::route('/quiz_attempts', 'quiz_attempts.index')->name('quiz_attempts');
    Volt::route('/certificates', 'certificates.index')->name('certificates.index');

    // Virtual Classroom Routes
    Volt::route('/classes', 'classes.index')->name('classes.index');
    Volt::route('/live-sessions', 'live-sessions.index')->name('live-sessions.index');
    Volt::route('/assignments', 'assignments.index')->name('assignments.index');
    Volt::route('/submissions', 'submissions.index')->name('submissions.index');
    Volt::route('/attendance', 'attendance.index')->name('attendance.index');
    Volt::route('/study-groups', 'study-groups.index')->name('study-groups.index');
    Volt::route('/notifications', 'notifications.index')->name('notifications.index');
});

Route::middleware('auth')->group(function () {



    Volt::route('/posts/create', 'posts.create');
    Volt::route('/posts/{post}/edit', 'posts.edit');
    Volt::route('/profile', 'profile');
});

Volt::route('/posts/{post}', 'posts.show');


Route::get('/test-event', function () {
    event(new TestSignalEvent('Hello from Laravel Reverb!'));
    return view('test');
});
