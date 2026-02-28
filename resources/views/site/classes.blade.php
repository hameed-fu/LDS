@extends('site.layouts.app')

@section('pageTitle', 'Courses')
@section('content')
    <section id="courses-2" class="courses-2 section">
        <div class="container section-title" data-aos="fade-up">
            <h2>Virtual Classes</h2>
            <p>Necessitatibus eius consequatur ex aliquid fuga eum quidem sint consectetur velit</p>
        </div>
        <div class="container" data-aos="fade-up" data-aos-delay="100">

            <div class="row">

                <div class="col-lg-12">


                    <div class="courses-grid" data-aos="fade-up" data-aos-delay="200">
                        <div class="row">
                            @foreach ($classes as $class)
                                <div class="col-lg-4 col-md-6">
                                    <div class="course-card">


                                         
                                        <div class="course-content">
                                            <div class="course-meta">
                                                <span class="level">{{ $class->level }}</span>
                                            </div>
                                            <h3>
                                                <a href="{{ route('class.detail', $class->id) }}">{{ $class->name }}
                                                </a>
                                            </h3>
                                            <p>{{ $class->description }}</p>
                                            <div class="course-stats">

                                                <div class="stat">
                                                    <i class="bi bi-people"></i>
                                                    <span>{{ App\Models\Enrollment::where('class_id', $class->id)->count() }} students</span>
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


                                            <a href="{{ route('enroll', $class->id) }}" class="btn-course">Enroll Now</a>
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
