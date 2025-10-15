@extends('site.layouts.app')

@section('pageTitle', 'Start Quiz - ' . $quiz->title)

@section('content')
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold text-dark">{{ $quiz->title }}</h2>
            <p class="text-muted">Lesson: {{ $quiz->lesson->title }}</p>
        </div>

        <div class="card shadow border-0 rounded-4 mx-auto" style="max-width: 700px;">
            <div class="card-body p-5 text-center">
                <h4 class="fw-semibold text-dark mb-3">Ready to start your quiz?</h4>
                <p class="text-muted mb-4">
                    This quiz contains <strong>{{ $quiz->questions->count() }}</strong> questions.  
                    You’ll receive your score immediately after submission.
                </p>

                <div class="d-flex justify-content-center gap-3">
                    <a href="{{ route('student.lesson.show', $quiz->lesson->id) }}" class="btn btn-outline-secondary rounded-pill px-4 py-2">
                        <i class="bi bi-arrow-left"></i> Back to Lesson
                    </a>

                    <a href="{{ route('student.quiz.show', $quiz->id) }}" class="btn btn-primary rounded-pill px-4 py-2">
                        <i class="bi bi-play-circle me-1"></i> Start Quiz
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
