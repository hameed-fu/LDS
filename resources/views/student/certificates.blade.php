@extends('site.layouts.app')

@section('pageTitle', 'My Certificates')

@section('content')
<section>
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="text-light fw-bold">My Certificates</h2>
            <p class="text-secondary">Here are the certificates for courses you've completed.</p>
        </div>

        <table class="table table-dark table-striped table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Course</th>
                    <th>Lessons Completed</th>
                    <th>Certificate</th>
                </tr>
            </thead>
            <tbody>
                @forelse($completedCourses as $index => $course)
                    @php
                        $certificate = $certificates->firstWhere('course_id', $course->id);
                        $totalLessons = $course->lessons->count();
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $course->title }}</td>
                        <td>{{ $totalLessons }}/{{ $totalLessons }}</td>
                        <td>
                            @if($totalLessons === $course->lessons->count())
                                <a href="{{ route('student.downloadCertificate', $course->id) }}"
                                    target="_blank"
                                   class="btn btn-gradient btn-sm text-white">
                                    <i class="bi bi-download me-1"></i>
                                    {{ $certificate ? 'Download Certificate' : 'Generate & Download' }}
                                </a>
                            @else
                                <span class="text-warning">Complete all lessons to generate</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-light">No completed courses yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <style>
        .btn-gradient {
            background: linear-gradient(135deg, #3b82f6, #06b6d4);
            border: none;
            transition: 0.3s;
        }
        .btn-gradient:hover {
            transform: scale(1.05);
            box-shadow: 0 0 15px rgba(6,182,212,0.6);
        }
    </style>
</section>
@endsection
