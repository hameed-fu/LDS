@extends('site.layouts.app')

@section('pageTitle', $virtualClass->name)

@section('content')
    <section id="course-details" class="position-relative py-5 min-vh-100 overflow-hidden"
        style="background: linear-gradient(135deg, #e0f7fa 0%, #f5f9ff 50%, #e3f2fd 100%);">

        <div class="container position-relative" style="z-index: 1;" data-aos="fade-up">

            {{-- Header Section --}}
            <div class="row align-items-center mb-5">
                {{-- Class Image --}}
                <div class="col-lg-5 mb-4 mb-lg-0">
                    <div class="overflow-hidden rounded-4 shadow-lg border border-white border-opacity-50">
                        @if ($virtualClass->image ?? false)
                            <img src="{{ asset('storage/' . $virtualClass->image) }}" alt="{{ $virtualClass->name }}"
                                class="img-fluid w-100"
                                style="object-fit: cover; height: 380px; transition: transform 0.5s ease;">
                        @else
                            <div class="bg-primary text-white d-flex align-items-center justify-content-center"
                                style="height: 380px;">
                                <div class="text-center">
                                    <i class="bi bi-laptop fs-1 mb-3"></i>
                                    <h4 class="mb-0">{{ $virtualClass->name }}</h4>
                                    <p class="mb-0 mt-2">Virtual Class</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Class Info --}}
                <div class="col-lg-7 ps-lg-5">
                    <div class="d-flex align-items-center gap-3 flex-wrap mb-3">
                        <span class="badge fs-6 text-white shadow-sm px-3 py-2"
                            style="background: linear-gradient(90deg, #007bff, #00b4d8);">
                            <i class="bi bi-person-fill me-1"></i>
                            {{ $virtualClass->teacher->name ?? 'Teacher' }}
                        </span>

                        <span class="text-muted small">
                            <i class="bi bi-camera-video me-1 text-primary"></i>
                            {{ $virtualClass->liveSessions->count() }} Live Sessions
                        </span>

                        @if ($virtualClass->schedule)
                            <span class="text-muted small">
                                <i class="bi bi-calendar3 me-1 text-primary"></i>
                                {{ $virtualClass->schedule }}
                            </span>
                        @endif
                    </div>

                    <h2 class="fw-bold text-dark mb-3">{{ $virtualClass->name }}</h2>
                    <p class="text-secondary fs-6 mb-4">{{ $virtualClass->description }}</p>

                    {{-- Progress Bar --}}
                    @if ($progress > 0)
                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <small class="text-muted fw-semibold">Progress</small>
                                <small class="fw-bold text-success">{{ $progress }}%</small>
                            </div>
                            <div class="progress rounded-pill bg-white shadow-sm" style="height: 14px;">
                                <div class="progress-bar progress-bar-striped rounded-pill"
                                    style="width: {{ $progress }}%; background: linear-gradient(90deg, #00c853, #b2ff59);">
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Next Session Info --}}
                    @php
                        $nextSession = $virtualClass->liveSessions
                            ->where('status', 'scheduled')
                            ->where('scheduled_at', '>', now())
                            ->sortBy('scheduled_at')
                            ->first();
                    @endphp

                    @if ($nextSession)
                        <div class="alert alert-info border-0 rounded-4 shadow-sm mb-4" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-clock-fill fs-4 me-3"></i>
                                <div>
                                    <h6 class="alert-heading mb-1">Next Session</h6>
                                    <p class="mb-1"><strong>{{ $nextSession->title }}</strong> starts at
                                        <span class="fw-bold">{{ $nextSession->scheduled_at->format('h:i A') }}</span>
                                    </p>
                                    <small class="text-muted">
                                        <span id="countdown-timer" class="fw-bold text-primary"></span> from now
                                    </small>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Action Buttons --}}
                    <div class="d-flex flex-wrap gap-3 mt-4">
                        @if ($progress < 100 && $virtualClass->liveSessions->count() > 0)
                            <a href="{{ route('student.course.continue', $virtualClass->id) }}"
                                class="btn btn-lg text-white fw-semibold rounded-pill px-4 shadow-sm"
                                style="background: linear-gradient(90deg, #00b09b, #96c93d); box-shadow: 0 4px 15px rgba(0, 176, 155, 0.3);">
                                <i class="bi bi-play-fill me-2"></i> Continue Learning
                            </a>
                        @elseif($virtualClass->liveSessions->count() === 0)
                            <span class="badge fs-6 px-4 py-3 rounded-pill shadow-sm text-white"
                                style="background: linear-gradient(90deg, #ff9500, #ffcc00); box-shadow: 0 4px 12px rgba(255, 149, 0, 0.4);">
                                <i class="bi bi-clock me-1"></i> Sessions Coming Soon
                            </span>
                        @else
                            <span class="badge fs-6 px-4 py-3 rounded-pill shadow-sm text-white"
                                style="background: linear-gradient(90deg, #ff7e5f, #feb47b); box-shadow: 0 4px 12px rgba(255, 126, 95, 0.4);">
                                <i class="bi bi-trophy-fill me-1"></i> Completed 🎉
                            </span>
                        @endif

                        <a href="{{ route('student.my_courses') }}"
                            class="btn btn-outline-dark btn-lg rounded-pill px-4 fw-semibold shadow-sm">
                            <i class="bi bi-arrow-left-circle me-2"></i> Back to My Classes
                        </a>
                    </div>
                </div>
            </div>

            {{-- Divider --}}
            @if ($virtualClass->liveSessions->count() > 0)
                <hr class="my-5" style="border-top: 2px dashed rgba(0,0,0,0.1);">

                {{-- Live Sessions List --}}
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex align-items-center mb-4">
                            <div class="me-2 text-primary fs-4"><i class="bi bi-camera-video-fill"></i></div>
                            <h4 class="fw-bold text-dark mb-0">Live Sessions</h4>
                        </div>

                        <div id="sessions-container" class="list-group rounded-4 shadow-sm border-0 overflow-hidden">
                            @foreach ($virtualClass->liveSessions ?? [] as $session)
                                @php
                                    $isCompleted = App\Models\Attendance::where('student_id', auth()->id())
                                        ->where('session_id', $session->id)
                                        ->where('status', 'present')
                                        ->exists();

                                    $sessionStatus = $session->status;
                                    $sessionDateTime = $session->scheduled_at->timestamp * 1000; // JS timestamp
                                    $isUpcoming = $sessionStatus === 'scheduled' && now()->lt($session->scheduled_at);
                                    $isLive = $sessionStatus === 'ongoing';
                                    $isPast =
                                        $sessionStatus === 'completed' ||
                                        $sessionStatus === 'cancelled' ||
                                        (now()->gt($session->scheduled_at) && $sessionStatus === 'scheduled');
                                @endphp

                                <div id="session-{{ $session->id }}"
                                    class="session-item list-group-item list-group-item-action py-4 px-4 d-flex justify-content-between align-items-center border-0 border-bottom"
                                    data-session-id="{{ $session->id }}" data-status="{{ $sessionStatus }}"
                                    data-scheduled-at="{{ $sessionDateTime }}"
                                    data-meeting-url="{{ $session->meeting_url }}"
                                    data-recording-url="{{ $session->recording_url }}"
                                    style="transition: all 0.3s ease; background: {{ $isCompleted ? 'linear-gradient(90deg, #f0fff4, #e6ffe6)' : '#ffffff' }};
                                    cursor: {{ $isUpcoming ? 'default' : 'pointer' }};">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                                            style="width: 44px; height: 44px;
                                            background: {{ $isCompleted ? 'linear-gradient(90deg, #00c851, #007e33)' : ($isLive ? 'linear-gradient(90deg, #ff0000, #ff6b6b)' : ($isUpcoming ? 'linear-gradient(90deg, #007bff, #00b4d8)' : '#9e9e9e')) }};
                                            color: white; font-weight: bold;">
                                            <i class="bi bi-camera-video"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-semibold text-dark mb-1">{{ $session->title }}</h6>
                                            <p class="small text-muted mb-1">
                                                <i class="bi bi-calendar3 me-1"></i>
                                                {{ $session->scheduled_at->format('M d, Y - h:i A') }}
                                            </p>
                                            @if ($session->description)
                                                <p class="small text-muted mb-0">
                                                    {{ Str::limit($session->description, 80) }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    <div>
                                        @if ($session->quizzes->count())
                                            <div class="mt-5">
                                                <h4 class="fw-semibold mb-3 text-dark">
                                                    <i class="bi bi-question-circle text-warning me-2"></i> Lesson Quizzes
                                                </h4>

                                                @foreach ($session->quizzes as $quiz)
                                                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                                                        <div
                                                            class="card-body d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <h5 class="fw-bold text-dark mb-1">{{ $quiz->title }}</h5>
                                                                <small class="text-muted">
                                                                    {{ $quiz->questions->where('options', '!=', null)->filter(fn($q) => $q->options->count() > 0)->count() }}
                                                                    Questions
                                                                </small>
                                                            </div>
                                                            <a href="{{ route('student.quiz.start', $quiz->id) }}"
                                                                class="btn btn-warning text-dark rounded-pill px-4">
                                                                <i class="bi bi-pencil-square me-1"></i> Attempt Quiz
                                                            </a>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>

                                    <div class="d-flex flex-column align-items-end gap-2">
                                        @if ($isLive)
                                            <span class="badge bg-danger rounded-pill px-3 py-2 shadow-sm live-badge">
                                                <i class="bi bi-broadcast me-1"></i> LIVE NOW
                                            </span>
                                            <div class="d-flex gap-2 mt-1">
                                                @if ($session->id)
                                                    <a href="{{ route('meeting', $session->meeting_code) }}"
                                                        target="_blank"
                                                        class="btn btn-sm btn-danger rounded-pill px-3 join-btn">
                                                        <i class="bi bi-camera-video me-1"></i> Join Now
                                                    </a>
                                                @endif

                                            </div>
                                        @elseif($isUpcoming)
                                            <span class="badge bg-primary rounded-pill px-3 py-2 shadow-sm upcoming-badge">
                                                <i class="bi bi-clock me-1"></i> Upcoming
                                            </span>
                                            <small class="text-muted countdown" data-target="{{ $sessionDateTime }}">
                                                Starts in: <span class="fw-bold">Calculating...</span>
                                            </small>
                                            <div class="d-flex gap-2 mt-1">

                                                <button class="btn btn-sm btn-primary rounded-pill px-3 add-calendar-btn"
                                                    onclick="addToCalendar({{ $session->id }})" title="Add to Calendar">
                                                    <i class="bi bi-calendar-plus me-1"></i> Remind
                                                </button>
                                            </div>
                                        @elseif($isCompleted)
                                            <span
                                                class="badge bg-success rounded-pill px-3 py-2 shadow-sm completed-badge">
                                                <i class="bi bi-check-circle me-1"></i>
                                                {{ $isCompleted ? 'Attended' : 'Completed' }}
                                            </span>
                                            <div class="d-flex gap-2 mt-1">
                                                @if ($session->recording_url)
                                                    <a href="{{ $session->recording_url }}" target="_blank"
                                                        class="btn btn-sm btn-outline-success rounded-pill px-3 recording-btn">
                                                        <i class="bi bi-play-circle me-1"></i> Recording
                                                    </a>
                                                @endif


                                            </div>
                                        @elseif($sessionStatus === 'cancelled')
                                            <span
                                                class="badge bg-secondary rounded-pill px-3 py-2 shadow-sm cancelled-badge">
                                                <i class="bi bi-x-circle me-1"></i> Cancelled
                                            </span>
                                            <button type="button"
                                                class="btn btn-sm btn-outline-secondary rounded-pill px-3 view-details-btn mt-1"
                                                data-bs-toggle="modal"
                                                data-bs-target="#sessionDetailsModal-{{ $session->id }}">
                                                <i class="bi bi-info-circle me-1"></i> Details
                                            </button>
                                        @else
                                            <span class="badge bg-secondary rounded-pill px-3 py-2 shadow-sm missed-badge">
                                                <i class="bi bi-hourglass-split me-1"></i> Missed
                                            </span>
                                            <div class="d-flex gap-2 mt-1">
                                                @if ($session->recording_url)
                                                    <a href="{{ $session->recording_url }}" target="_blank"
                                                        class="btn btn-sm btn-outline-success rounded-pill px-3 recording-btn">
                                                        <i class="bi bi-play-circle me-1"></i> Recording
                                                    </a>
                                                @endif


                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <a href="{{ route('student.session.show', $session->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                            <i class="bi bi-play-circle me-1"></i> View Session
                                        </a>
                                    </div>

                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>

    {{-- Join Session Modal --}}
    <div class="modal fade" id="joinSessionModal" tabindex="-1" aria-labelledby="joinSessionModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="joinSessionModalLabel">Join Live Session</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <div class="text-center mb-4">
                        <div
                            class="bg-danger rounded-circle d-inline-flex align-items-center justify-content-center p-3 mb-3">
                            <i class="bi bi-camera-video text-white fs-3"></i>
                        </div>
                        <h4 id="session-title" class="fw-bold mb-2">Session Title</h4>
                        <p class="text-muted mb-3">The session is now live! Click the button below to join.</p>

                        <div class="alert alert-info rounded-3 mb-4">
                            <i class="bi bi-info-circle me-2"></i>
                            <small>Make sure you have a stable internet connection and your microphone/camera ready.</small>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <a id="join-meeting-link" href="#" target="_blank"
                            class="btn btn-lg btn-danger rounded-pill py-3 fw-bold">
                            <i class="bi bi-camera-video-fill me-2"></i> Join Session Now
                        </a>
                        <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">
                            Not Now
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

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

        .session-live {
            animation: pulse 2s infinite;
            border-left: 4px solid #dc3545 !important;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.4);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(220, 53, 69, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(220, 53, 69, 0);
            }
        }

        .countdown-expired {
            color: #dc3545 !important;
            font-weight: bold !important;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sessions = document.querySelectorAll('.session-item');
            const joinModal = new bootstrap.Modal(document.getElementById('joinSessionModal'));
            const sessionTitle = document.getElementById('session-title');
            const joinMeetingLink = document.getElementById('join-meeting-link');

            // Function to update countdown timers
            function updateCountdowns() {
                const now = Date.now();

                sessions.forEach(session => {
                    const scheduledAt = parseInt(session.dataset.scheduledAt);
                    const status = session.dataset.status;
                    const countdownElement = session.querySelector('.countdown span');
                    const upcomingBadge = session.querySelector('.upcoming-badge');
                    const liveBadge = session.querySelector('.live-badge');
                    const joinBtn = session.querySelector('.join-btn');

                    // If session is scheduled and not yet started
                    if (status === 'scheduled' && scheduledAt > now) {
                        const timeRemaining = scheduledAt - now;

                        if (countdownElement) {
                            if (timeRemaining <= 0) {
                                countdownElement.textContent = 'Session starting...';
                                countdownElement.classList.add('countdown-expired');
                            } else {
                                const hours = Math.floor(timeRemaining / (1000 * 60 * 60));
                                const minutes = Math.floor((timeRemaining % (1000 * 60 * 60)) / (1000 *
                                    60));
                                const seconds = Math.floor((timeRemaining % (1000 * 60)) / 1000);

                                countdownElement.textContent = `${hours}h ${minutes}m ${seconds}s`;

                                // If less than 10 minutes remaining, show warning
                                if (timeRemaining < 10 * 60 * 1000) {
                                    countdownElement.classList.add('countdown-expired');
                                } else {
                                    countdownElement.classList.remove('countdown-expired');
                                }
                            }
                        }

                        // Check if session should start now (within 1 minute)
                        if (timeRemaining <= 0 && timeRemaining > -5 * 60 *
                            1000) { // Within 5 minutes after start time
                            startSession(session);
                        }
                    }
                });
            }

            // Function to start a session (update UI and show modal)
            function startSession(sessionElement) {
                const sessionId = sessionElement.dataset.sessionId;
                const title = sessionElement.querySelector('h6').textContent;
                const meetingUrl = sessionElement.dataset.meetingUrl;
                const recordingUrl = sessionElement.dataset.recordingUrl;

                // Update session status in UI
                sessionElement.dataset.status = 'ongoing';
                sessionElement.classList.add('session-live');

                // Update badges
                const upcomingBadge = sessionElement.querySelector('.upcoming-badge');
                const countdownElement = sessionElement.querySelector('.countdown');

                if (upcomingBadge) {
                    upcomingBadge.innerHTML = '<i class="bi bi-broadcast me-1"></i> LIVE NOW';
                    upcomingBadge.classList.remove('bg-primary');
                    upcomingBadge.classList.add('bg-danger');
                }

                if (countdownElement) {
                    countdownElement.innerHTML = '<span class="fw-bold text-danger">LIVE NOW</span>';
                }

                // Add join button if not already present
                if (meetingUrl && !sessionElement.querySelector('.join-btn')) {
                    const buttonContainer = sessionElement.querySelector('.d-flex.flex-column.align-items-end');
                    const joinBtn = document.createElement('a');
                    joinBtn.href = meetingUrl;
                    joinBtn.target = '_blank';
                    joinBtn.className = 'btn btn-sm btn-danger rounded-pill px-3 join-btn';
                    joinBtn.textContent = 'Join Now';
                    buttonContainer.appendChild(joinBtn);
                }

                // Show join modal
                if (meetingUrl) {
                    sessionTitle.textContent = title;
                    joinMeetingLink.href = meetingUrl;

                    // Auto-show modal for the first live session
                    const isFirstLive = !document.querySelector('.session-live:not(#session-' + sessionId + ')');
                    if (isFirstLive) {
                        setTimeout(() => {
                            joinModal.show();
                        }, 1000);
                    }
                }

                // Send AJAX request to update session status on server
                updateSessionStatus(sessionId, 'ongoing');
            }

            // Function to update session status on server
            function updateSessionStatus(sessionId, status) {
                fetch(`/api/live-sessions/${sessionId}/status`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        },
                        body: JSON.stringify({
                            status: status
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Session status updated:', data);
                    })
                    .catch(error => {
                        console.error('Error updating session status:', error);
                    });
            }

            // Function to mark attendance when joining
            function markAttendance(sessionId) {
                fetch(`/api/attendance`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        },
                        body: JSON.stringify({
                            session_id: sessionId,
                            student_id: {{ auth()->id() }},
                            status: 'present'
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Attendance marked:', data);
                    })
                    .catch(error => {
                        console.error('Error marking attendance:', error);
                    });
            }

            // Set up event listeners for join buttons
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('join-btn') || e.target.closest('.join-btn')) {
                    const sessionElement = e.target.closest('.session-item');
                    const sessionId = sessionElement.dataset.sessionId;

                    // Mark attendance when user clicks join
                    markAttendance(sessionId);

                    // Update UI to show attended
                    const completedBadge = sessionElement.querySelector('.completed-badge');
                    if (!completedBadge) {
                        const badgeContainer = sessionElement.querySelector(
                            '.d-flex.flex-column.align-items-end');
                        const badge = document.createElement('span');
                        badge.className =
                            'badge bg-success rounded-pill px-3 py-2 shadow-sm completed-badge';
                        badge.innerHTML = '<i class="bi bi-check-circle me-1"></i> Attended';
                        badgeContainer.appendChild(badge);
                    }
                }
            });

            // Update countdowns every second
            setInterval(updateCountdowns, 1000);
            updateCountdowns(); // Initial call

            // Check for sessions that should be live on page load
            sessions.forEach(session => {
                const scheduledAt = parseInt(session.dataset.scheduledAt);
                const status = session.dataset.status;
                const now = Date.now();

                // If session is scheduled and should have started (within last 2 hours)
                if (status === 'scheduled' && scheduledAt <= now && scheduledAt > now - 2 * 60 * 60 *
                    1000) {
                    startSession(session);
                }
            });

            // Next session countdown timer
            const nextSessionElement = document.getElementById('countdown-timer');
            if (nextSessionElement && {{ $nextSession ? $nextSession->scheduled_at->timestamp * 1000 : 'null' }}) {
                const nextSessionTime = {{ $nextSession ? $nextSession->scheduled_at->timestamp * 1000 : 'null' }};

                function updateNextSessionTimer() {
                    const now = Date.now();
                    const timeRemaining = nextSessionTime - now;

                    if (timeRemaining <= 0) {
                        nextSessionElement.textContent = 'Starting now!';
                        nextSessionElement.classList.add('countdown-expired');
                        location.reload(); // Reload to update session status
                    } else {
                        const hours = Math.floor(timeRemaining / (1000 * 60 * 60));
                        const minutes = Math.floor((timeRemaining % (1000 * 60 * 60)) / (1000 * 60));
                        const seconds = Math.floor((timeRemaining % (1000 * 60)) / 1000);

                        nextSessionElement.textContent = `${hours}h ${minutes}m ${seconds}s`;

                        if (timeRemaining < 10 * 60 * 1000) {
                            nextSessionElement.classList.add('countdown-expired');
                        }
                    }
                }

                setInterval(updateNextSessionTimer, 1000);
                updateNextSessionTimer();
            }
        });
    </script>
@endsection
