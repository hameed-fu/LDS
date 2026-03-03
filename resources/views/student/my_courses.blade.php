@extends('site.layouts.app')

@section('pageTitle', 'My Virtual Classes')

@section('content')
<section id="my-courses" class="section light-background py-5">
    <div class="container section-title mb-5" data-aos="fade-up">
        <h2 class="text-3xl font-bold text-gray-900">My Virtual Classes</h2>
        <p class="text-gray-600">Track your enrolled classes and learning progress</p>
    </div>

    <div class="container" data-aos="fade-up" data-aos-delay="100">
        <div class="row g-4">
            @forelse ($enrollments as $enrollment)
                @php
                    $virtualClass = $enrollment->virtualClass;
                    $progress = $enrollment->progress ?? 0;
                    $completedSessions = $enrollment->completed_sessions ?? 0;
                    $totalSessions = $virtualClass->liveSessions->count();
                @endphp

                <div class="col-lg-4 col-md-6">
                    <div class="card shadow-sm border-0 h-100 hover-shadow transition-all rounded-3">
                        {{-- Image or placeholder --}}
                        @if($virtualClass->image ?? false)
                            <img src="{{ asset('storage/' . $virtualClass->image) }}" 
                                 class="card-img-top rounded-top" 
                                 alt="{{ $virtualClass->name }}"
                                 style="height: 200px; object-fit: cover;">
                        @else
                            <div class="card-img-top rounded-top bg-primary text-white d-flex align-items-center justify-content-center" 
                                 style="height: 200px;">
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
                            <div class="progress mb-3" style="height: 8px;">
                                <div class="progress-bar bg-success" role="progressbar"
                                    style="width: {{ $progress }}%;"
                                    aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>

                            {{-- Sessions info --}}
                            <p class="small text-muted mb-3">
                                <i class="bi bi-camera-video me-1"></i>
                                {{ $completedSessions }} / {{ $totalSessions }} sessions attended
                            </p>

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('student.course.show', $virtualClass->id) }}" 
                                   class="btn btn-sm btn-outline-primary">View Details</a>

                                @if($progress < 100 && $totalSessions > 0)
                                    <a href="{{ route('student.course.show', $virtualClass->id) }}" 
                                       class="btn btn-sm btn-success">Continue</a>
                                @elseif($totalSessions === 0)
                                    <span class="badge bg-warning">No Sessions Yet</span>
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
                        You haven't enrolled in any virtual classes yet. 
                        <a href="{{ route('site.classes') }}" class="fw-semibold text-decoration-none">Browse Classes</a>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</section>

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