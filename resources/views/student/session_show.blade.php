@extends('site.layouts.app')

@section('pageTitle', $session->virtualClass->name . ' - Assignments & Quizzes')

@section('content')
<section class="py-5 position-relative" 
    style="background: linear-gradient(135deg, #f8fbff 0%, #f6f9fc 40%, #eef2f7 100%); min-height: 100vh;">

    <div class="container">

        {{-- HEADER --}}
        <div class="mb-5">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h2 class="fw-bold text-dark mb-1">
                        <i class="bi bi-grid-1x2-fill text-primary me-2"></i>
                        Assignments & Quizzes
                    </h2>
                    <p class="text-muted mb-0">
                        Class: <span class="fw-semibold text-primary">{{ $session->virtualClass->name }}</span>
                    </p>
                </div>

                <a href="{{ route('student.course.show', $session->virtualClass->id) }}"
                   class="btn btn-light shadow-sm rounded-pill px-4">
                    <i class="bi bi-arrow-left me-2"></i> Back
                </a>
            </div>
        </div>

        {{-- STATS --}}
        <div class="row g-4 mb-5">

            {{-- Assignments Card --}}
            <div class="col-md-6">
                <div class="card border-0 shadow-lg rounded-4 h-100 stat-card">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="stat-icon bg-warning-subtle text-warning">
                                <i class="bi bi-journal-text"></i>
                            </div>
                            <div>
                                <h3 class="fw-bold mb-0">{{ $totalAssignments }}</h3>
                                <small class="text-muted">Assignments</small>
                            </div>
                        </div>

                        @php
                            $assignmentProgress = $totalAssignments > 0 
                                ? round(($submittedAssignments / $totalAssignments) * 100) 
                                : 0;
                        @endphp

                        <div class="progress rounded-pill mb-2" style="height: 8px;">
                            <div class="progress-bar bg-warning"
                                 style="width: {{ $assignmentProgress }}%">
                            </div>
                        </div>

                        <small class="text-muted">
                            {{ $submittedAssignments }} submitted • {{ $assignmentProgress }}%
                        </small>
                        
                    </div>
                </div>
            </div>

            {{-- Quiz Card --}}
            <div class="col-md-6">
                <div class="card border-0 shadow-lg rounded-4 h-100 stat-card">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="stat-icon bg-info-subtle text-info">
                                <i class="bi bi-question-circle"></i>
                            </div>
                            <div>
                                <h3 class="fw-bold mb-0">{{ $totalQuizzes }}</h3>
                                <small class="text-muted">Quizzes</small>
                            </div>
                        </div>

                        @php
                            $quizProgress = $totalQuizzes > 0 
                                ? round(($attemptedQuizzes / $totalQuizzes) * 100) 
                                : 0;
                        @endphp

                        <div class="progress rounded-pill mb-2" style="height: 8px;">
                            <div class="progress-bar bg-info"
                                 style="width: {{ $quizProgress }}%">
                            </div>
                        </div>

                        <small class="text-muted">
                            {{ $attemptedQuizzes }} attempted • {{ $quizProgress }}%
                        </small>
                    </div>
                </div>
            </div>

        </div>

        {{-- CONTENT GRID --}}
        <div class="row g-4">

            {{-- ASSIGNMENTS --}}
            <div class="col-lg-6">
                <div class="card border-0 shadow rounded-4 h-100 content-card">
                    <div class="card-header bg-transparent border-0 py-3">
                        <h5 class="fw-bold mb-0 text-warning">
                            <i class="bi bi-journal-text me-2"></i> Assignments
                        </h5>
                    </div>

                    <div class="card-body p-0">
                        @forelse($assignments as $assignment)
                            @php
                                $hasSubmitted = $assignment->submissions->count() > 0;
                                $submission = $assignment->submissions->first();
                            @endphp

                            <div class="item-card px-4 py-3 border-bottom">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="fw-semibold mb-1">{{ $assignment->title }}</h6>
                                        <small class="text-muted">
                                            Due: {{ $assignment->due_date?->format('M d, Y') ?? 'No deadline' }}
                                        </small>
                                    </div>

                                    <span class="badge rounded-pill {{ $hasSubmitted ? 'bg-success' : 'bg-warning text-dark' }}">
                                        {{ $hasSubmitted ? 'Submitted' : 'Pending' }}
                                    </span>
                                </div>

                                @if($hasSubmitted && $submission)
                                    <small class="text-success d-block mt-2">
                                        Grade: {{ $submission->grade ?? 'Pending' }}
                                    </small>
                                @endif

                                <a href="{{ route('student.assignment.show', $assignment->id) }}"
   class="btn btn-sm btn-warning rounded-pill mt-2">
    <i class="bi bi-eye me-1"></i> Open
</a>
                            </div>

                        @empty
                            <div class="text-center py-5 text-muted">
                                No assignments available.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- QUIZZES --}}
            <div class="col-lg-6">
                <div class="card border-0 shadow rounded-4 h-100 content-card">
                    <div class="card-header bg-transparent border-0 py-3">
                        <h5 class="fw-bold mb-0 text-info">
                            <i class="bi bi-question-circle me-2"></i> Quizzes
                        </h5>
                    </div>

                    <div class="card-body p-0">
                        @forelse($quizzes as $quiz)
                            @php
                                $attempt = $quiz->attempts->sortByDesc('score')->first();
                            @endphp

                            <div class="item-card px-4 py-3 border-bottom">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="fw-semibold mb-1">{{ $quiz->title }}</h6>
                                        <small class="text-muted">
                                            {{ $quiz->questions->count() }} Questions
                                        </small>
                                    </div>

                                    @if($attempt)
                                        <span class="badge bg-success rounded-pill">
                                            {{ $attempt->score }}%
                                        </span>
                                    @else
                                        <span class="badge bg-secondary rounded-pill">
                                            Not Attempted
                                        </span>
                                    @endif
                                </div>

                                <div class="mt-2">
                                    <a href="{{ route('student.quiz.start', $quiz->id) }}"
                                       class="btn btn-sm btn-info rounded-pill px-3">
                                        <i class="bi bi-play-circle me-1"></i>
                                        {{ $attempt ? 'Retake' : 'Start' }}
                                    </a>
                                </div>
                            </div>

                        @empty
                            <div class="text-center py-5 text-muted">
                                No quizzes available.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>

    </div>
</section>

<style>
.stat-card {
    transition: all .3s ease;
}
.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 25px rgba(0,0,0,0.08) !important;
}
.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size: 1.3rem;
    margin-right: 12px;
}
.content-card {
    transition: all .3s ease;
}
.item-card {
    transition: background .2s ease;
}
.item-card:hover {
    background: #f8fafc;
}
.progress {
    background: #edf2f7;
}
</style>

@endsection