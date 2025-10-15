<?php

use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CodeRunController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\StudentController;
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
Route::get('/courses', [SiteController::class, 'courses'])->name('site.courses');
Route::get('/cours-detail/{course_id}', [SiteController::class, 'course_detail'])->name('course.detail');
Route::get('/contact', [SiteController::class, 'contact'])->name('contact');
Route::get('/enroll/{course_id?}', [SiteController::class, 'enroll'])->name('enroll');
Route::post('/enroll/save', [SiteController::class, 'enroll_save'])->name('enroll.save');
Route::get('/register', [SiteController::class, 'register'])->name('register');





Route::middleware(['auth'])->group(function () {
    // Dashboard & Courses
    Route::get('/student/dashboard', [StudentController::class, 'dashboard'])->name('student.dashboard');
    Route::get('/student/my-courses', [StudentController::class, 'myCourses'])->name('student.my_courses');
    Route::get('/student/quiz-attempts', [StudentController::class, 'myQuizAttempts'])->name('student.my_quiz_attempts');
    Route::get('/student/course/{course}', [StudentController::class, 'showCourse'])->name('student.course.show');
    Route::get('/student/course/{course}/continue', [StudentController::class, 'continueCourse'])->name('student.course.continue');
    Route::get('/student/lesson/{lesson}', [StudentController::class, 'lessonShow'])->name('student.lesson.show');

    // Exercises
    Route::get('/student/exercise/{exercise}', [StudentController::class, 'exerciseShow'])->name('student.exercise.show');
    Route::get('/run-code', [CodeRunController::class, 'index'])->name('run.index');
    Route::post('/run', [CodeRunController::class, 'execute'])->name('run.execute');
    // Quizzes
    Route::get('/student/quiz/{quiz}', [StudentController::class, 'quizShow'])->name('student.quiz.show');
    Route::get('/student/quiz/{quiz}/start', [StudentController::class, 'quizStart'])->name('student.quiz.start');
    Route::post('/student/quiz/{quiz}/attempt', [StudentController::class, 'submitQuiz'])->name('student.quiz.attempt');
});

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
});

Route::middleware('auth')->group(function () {



    Volt::route('/posts/create', 'posts.create');
    Volt::route('/posts/{post}/edit', 'posts.edit');
    Volt::route('/profile', 'profile');
});

Volt::route('/posts/{post}', 'posts.show');
