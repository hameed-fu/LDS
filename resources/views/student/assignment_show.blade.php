@extends('site.layouts.app')

@section('pageTitle', $assignment->title)

@section('content')
<section class="py-5" style="min-height:100vh; background:#f8fafc;">
    <div class="container">

        <div class="card shadow-lg border-0 rounded-4">
            <div class="card-body p-5">

                <h3 class="fw-bold mb-2">
                    {{ $assignment->title }}
                </h3>

                <p class="text-muted">
                    Due: {{ $assignment->due_date?->format('M d, Y h:i A') ?? 'No deadline' }}
                </p>

                <hr>

                <div class="mb-4">
                    {!! $assignment->description !!}
                </div>

                @php
                    $submission = $assignment->submissions
                        ->where('student_id', auth()->id())
                        ->first();
                @endphp

                {{-- SUCCESS MESSAGE --}}
                @if(session('success'))
                    <div class="alert alert-success rounded-3">
                        {{ session('success') }}
                    </div>
                @endif

                {{-- ERROR MESSAGE --}}
                @if(session('error'))
                    <div class="alert alert-danger rounded-3">
                        {{ session('error') }}
                    </div>
                @endif

                {{-- Status Badge --}}
                @if($submission)
                    <div class="mb-3">
                        <span class="badge 
                            @if($submission->status == 'graded') bg-success
                            @elseif($submission->status == 'late') bg-danger
                            @else bg-warning text-dark
                            @endif">
                            {{ ucfirst($submission->status) }}
                        </span>
                    </div>
                @endif

                {{-- 🚫 BLOCK FORM IF ALREADY SUBMITTED --}}
                @if(!$submission)

                    <form method="POST"
                          action="{{ route('student.assignment.submit', $assignment->id) }}"
                          enctype="multipart/form-data">
                        @csrf

                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                Upload Assignment
                            </label>

                            <input type="file"
                                   name="file"
                                   class="form-control rounded-3"
                                   required>
                        </div>

                        <button class="btn btn-primary rounded-pill px-4">
                            Submit Assignment
                        </button>
                    </form>

                @else

                    {{-- 🚫 Show error if already submitted --}}
                    <div class="alert alert-danger rounded-4 mt-3">
                        You have already submitted this assignment.
                    </div>

                @endif

                {{-- Submission Info --}}
                @if($submission)
                    <div class="alert alert-light border mt-4 rounded-4">

                        <p class="mb-1">
                            <strong>Submitted:</strong>
                            {{ $submission->submitted_at?->format('M d, Y h:i A') }}
                        </p>

                        @if($submission->score !== null)
                            <p class="mb-1">
                                <strong>Score:</strong>
                                {{ $submission->score }}
                            </p>
                        @endif

                        @if($submission->feedback)
                            <p class="mb-0">
                                <strong>Feedback:</strong>
                                {{ $submission->feedback }}
                            </p>
                        @endif

                        @if($submission->file_path)
                            <div class="mt-3">
                                <a href="{{ asset('storage/' . $submission->file_path) }}"
                                   target="_blank"
                                   class="btn btn-sm btn-outline-primary rounded-pill">
                                    View Submitted File
                                </a>
                            </div>
                        @endif

                    </div>
                @endif

            </div>
        </div>

    </div>
</section>
@endsection