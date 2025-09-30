<?php

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
});

Volt::route('/', 'posts.index');

Route::prefix('admin')->middleware('auth')->group(function () {
    Volt::route('/dashboard', 'dashboard')->name('dashboard');
    Volt::route('/users', 'users.index')->name('user.index');

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
