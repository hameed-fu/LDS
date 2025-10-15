@extends('site.layouts.app')

@section('pageTitle', 'Course detail')
@section('content')
    <!-- Page Title -->
    <div class="page-title light-background">
        <div class="container d-lg-flex justify-content-between align-items-center">
            <h1 class="mb-2 mb-lg-0">Course Details</h1>
            <nav class="breadcrumbs">
                <ol>
                    <li><a href="index.html">Home</a></li>
                    <li class="current">Course Details</li>
                </ol>
            </nav>
        </div>
    </div><!-- End Page Title -->

    <!-- Course Details Section -->
    <section id="course-details" class="course-details section">

        <div class="container" data-aos="fade-up" data-aos-delay="100">

            <div class="row">
                <div class="col-lg-8">

                    <!-- Course Hero -->
                    <div class="course-hero" data-aos="fade-up" data-aos-delay="200">
                        <div class="hero-content">
                            <div class="course-badge">
                                <span class="category">{{ $course->level }}</span>
                                {{-- <span class="level">{{ $course->level }}</span> --}}
                            </div>
                            <h1>{{ $course->title }}</h1>
                            <p class="course-subtitle"> </p>


                        </div>
                        <div class="hero-image">
                            <img src="{{ asset('storage/' . $course->image) }}" alt="Course Preview" class="img-fluid">

                        </div>
                    </div><!-- End Course Hero -->

                    <!-- Course Navigation Tabs -->
                    <div class="course-nav-tabs" data-aos="fade-up" data-aos-delay="300">
                        <ul class="nav nav-tabs" id="course-detailsCourseTab" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active" id="course-detailsoverview-tab" data-bs-toggle="tab"
                                    data-bs-target="#course-detailsoverview" type="button" role="tab">
                                    <i class="bi bi-layout-text-window-reverse"></i>
                                    Overview
                                </button>
                            </li>

                        </ul>

                        <div class="tab-content" id="course-detailsCourseTabContent">

                            <!-- Overview Tab -->
                            <div class="tab-pane fade show active" id="course-detailsoverview" role="tabpanel">

                                <div class="overview-section">
                                    {{ $course->description }}
                                </div>





                            </div> 



                        </div>
                    </div>

                </div>

                <div class="col-lg-4">

                    <!-- Enrollment Card -->
                    <div class="enrollment-card" data-aos="fade-up" data-aos-delay="200">

                        <div class="card-header">
                            <div class="price-display">
                                <span class="current-price">$149</span>
                                <span class="original-price">$249</span>
                                <span class="discount">40% OFF</span>
                            </div>
                            <div class="enrollment-count">
                                <i class="bi bi-people"></i>
                                <span>3,892 students enrolled</span>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="course-highlights">
                                <div class="highlight-item">
                                    <i class="bi bi-trophy"></i>
                                    <span>Certificate included</span>
                                </div>
                                <div class="highlight-item">
                                    <i class="bi bi-clock-history"></i>
                                    <span>45 hours content</span>
                                </div>
                                <div class="highlight-item">
                                    <i class="bi bi-download"></i>
                                    <span>Downloadable resources</span>
                                </div>
                                <div class="highlight-item">
                                    <i class="bi bi-infinity"></i>
                                    <span>Lifetime access</span>
                                </div>
                                <div class="highlight-item">
                                    <i class="bi bi-phone"></i>
                                    <span>Mobile access</span>
                                </div>
                            </div>

                            <div class="action-buttons">
                                <a href="{{ route('enroll', $course->id) }}" > 

                                    <button class="btn-primary">Enroll Now</button>

                                </a>
                             </div>

                            <div class="guarantee">
                                <i class="bi bi-shield-check"></i>
                                <span>30-day money-back guarantee</span>
                            </div>
                        </div>

                    </div><!-- End Enrollment Card -->




                </div>

            </div>

        </div>

    </section><!-- /Course Details Section -->

@endsection
