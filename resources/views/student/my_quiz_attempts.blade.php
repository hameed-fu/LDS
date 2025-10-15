@extends('site.layouts.app')

@section('pageTitle', 'My Quiz Attempts')

@section('content')
<section id="quiz-attempts" class="section light-background py-5">
    <div class="container section-title mb-5" data-aos="fade-up">
        <h2 class="text-3xl font-bold text-gray-900">My Quiz Attempts</h2>
        <p class="text-gray-600">Review your quiz performance and progress</p>
    </div>

    <div class="container" data-aos="fade-up" data-aos-delay="100">
        @if ($quizAttempts->count() > 0)
            <div class="table-responsive shadow-sm rounded-3">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Quiz Title</th>
                            <th>Score</th>
                            <th>Status</th>
                            <th>Attempted On</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($quizAttempts as $index => $attempt)
                            @php
                                $quiz = $attempt->quiz;
                                $passed = $attempt->score >= 50;
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="fw-semibold">{{ $quiz->title }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1" style="height: 6px;">
                                            <div class="progress-bar {{ $passed ? 'bg-success' : 'bg-danger' }}" 
                                                role="progressbar" style="width: {{ $attempt->score }}%;">
                                            </div>
                                        </div>
                                        <span class="ms-2 small text-muted">{{ $attempt->score }}%</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge {{ $passed ? 'bg-success' : 'bg-danger' }}">
                                        {{ $passed ? 'Passed' : 'Failed' }}
                                    </span>
                                </td>
                                <td>
                                    {{ \Carbon\Carbon::parse($attempt->attempted_at)->format('M d, Y h:i A') }}
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('student.quiz.show', $quiz->id) }}" class="btn btn-sm btn-outline-primary">
                                        View
                                    </a>
                                    <a href="{{ route('student.quiz.start', $quiz->id) }}" class="btn btn-sm btn-success ms-2">
                                        Retake
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-5">
                <div class="alert alert-info shadow-sm d-inline-block px-4 py-3 rounded-3">
                    You haven’t attempted any quizzes yet.
                    <a href="{{ route('student.my_courses') }}" class="fw-semibold text-decoration-none">Start Learning</a>
                </div>
            </div>
        @endif
    </div>
</section>
@endsection
