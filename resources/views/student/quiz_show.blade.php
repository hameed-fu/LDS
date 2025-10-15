@extends('site.layouts.app')

@section('pageTitle', $quiz->title)

@section('content')
<section class="py-5 min-vh-100 position-relative" 
    style="background: linear-gradient(135deg, #f0f7ff 0%, #fdfbfb 50%, #e8f5e9 100%);">

    <div class="container position-relative" style="z-index: 1;" data-aos="fade-up">
        {{-- Quiz Header --}}
        <div class="text-center mb-5">
            <h2 class="fw-bold text-dark mb-2">{{ $quiz->title }}</h2>
            <p class="text-muted fs-6">Answer all questions below and submit your responses to see your score.</p>
            <hr class="mx-auto mt-4" style="width: 80px; border-top: 3px solid #00b4d8;">
        </div>

        {{-- Previous Attempts --}}
        @if ($attempts->count() > 0)
            <div class="card border-0 shadow-lg rounded-4 mb-5">
                <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
                    <h5 class="fw-semibold text-dark mb-0">
                        <i class="bi bi-clock-history me-2 text-primary"></i>Your Previous Attempts
                    </h5>
                    <span class="badge bg-light text-dark fw-semibold px-3 py-2 shadow-sm">
                        Total: {{ $attempts->count() }}
                    </span>
                </div>

                <div class="card-body px-0 pt-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="fw-semibold ps-4">#</th>
                                    <th class="fw-semibold">Score</th>
                                    <th class="fw-semibold">Attempted On</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($attempts as $index => $attempt)
                                    <tr class="border-bottom">
                                        <td class="ps-4">{{   $index + 1 }}</td>
                                        <td class="fw-bold text-success">
                                            {{ $attempt->score }}%
                                        </td>
                                        <td class="text-muted">
                                            {{ \Carbon\Carbon::parse($attempt->attempted_at)->format('d M, Y h:i A') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        {{-- Quiz Form --}}
        <form method="POST" action="{{ route('student.quiz.attempt', $quiz->id) }}" class="mb-5">
            @csrf
            <div class="card shadow-lg border-0 rounded-4 p-4">
                @foreach ($quiz->questions as $index => $question)
                    <div class="mb-5 pb-4 border-bottom">
                        <h5 class="fw-semibold text-dark mb-3">
                            <span class="badge bg-primary-subtle text-primary me-2 px-3 py-2 rounded-pill shadow-sm">
                                Q{{ $index + 1 }}
                            </span>
                            {{ $question->question_text }}
                        </h5>

                        <div class="ps-3 mt-3">
                            @foreach ($question->options as $option)
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio"
                                           name="question_{{ $question->id }}"
                                           id="opt{{ $option->id }}"
                                           value="{{ $option->id }}">
                                    <label class="form-check-label" for="opt{{ $option->id }}">
                                        {{ $option->option_text }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <div class="d-flex justify-content-between align-items-center mt-4">
                    <span class="text-muted small">
                        <i class="bi bi-info-circle me-1 text-primary"></i> You can retake this quiz anytime.
                    </span>
                    <button type="submit" 
                        class="btn btn-lg text-white fw-semibold rounded-pill px-4 py-2 shadow-sm"
                        style="background: linear-gradient(90deg, #00b09b, #96c93d); box-shadow: 0 4px 15px rgba(0,176,155,0.3);">
                        <i class="bi bi-check-circle me-2"></i> Submit Quiz
                    </button>
                </div>
            </div>
        </form>

        {{-- Back Button --}}
        <div class="text-center mt-4">
            <a href="{{ route('student.my_courses') }}" 
               class="btn btn-outline-dark rounded-pill px-4 fw-semibold shadow-sm">
                <i class="bi bi-arrow-left-circle me-2"></i> Back to My Courses
            </a>
        </div>
    </div>
</section>

{{-- Style Enhancements --}}
<style>
    .form-check-input:checked {
        background-color: #00b09b;
        border-color: #00b09b;
    }

    .form-check-label {
        cursor: pointer;
        transition: color 0.3s ease;
    }

    .form-check-label:hover {
        color: #007bff;
    }

    tr:hover td {
        background-color: #f8f9fa;
        transition: background-color 0.3s ease;
    }

    h5 span.badge {
        font-size: 0.9rem;
        background: linear-gradient(90deg, #007bff1a, #00b4d81a);
    }
</style>
@endsection
