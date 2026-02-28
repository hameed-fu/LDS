<div class="container" data-aos="fade-up" data-aos-delay="100">
    <div class="row gy-4">

        @foreach ($classes as $class)
        <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
            <div class="course-card">
                 
                <div class="course-content">
                    <div class="course-meta">
                        <span class="level text-capitalize">{{ $class->level }}</span>
                         
                    </div>
                    <h3><a href="{{ route('class.detail', $class->id) }}">{{ $class->name }}
                                                </a></h3>
                    <p>{{ $class->description }}</p>
                     
                    <div class="course-stats">
                        <div class="rating">
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-half"></i>
                            <span>(4.5)</span>
                        </div>
                        <div class="students">
                            <i class="bi bi-people-fill"></i>
                            <span>{{ App\Models\Enrollment::where('class_id', $class->id)->count() }} students</span>
                        </div>
                    </div>
                    <a href="/enroll" class="btn-course">Enroll Now</a>
                </div>
            </div>
        </div>
        @endforeach




    </div>

    <div class="more-courses text-center" data-aos="fade-up" data-aos-delay="500">
        <a href="/courses" class="btn-more">View All Courses</a>
    </div>

</div>
