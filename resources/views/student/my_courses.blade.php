@extends('site.layouts.app')

@section('pageTitle', 'My Courses')

@section('content')
<section id="my-courses" class="section light-background py-5">
    <div class="container section-title mb-5" data-aos="fade-up">
        <h2 class="text-3xl font-bold text-gray-900">My Courses</h2>
        <p class="text-gray-600">Track your enrolled courses and learning progress</p>
    </div>

    <div class="container" data-aos="fade-up" data-aos-delay="100">
        <div class="row g-4">
            @forelse ($enrollments as $enrollment)
                @php
                    $course = $enrollment->course;
                    $progress = $enrollment->progress;
                @endphp

                <div class="col-lg-4 col-md-6">
                    <div class="card shadow-sm border-0 h-100 hover-shadow transition-all rounded-3">
                        <img src="{{ asset('storage/' . $course->image) }}" class="card-img-top rounded-top" alt="{{ $course->title }}">
                        <div class="card-body">
                            <h5 class="card-title fw-semibold text-dark mb-2">{{ $course->title }}</h5>
                            <p class="text-muted small mb-3">{{ Str::limit($course->description, 90) }}</p>

                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-primary">{{ ucfirst($course->level) }}</span>
                                <small class="text-muted">{{ $progress }}% Completed</small>
                            </div>

                            {{-- Progress bar --}}
                            <div class="progress mb-3" style="height: 8px;">
                                <div class="progress-bar bg-success" role="progressbar"
                                    style="width: {{ $progress }}%;"
                                    aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('student.course.show', $course->id) }}" class="btn btn-sm btn-outline-primary">View Details</a>

                                @if($progress < 100)
                                    <a href="{{ route('student.course.continue', $course->id) }}" class="btn btn-sm btn-success">Continue</a>
                                @else
                                    <span class="badge bg-success">Completed</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <div class="alert alert-info shadow-sm d-inline-block px-4 py-3 rounded-3">
                        You haven’t enrolled in any courses yet. 
                        <a href="{{ route('enroll') }}" class="fw-semibold text-decoration-none">Browse Courses</a>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</section>
@endsection
