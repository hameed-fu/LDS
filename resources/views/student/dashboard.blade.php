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
            Here’s your learning overview and enrolled courses
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
                    <h3 class="fw-bold mb-0">{{ $totalCourses }}</h3>
                    <p class="text-muted mb-0">Total Enrolled Courses</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 text-center hover-shadow bg-white">
                    <div class="icon mb-3 text-success fs-2">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <h3 class="fw-bold mb-0">{{ $totalLessons }}</h3>
                    <p class="text-muted mb-0">Lessons Completed</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 text-center hover-shadow bg-white">
                    <div class="icon mb-3 text-info fs-2">
                        <i class="bi bi-calendar3"></i>
                    </div>
                    <h3 class="fw-bold mb-0">{{ now()->format('M d, Y') }}</h3>
                    <p class="text-muted mb-0">Today’s Date</p>
                </div>
            </div>
        </div>
    </div>

    {{-- My Courses Section --}}
    <div class="container" data-aos="fade-up" data-aos-delay="200">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-semibold mb-0">My Courses</h3>
            <a href="{{ route('site.courses') }}" class="btn btn-primary btn-sm rounded-pill px-3">
                <i class="bi bi-plus-circle me-1"></i> Enroll More
            </a>
        </div>

        <div class="row g-4">
            @forelse ($enrollments as $enrollment)
                @php
                    $course = $enrollment->course;
                    $progress = $enrollment->progress ?? 0;
                    $completedLessons = $enrollment->completed_lessons ?? 0;
                    $totalLessons = $course->lessons->count();
                @endphp

                <div class="col-lg-4 col-md-6">
                    <div class="card shadow-sm border-0 h-100 hover-shadow transition-all rounded-3 overflow-hidden">
                        <img src="{{ asset('storage/' . $course->image) }}" class="card-img-top rounded-top"
                            alt="{{ $course->title }}">

                        <div class="card-body">
                            <h5 class="card-title fw-semibold text-dark mb-2">{{ $course->title }}</h5>
                            <p class="text-muted small mb-3">{{ Str::limit($course->description, 90) }}</p>

                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-primary">{{ ucfirst($course->level) }}</span>
                                <small class="text-muted">{{ $progress }}% Completed</small>
                            </div>

                            {{-- Progress bar --}}
                            <div class="progress mb-2" style="height: 8px;">
                                <div class="progress-bar bg-success" role="progressbar"
                                    style="width: {{ $progress }}%;" aria-valuenow="{{ $progress }}"
                                    aria-valuemin="0" aria-valuemax="100"></div>
                            </div>

                            <p class="small text-muted mb-3">
                                {{ $completedLessons }} / {{ $totalLessons }} lessons completed
                            </p>

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('student.course.show', $course->id) }}"
                                    class="btn btn-sm btn-outline-primary">
                                    View Details
                                </a>

                                @if ($progress < 100)
                                    <a href="{{ route('student.course.continue', $course->id) }}"
                                        class="btn btn-sm btn-success">
                                        Continue
                                    </a>
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
                        You haven’t enrolled in any courses yet.
                        <a href="{{ route('site.courses') }}" class="fw-semibold text-decoration-none">Browse Courses</a>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</section>

{{-- Optional Custom Styles --}}
<style>
    .hover-shadow:hover {
        transform: translateY(-4px);
        transition: all 0.3s ease-in-out;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1) !important;
    }

    .progress {
        background-color: #f1f1f1;
    }
</style>
@endsection
