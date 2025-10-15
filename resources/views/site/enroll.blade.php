@extends('site.layouts.app')

@section('pageTitle', 'Enroll')
@section('content')
    <!-- Courses Hero Section -->
    <section id="enroll" class="enroll section">

        <div class="container" data-aos="fade-up" data-aos-delay="100">

            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="enrollment-form-wrapper">

                        <div class="enrollment-header text-center mb-5" data-aos="fade-up" data-aos-delay="200">
                            <h2>Enroll in Your Dream Course</h2>
                            <p>Take the next step in your educational journey. Complete the form below to secure your spot
                                in our comprehensive online learning program.</p>

                            @if (session('success'))
                                <div class="alert alert-success text-center">
                                    {{ session('success') }}
                                </div>
                            @endif

                            @if (session('error'))
                                <div class="alert alert-danger text-center">
                                    {{ session('error') }}
                                </div>
                            @endif
                        </div>

                        <form class="enrollment-form" action="{{ route('enroll.save') }}" method="POST" data-aos="fade-up"
                            data-aos-delay="300">
                            @csrf
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="firstName" class="form-label">Full Name *</label>
                                        <input type="text" readonly id="firstName" value="{{ auth()->user()->name }}"
                                            name="firstName" class="form-control" required="" autocomplete="given-name">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email" class="form-label">Email Address *</label>
                                        <input type="email" readonly id="email" value="{{ auth()->user()->email }}"
                                            name="email" class="form-control" required="" autocomplete="email">
                                    </div>
                                </div>
                            </div>



                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label for="course" class="form-label">Select Course *</label>
                                        {{-- Course Selection --}}
                                        <div class="form-group mb-4">
                                            <label for="course_id" class="form-label fw-semibold">Select Course *</label>
                                            <select name="course_id" id="course_id" class="form-control" required>
                                                <option value="">-- Choose a Course --</option>
                                                @foreach ($courses as $course)
                                                    <option {{ $course->id == request()->course_id ? 'selected' : '' }}
                                                        value="{{ $course->id }}">{{ $course->title }}</option>
                                                @endforeach
                                            </select>
                                            @error('course_id')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>

                                    </div>
                                </div>
                            </div>







                            <div class="row">
                                <div class="col-12 text-center">
                                    <button type="submit" class="btn btn-enroll">
                                        <i class="bi bi-check-circle me-2"></i>
                                        Enroll Now
                                    </button>
                                    <p class="enrollment-note mt-3">
                                        <i class="bi bi-shield-check"></i>
                                        Your information is secure and will never be shared with third parties
                                    </p>
                                </div>
                            </div>

                        </form>

                    </div>
                </div><!-- End Form Column -->

                <div class="col-lg-4 d-none d-lg-block">
                    <div class="enrollment-benefits" data-aos="fade-left" data-aos-delay="400">
                        <h3>Why Choose Our Courses?</h3>
                        <div class="benefit-item">
                            <div class="benefit-icon">
                                <i class="bi bi-trophy"></i>
                            </div>
                            <div class="benefit-content">
                                <h4>Expert Instructors</h4>
                                <p>Learn from industry professionals with years of real-world experience</p>
                            </div>
                        </div><!-- End Benefit Item -->

                        <div class="benefit-item">
                            <div class="benefit-icon">
                                <i class="bi bi-clock"></i>
                            </div>
                            <div class="benefit-content">
                                <h4>Flexible Learning</h4>
                                <p>Study at your own pace with 24/7 access to course materials</p>
                            </div>
                        </div><!-- End Benefit Item -->

                        <div class="benefit-item">
                            <div class="benefit-icon">
                                <i class="bi bi-award"></i>
                            </div>
                            <div class="benefit-content">
                                <h4>Certification</h4>
                                <p>Earn industry-recognized certificates upon course completion</p>
                            </div>
                        </div><!-- End Benefit Item -->

                        <div class="benefit-item">
                            <div class="benefit-icon">
                                <i class="bi bi-people"></i>
                            </div>
                            <div class="benefit-content">
                                <h4>Community Support</h4>
                                <p>Connect with fellow students and get help when you need it</p>
                            </div>
                        </div><!-- End Benefit Item -->

                        <div class="enrollment-stats mt-4">
                            <div class="stat-item">
                                <span class="stat-number">15,000+</span>
                                <span class="stat-label">Students Enrolled</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number">98%</span>
                                <span class="stat-label">Completion Rate</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number">4.9/5</span>
                                <span class="stat-label">Average Rating</span>
                            </div>
                        </div><!-- End Stats -->

                    </div>
                </div><!-- End Benefits Column -->

            </div>

        </div>

    </section><!-- /Enroll Section -->


@endsection
