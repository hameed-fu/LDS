@extends('site.layouts.app')

@section('pageTitle', $virtualClass->name . ' - Assignments & Quizzes')

@section('content')
<section id="assignments-quizzes" class="py-5 bg-light">
    <div class="container">
        {{-- Header --}}
        <div class="mb-5">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('student.my_courses') }}">My Classes</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('student.course.show', $virtualClass->id) }}">{{ $virtualClass->name }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Assignments & Quizzes</li>
                </ol>
            </nav>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="fw-bold text-dark mb-2">Assignments & Quizzes</h1>
                    <p class="text-muted mb-0">For class: <strong class="text-primary">{{ $virtualClass->name }}</strong></p>
                </div>
                <a href="{{ route('student.course.show', $virtualClass->id) }}" class="btn btn-outline-primary rounded-pill px-4">
                    <i class="bi bi-arrow-left me-2"></i> Back to Class
                </a>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="row mb-5">
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <div class="bg-light-warning rounded-circle p-3 me-3">
                                <i class="bi bi-journal-text text-warning fs-3"></i>
                            </div>
                            <div>
                                <h3 class="fw-bold mb-0">{{ $totalAssignments }}</h3>
                                <p class="text-muted mb-0">Total Assignments</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="d-flex justify-content-between mb-2">
                                <small class="text-muted">Submitted</small>
                                <small class="fw-bold text-success">{{ $submittedAssignments }} / {{ $totalAssignments }}</small>
                            </div>
                            <div class="progress rounded-pill" style="height: 8px;">
                                <div class="progress-bar bg-warning" 
                                     style="width: {{ $totalAssignments > 0 ? round(($submittedAssignments / $totalAssignments) * 100) : 0 }}%;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <div class="bg-light-info rounded-circle p-3 me-3">
                                <i class="bi bi-question-circle text-info fs-3"></i>
                            </div>
                            <div>
                                <h3 class="fw-bold mb-0">{{ $totalQuizzes }}</h3>
                                <p class="text-muted mb-0">Total Quizzes</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="d-flex justify-content-between mb-2">
                                <small class="text-muted">Attempted</small>
                                <small class="fw-bold text-success">{{ $attemptedQuizzes }} / {{ $totalQuizzes }}</small>
                            </div>
                            <div class="progress rounded-pill" style="height: 8px;">
                                <div class="progress-bar bg-info" 
                                     style="width: {{ $totalQuizzes > 0 ? round(($attemptedQuizzes / $totalQuizzes) * 100) : 0 }}%;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            {{-- Assignments Section --}}
            <div class="col-lg-6">
                <div class="card border-0 shadow rounded-4 h-100">
                    <div class="card-header bg-warning text-white rounded-top-4 py-3">
                        <h5 class="fw-bold mb-0">
                            <i class="bi bi-journal-text me-2"></i> Assignments
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        @if($assignments->count() > 0)
                            <div class="list-group list-group-flush">
                                @foreach($assignments as $assignment)
                                    @php
                                        $hasSubmitted = $assignment->submissions->count() > 0;
                                        $isOverdue = $assignment->due_date && now()->gt($assignment->due_date) && !$hasSubmitted;
                                        $submission = $hasSubmitted ? $assignment->submissions->first() : null;
                                    @endphp
                                    
                                    <div class="list-group-item border-0 px-4 py-3">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <h6 class="fw-semibold text-dark mb-1">{{ $assignment->title }}</h6>
                                                <p class="small text-muted mb-1">{{ Str::limit($assignment->description, 80) }}</p>
                                            </div>
                                            <span class="badge {{ $hasSubmitted ? 'bg-success' : ($isOverdue ? 'bg-danger' : 'bg-warning') }} rounded-pill">
                                                {{ $hasSubmitted ? 'Submitted' : ($isOverdue ? 'Overdue' : 'Pending') }}
                                            </span>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                <i class="bi bi-calendar me-1"></i>
                                                Due: {{ $assignment->due_date ? $assignment->due_date->format('M d, Y - h:i A') : 'No deadline' }}
                                            </small>
                                            
                                            <div class="d-flex gap-2">
                                                @if($hasSubmitted)
                                                    <a href="#" class="btn btn-sm btn-outline-success rounded-pill px-3">
                                                        <i class="bi bi-eye me-1"></i> View
                                                    </a>
                                                @else
                                                    <a href="#" class="btn btn-sm btn-warning rounded-pill px-3">
                                                        <i class="bi bi-upload me-1"></i> Submit
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        @if($hasSubmitted && $submission)
                                        <div class="mt-2">
                                            <small class="text-success">
                                                <i class="bi bi-check-circle me-1"></i>
                                                Submitted on {{ $submission->submitted_at->format('M d, Y') }}
                                                @if($submission->grade)
                                                    • Grade: <strong>{{ $submission->grade }}/{{ $assignment->total_marks ?? 100 }}</strong>
                                                @endif
                                            </small>
                                        </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-5">
                                <div class="mb-3">
                                    <i class="bi bi-journal-x text-warning fs-1"></i>
                                </div>
                                <h5 class="fw-semibold text-muted mb-2">No Assignments Yet</h5>
                                <p class="text-muted">No assignments have been created for this class.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Quizzes Section --}}
            <div class="col-lg-6">
                <div class="card border-0 shadow rounded-4 h-100">
                    <div class="card-header bg-info text-white rounded-top-4 py-3">
                        <h5 class="fw-bold mb-0">
                            <i class="bi bi-question-circle me-2"></i> Quizzes
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        @if($quizzes->count() > 0)
                            <div class="list-group list-group-flush">
                                @foreach($quizzes as $quiz)
                                    @php
                                        $hasAttempted = $quiz->attempts->count() > 0;
                                        $bestAttempt = $hasAttempted ? $quiz->attempts->sortByDesc('score')->first() : null;
                                        $questionCount = $quiz->questions_count ?? $quiz->questions->count();
                                    @endphp
                                    
                                    <div class="list-group-item border-0 px-4 py-3">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <h6 class="fw-semibold text-dark mb-1">{{ $quiz->title }}</h6>
                                                <p class="small text-muted mb-1">{{ Str::limit($quiz->description, 80) }}</p>
                                            </div>
                                            <span class="badge {{ $hasAttempted ? 'bg-success' : 'bg-info' }} rounded-pill">
                                                {{ $hasAttempted ? 'Attempted' : 'Not Attempted' }}
                                            </span>
                                        </div>
                                        
                                        <div class="row g-2 mb-2">
                                            <div class="col-6">
                                                <small class="text-muted">
                                                    <i class="bi bi-question-diamond me-1"></i>
                                                    {{ $questionCount }} Questions
                                                </small>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted">
                                                    <i class="bi bi-clock me-1"></i>
                                                    {{ $quiz->duration ?? 30 }} mins
                                                </small>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            @if($hasAttempted && $bestAttempt)
                                                <div>
                                                    <small class="text-success fw-semibold">
                                                        Best Score: {{ $bestAttempt->score }}%
                                                    </small>
                                                    <small class="text-muted d-block">
                                                        Last attempt: {{ $bestAttempt->attempted_at->diffForHumans() }}
                                                    </small>
                                                </div>
                                                <div class="d-flex gap-2">
                                                    @if($quiz->allow_multiple_attempts ?? false)
                                                        <a href="{{ route('student.quiz.start', $quiz->id) }}" 
                                                           class="btn btn-sm btn-info rounded-pill px-3">
                                                            <i class="bi bi-arrow-repeat me-1"></i> Retake
                                                        </a>
                                                    @endif
                                                    <a href="#" class="btn btn-sm btn-outline-info rounded-pill px-3">
                                                        <i class="bi bi-bar-chart me-1"></i> Results
                                                    </a>
                                                </div>
                                            @else
                                                <small class="text-muted">Not attempted yet</small>
                                                <a href="{{ route('student.quiz.start', $quiz->id) }}" 
                                                   class="btn btn-sm btn-info rounded-pill px-3">
                                                    <i class="bi bi-play-circle me-1"></i> Start Quiz
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-5">
                                <div class="mb-3">
                                    <i class="bi bi-question-square text-info fs-1"></i>
                                </div>
                                <h5 class="fw-semibold text-muted mb-2">No Quizzes Yet</h5>
                                <p class="text-muted">No quizzes have been created for this class.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Upcoming Deadlines --}}
        @if($assignments->where('due_date', '>', now())->count() > 0)
        <div class="mt-5">
            <div class="card border-0 shadow rounded-4">
                <div class="card-header bg-light border-0 rounded-top-4 py-3">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-calendar-check text-danger me-2"></i> Upcoming Deadlines
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($assignments->where('due_date', '>', now())->sortBy('due_date')->take(3) as $assignment)
                            @php
                                $daysRemaining = now()->diffInDays($assignment->due_date, false);
                                $isUrgent = $daysRemaining <= 2;
                            @endphp
                            
                            <div class="col-md-4">
                                <div class="border rounded-3 p-3 h-100 {{ $isUrgent ? 'border-danger bg-light-danger' : 'border-warning bg-light-warning' }}">
                                    <h6 class="fw-semibold mb-2">{{ $assignment->title }}</h6>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <i class="bi bi-calendar me-1"></i>
                                            {{ $assignment->due_date->format('M d') }}
                                        </small>
                                        <span class="badge {{ $isUrgent ? 'bg-danger' : 'bg-warning' }} rounded-pill">
                                            {{ $daysRemaining > 0 ? $daysRemaining . ' days' : 'Today' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</section>

{{-- Custom Styles --}}
<style>
    .bg-light-warning {
        background-color: rgba(255, 193, 7, 0.1);
    }
    
    .bg-light-info {
        background-color: rgba(13, 202, 240, 0.1);
    }
    
    .bg-light-danger {
        background-color: rgba(220, 53, 69, 0.1);
    }
    
    .list-group-item:hover {
        background-color: #f8f9fa;
        transition: background-color 0.2s;
    }
    
    .progress {
        background-color: #e9ecef;
    }
</style>
@endsection