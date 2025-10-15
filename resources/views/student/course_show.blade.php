@extends('site.layouts.app')

@section('pageTitle', $course->title)

@section('content')
<section id="course-details" class="position-relative py-5 min-vh-100 overflow-hidden"
    style="background: linear-gradient(135deg, #e0f7fa 0%, #f5f9ff 50%, #e3f2fd 100%);">

    <div class="container position-relative" style="z-index: 1;" data-aos="fade-up">

        {{-- Header Section --}}
        <div class="row align-items-center mb-5">
            {{-- Course Image --}}
            <div class="col-lg-5 mb-4 mb-lg-0">
                <div class="overflow-hidden rounded-4 shadow-lg border border-white border-opacity-50">
                    <img src="{{ asset('storage/' . $course->image) }}"
                         alt="{{ $course->title }}"
                         class="img-fluid w-100"
                         style="object-fit: cover; height: 380px; transition: transform 0.5s ease;">
                </div>
            </div>

            {{-- Course Info --}}
            <div class="col-lg-7 ps-lg-5">
                <div class="d-flex align-items-center gap-3 flex-wrap mb-3">
                    <span class="badge fs-6 text-white shadow-sm px-3 py-2"
                        style="background: linear-gradient(90deg, #007bff, #00b4d8);">
                        <i class="bi bi-compass me-1"></i> {{ ucfirst($course->level) }}
                    </span>

                    <span class="text-muted small">
                        <i class="bi bi-journal-bookmark me-1 text-primary"></i>
                        {{ $course->lessons->count() }} Lessons
                    </span>
                </div>

                <h2 class="fw-bold text-dark mb-3">{{ $course->title }}</h2>
                <p class="text-secondary fs-6 mb-4">{{ $course->description }}</p>

                {{-- Progress Bar --}}
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <small class="text-muted fw-semibold">Progress</small>
                        <small class="fw-bold text-success">{{ $progress }}%</small>
                    </div>
                    <div class="progress rounded-pill bg-white shadow-sm" style="height: 14px;">
                        <div class="progress-bar progress-bar-striped   rounded-pill"
                            style="width: {{ $progress }}%; background: linear-gradient(90deg, #00c853, #b2ff59);">
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="d-flex flex-wrap gap-3 mt-4">
                    @if($progress < 100)
                        <a href="{{ route('student.course.continue', $course->id) }}"
                           class="btn btn-lg text-white fw-semibold rounded-pill px-4 shadow-sm"
                           style="background: linear-gradient(90deg, #00b09b, #96c93d); box-shadow: 0 4px 15px rgba(0, 176, 155, 0.3);">
                            <i class="bi bi-play-fill me-2"></i> Continue Learning
                        </a>
                    @else
                        <span class="badge fs-6 px-4 py-3 rounded-pill shadow-sm text-white"
                              style="background: linear-gradient(90deg, #ff7e5f, #feb47b); box-shadow: 0 4px 12px rgba(255, 126, 95, 0.4);">
                            <i class="bi bi-trophy-fill me-1"></i> Completed 🎉
                        </span>
                    @endif

                    <a href="{{ route('student.my_courses') }}"
                       class="btn btn-outline-dark btn-lg rounded-pill px-4 fw-semibold shadow-sm">
                        <i class="bi bi-arrow-left-circle me-2"></i> Back to My Courses
                    </a>
                </div>
            </div>
        </div>

        {{-- Divider --}}
        <hr class="my-5" style="border-top: 2px dashed rgba(0,0,0,0.1);">

        {{-- Lessons List --}}
        <div class="row">
            <div class="col-12">
                <div class="d-flex align-items-center mb-4">
                    <div class="me-2 text-primary fs-4"><i class="bi bi-map-fill"></i></div>
                    <h4 class="fw-bold text-dark mb-0">Adventure Checkpoints</h4>
                </div>

                <div class="list-group rounded-4 shadow-sm border-0 overflow-hidden">
                    @forelse($course->lessons ?? [] as $index => $lesson)
                        @php
                            $isCompleted = \App\Models\LessonView::where('user_id', auth()->id())
                                ->where('lesson_id', $lesson->id)
                                ->exists();
                        @endphp

                        <a href="{{ route('student.lesson.show', $lesson->id) }}"
                           class="list-group-item list-group-item-action py-4 px-4 d-flex justify-content-between align-items-center border-0 border-bottom"
                           style="transition: all 0.3s ease; background: {{ $isCompleted ? 'linear-gradient(90deg, #f0fff4, #e6ffe6)' : '#ffffff' }};">
                            <div class="d-flex align-items-start gap-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center"
                                     style="width: 44px; height: 44px;
                                            background: {{ $isCompleted ? 'linear-gradient(90deg, #00c851, #007e33)' : 'linear-gradient(90deg, #9e9e9e, #757575)' }};
                                            color: white; font-weight: bold;">
                                    {{ $index + 1 }}
                                </div>
                                <div>
                                    <h6 class="fw-semibold text-dark mb-1">{{ $lesson->title }}</h6>
                                    <p class="small text-muted mb-0">{{ Str::limit($lesson->description, 80) }}</p>
                                </div>
                            </div>

                            <span class="badge {{ $isCompleted ? 'bg-success' : 'bg-secondary' }} rounded-pill px-3 py-2 shadow-sm">
                                <i class="bi {{ $isCompleted ? 'bi-check-circle-fill' : 'bi-hourglass-split' }}"></i>
                                {{ $isCompleted ? 'Completed' : 'Pending' }}
                            </span>
                        </a>
                    @empty
                        <div class="alert alert-info rounded-4 mb-0 p-4 text-center shadow-sm bg-white border-0">
                            <i class="bi bi-info-circle me-2"></i> No lessons have been added yet.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Hover & Animation Styling --}}
<style>
    #course-details img:hover {
        transform: scale(1.04);
    }

    .list-group-item:hover {
        transform: translateY(-2px);
        background: #f9fcff !important;
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.05);
    }

   
</style>
@endsection
