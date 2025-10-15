@extends('site.layouts.app')

@section('pageTitle', $lesson->title)

@section('content')
<section id="lesson-view" class="py-5 bg-light">
    <div class="container">

        {{-- Header --}}
        <div class="mb-5 text-center">
            <h2 class="fw-bold text-dark mb-2">{{ $lesson->title }}</h2>
            <p class="text-muted">
                Part of the course:
                <a href="{{ route('student.course.show', $lesson->course->id) }}" class="text-primary fw-semibold">
                    {{ $lesson->course->title }}
                </a>
            </p>
        </div>

        <div class="row g-4">
            {{-- Lesson Content --}}
            <div class="col-lg-8">
                <div class="card border-0 shadow rounded-4 overflow-hidden">
                    {{-- Video --}}
                    @if($lesson->video_url)
                        <div class="ratio ratio-16x9">
                            <iframe src="{{ $lesson->video_url }}" allowfullscreen style="border:none;"></iframe>
                        </div>
                    @endif

                    {{-- Text Content --}}
                    <div class="p-4">
                        <div class="lesson-content text-secondary fs-6 lh-lg">
                            {!! $lesson->content ?? 'No content available for this lesson.' !!}
                        </div>
                    </div>
                </div>

                {{-- Exercises Section --}}
                @if($lesson->exercises->count())
                    <div class="mt-5">
                        <h4 class="fw-semibold mb-3 text-dark">
                            <i class="bi bi-code-square text-primary me-2"></i> Exercises
                        </h4>

                        @foreach($lesson->exercises as $exercise)
                            <div class="card mb-4 border-0 shadow-sm rounded-4">
                                <div class="card-body">
                                    <h5 class="fw-bold text-dark mb-2">{{ $exercise->title }}</h5>
                                    <p class="text-muted mb-3">{{ $exercise->description }}</p>

                                    @if($exercise->sample_code)
                                        <pre class="bg-light p-3 rounded text-dark"><code>{{ $exercise->sample_code }}</code></pre>
                                    @endif

                                    <div class="mt-3">
                                        <a href="{{ route('student.exercise.show', $exercise->id) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                            <i class="bi bi-play-circle me-1"></i> Try Exercise
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Quizzes Section --}}
                @if($lesson->quizzes->count())
                    <div class="mt-5">
                        <h4 class="fw-semibold mb-3 text-dark">
                            <i class="bi bi-question-circle text-warning me-2"></i> Lesson Quizzes
                        </h4>

                        @foreach($lesson->quizzes as $quiz)
                            <div class="card border-0 shadow-sm rounded-4 mb-4">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="fw-bold text-dark mb-1">{{ $quiz->title }}</h5>
                                        <small class="text-muted">{{ $quiz->questions->count() }} Questions</small>
                                    </div>
                                    <a href="{{ route('student.quiz.start', $quiz->id) }}" class="btn btn-warning text-dark rounded-pill px-4">
                                        <i class="bi bi-pencil-square me-1"></i> Attempt Quiz
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Navigation Buttons --}}
                <div class="d-flex justify-content-between align-items-center mt-4">
                    @php
                        $lessons = $lesson->course->lessons;
                        $currentIndex = $lessons->search(fn($l) => $l->id === $lesson->id);
                        $prevLesson = $lessons->get($currentIndex - 1);
                        $nextLesson = $lessons->get($currentIndex + 1);
                    @endphp

                    @if($prevLesson)
                        <a href="{{ route('student.lesson.show', $prevLesson->id) }}" class="btn btn-outline-secondary rounded-pill px-4 py-2">
                            <i class="bi bi-arrow-left-circle me-2"></i> Previous Lesson
                        </a>
                    @else
                        <span></span>
                    @endif

                    @if($nextLesson)
                        <a href="{{ route('student.lesson.show', $nextLesson->id) }}" class="btn btn-primary rounded-pill px-4 py-2">
                            Next Lesson <i class="bi bi-arrow-right-circle ms-2"></i>
                        </a>
                    @else
                        <a href="{{ route('student.course.show', $lesson->course->id) }}" class="btn btn-success rounded-pill px-4 py-2">
                            🎉 Course Completed
                        </a>
                    @endif
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow rounded-4 overflow-hidden">
                    <div class="card-header bg-primary text-white fw-semibold py-3">
                        <i class="bi bi-list-check me-2"></i> Lessons in this Course
                    </div>

                    <div class="list-group list-group-flush">
                        @foreach ($lesson->course->lessons as $index => $l)
                            @php
                                $viewed = in_array($l->id, $viewedLessonIds);
                                $isActive = $l->id === $lesson->id;
                            @endphp
                            <a href="{{ route('student.lesson.show', $l->id) }}"
                               class="list-group-item d-flex justify-content-between align-items-center py-3 px-4 {{ $isActive ? 'active-lesson' : 'lesson-item' }}">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fw-semibold">{{ $index + 1 }}.</span>
                                    <span class="{{ $isActive ? 'fw-bold text-primary' : 'text-dark' }}">
                                        {{ $l->title }}
                                    </span>
                                </div>
                                @if ($viewed)
                                    <span class="badge bg-success rounded-pill">
                                        <i class="bi bi-check-circle-fill"></i>
                                    </span>
                                @endif
                            </a>
                        @endforeach
                    </div>

                    {{-- Progress --}}
                    <div class="p-4 border-top">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="text-muted">Progress</small>
                            <small class="text-success fw-semibold">{{ $progress }}%</small>
                        </div>
                        <div class="progress rounded-pill" style="height:10px;">
                            <div class="progress-bar bg-success" style="width: {{ $progress }}%;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Custom CSS --}}
    <style>
        .lesson-item:hover {
            background-color: #f9fafb;
            transform: translateX(3px);
        }
        .active-lesson {
            background-color: #eef5ff;
            border-left: 4px solid #0d6efd;
        }
        .lesson-content p { margin-bottom: 1rem; }
        .lesson-content h4 { font-weight: 600; color: #333; margin-top: 1.5rem; }
        pre code { font-family: "Fira Code", monospace; }
    </style>
</section>
@endsection
