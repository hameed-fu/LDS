@extends('site.layouts.app')

@section('pageTitle', 'Courses')
@section('content')
    <section id="courses-2" class="courses-2 section">
        <div class="container section-title" data-aos="fade-up">
            <h2>Featured Courses</h2>
            <p>Necessitatibus eius consequatur ex aliquid fuga eum quidem sint consectetur velit</p>
        </div>
        <div class="container" data-aos="fade-up" data-aos-delay="100">

            <div class="row">

                <div class="col-lg-12">


                    <div class="courses-grid" data-aos="fade-up" data-aos-delay="200">
                        <div class="row">
                            @foreach ($courses as $course)
                                <div class="col-lg-3 col-md-6">
                                    <div class="course-card">


                                        <div class="course-image">
                                            <a href="{{ route('course.detail', $course->id) }}">
                                                <img src="{{ asset('storage/' . $course->image) }}" alt="Course"
                                                    class="img-fluid">
                                            </a>

                                        </div>
                                        <div class="course-content">
                                            <div class="course-meta">
                                                <span class="level">{{ $course->level }}</span>
                                            </div>
                                            <h3>
                                                <a href="{{ route('course.detail', $course->id) }}">{{ $course->title }}
                                                </a>
                                            </h3>
                                            <p>{{ $course->description }}</p>
                                            <div class="course-stats">

                                                <div class="stat">
                                                    <i class="bi bi-people"></i>
                                                    <span>1,245 students</span>
                                                </div>
                                                <div class="rating">
                                                    <i class="bi bi-star-fill"></i>
                                                    <i class="bi bi-star-fill"></i>
                                                    <i class="bi bi-star-fill"></i>
                                                    <i class="bi bi-star-fill"></i>
                                                    <i class="bi bi-star-half"></i>
                                                    <span>4.8 (89 reviews)</span>
                                                </div>
                                            </div>


                                            <a href="{{ route('enroll', $course->id) }}" class="btn-course">Enroll Now</a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach


                        </div>
                    </div>
                </div>
            </div>

        </div>

    </section>



@endsection
