@extends('site.layouts.app')

@section('pageTitle', 'Student Dashboard')

@section('content')
<section id="student-dashboard" class="section light-background py-5">
    {{-- Header --}}
    <div class="container text-center mb-5" data-aos="fade-up">
        <h2 class="fw-bold mb-2">
            Welcome back, <span class="text-primary">{{ $user->name }}</span> 👋
        </h2>
        <p class="text-muted mb-0">
            Here's your learning overview and enrolled virtual classes
        </p>
    </div>

    {{-- Analytics Section --}}
    <div class="container mb-5" data-aos="fade-up" data-aos-delay="100">
        <div class="row g-4 justify-content-center">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 text-center hover-shadow bg-white">
                    <div class="icon mb-3 text-primary fs-2">
                        <i class="bi bi-journal-text"></i>
                    </div>
                    <h3 class="fw-bold mb-0">{{ $totalClasses }}</h3>
                    <p class="text-muted mb-0">Enrolled Virtual Classes</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 text-center hover-shadow bg-white">
                    <div class="icon mb-3 text-success fs-2">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <h3 class="fw-bold mb-0">{{ $totalSessionsCompleted }}</h3>
                    <p class="text-muted mb-0">Live Sessions Attended</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 text-center hover-shadow bg-white">
                    <div class="icon mb-3 text-info fs-2">
                        <i class="bi bi-calendar3"></i>
                    </div>
                    <h3 class="fw-bold mb-0">{{ now()->format('M d, Y') }}</h3>
                    <p class="text-muted mb-0">Today's Date</p>
                </div>
            </div>
        </div>
    </div>

    {{-- My Virtual Classes Section --}}
    <div class="container" data-aos="fade-up" data-aos-delay="200">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-semibold mb-0">My Virtual Classes</h3>
            <a href="{{ route('site.classes') }}" class="btn btn-primary btn-sm rounded-pill px-3">
                <i class="bi bi-plus-circle me-1"></i> Enroll More
            </a>
        </div>

        <div class="row g-4">
            @forelse ($enrollments as $enrollment)
                @php
                    $virtualClass = $enrollment->virtualClass;
                    $progress = $enrollment->progress ?? 0;
                    $completedSessions = $enrollment->completed_sessions ?? 0;
                    $totalSessions = $virtualClass->liveSessions->count();
                @endphp

                <div class="col-lg-4 col-md-6">
                    <div class="card shadow-sm border-0 h-100 hover-shadow transition-all rounded-3 overflow-hidden">
                        {{-- You might want to add an image field to VirtualClass model --}}
                        @if($virtualClass->image ?? false)
                            <img src="{{ asset('storage/' . $virtualClass->image) }}" class="card-img-top rounded-top"
                                alt="{{ $virtualClass->name }}" style="height: 180px; object-fit: cover;">
                        @else
                            <div class="bg-primary text-white p-4 text-center rounded-top" style="height: 180px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-laptop fs-1"></i>
                            </div>
                        @endif

                        <div class="card-body">
                            <h5 class="card-title fw-semibold text-dark mb-2">{{ $virtualClass->name }}</h5>
                            <p class="text-muted small mb-3">{{ Str::limit($virtualClass->description, 90) }}</p>

                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-primary">
                                    <i class="bi bi-person-fill me-1"></i>
                                    {{ $virtualClass->teacher->name ?? 'Teacher' }}
                                </span>
                                <small class="text-muted">{{ $progress }}% Completed</small>
                            </div>

                            {{-- Progress bar --}}
                            <div class="progress mb-2" style="height: 8px;">
                                <div class="progress-bar bg-success" role="progressbar"
                                    style="width: {{ $progress }}%;" aria-valuenow="{{ $progress }}"
                                    aria-valuemin="0" aria-valuemax="100"></div>
                            </div>

                            <p class="small text-muted mb-3">
                                {{ $completedSessions }} / {{ $totalSessions }} sessions attended
                            </p>

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('student.course.show', $virtualClass->id) }}"
                                    class="btn btn-sm btn-outline-primary">
                                    View Details
                                </a>

                                @if ($progress < 100 && $totalSessions > 0)
                                    <a href="{{ route('student.course.show', $virtualClass->id) }}"
                                        class="btn btn-sm btn-success">
                                        Continue
                                    </a>
                                @elseif($totalSessions === 0)
                                    <span class="badge bg-warning py-2 px-3">No Sessions Yet</span>
                                @else
                                    <span class="badge bg-success py-2 px-3">Completed</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <div class="alert alert-info shadow-sm d-inline-block px-4 py-3 rounded-3">
                        You haven't enrolled in any virtual classes yet.
                        <a href="{{ route('site.classes') }}" class="fw-semibold text-decoration-none">Browse Classes</a>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</section>
@endsection