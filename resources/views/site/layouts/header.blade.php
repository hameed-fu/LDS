<header id="header" class="header d-flex align-items-center sticky-top">
    <div class="container-fluid container-xl position-relative d-flex align-items-center">

        <a href="/" class="logo d-flex align-items-center me-auto">
            <h1 class="sitename">LDS</h1>
        </a>

        <nav id="navmenu" class="navmenu">
            <ul>
                <li>
                    <a href="{{ url('/') }}" class="{{ request()->is('/') ? 'active' : '' }}">Home</a>
                </li>
                <li>
                    <a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'active' : '' }}">About</a>
                </li>
                <li>
                    <a href="{{ route('site.courses') }}"
                        class="{{ request()->routeIs('site.courses') ? 'active' : '' }}">Courses</a>
                </li>
                <li>
                    <a href="{{ route('contact') }}"
                        class="{{ request()->routeIs('contact') ? 'active' : '' }}">Contact</a>
                </li>
            </ul>
            <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
        </nav>

        {{-- Auth Buttons --}}
        @guest
            <div class="d-flex gap-2 align-items-center">
                {{-- Enroll Now (redirects guest to login) --}}
                <a href="{{ route('login') }}" class="btn-getstarted">Enroll Now</a>

                {{-- Login/Register buttons --}}
                <div class="d-flex gap-2">
                    <a href="{{ route('login') }}" class="btn-getstarted">Login</a>
                    <a href="{{ route('register') }}" class="btn-getstarted bg-success btn-outline-success">Register</a>
                </div>
            </div>
        @else
            <div class="d-flex gap-2 align-items-center">
                {{-- Enroll for logged-in users --}}
                <a class="btn-getstarted" href="{{ route('enroll') }}">Enroll Now</a>

                {{-- User dropdown --}}
                <div class="dropdown">
                    <a class="btn-getstarted dropdown-toggle bg-secondary text-white" href="#" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        {{ Auth::user()->name }}
                    </a>

                    <ul class="dropdown-menu dropdown-menu-end">
                        {{-- Dashboard --}}
                        <li>
                            <a class="dropdown-item" href="{{ route('student.dashboard') }}">
                                <i class="bi bi-speedometer2 me-1"></i> Dashboard
                            </a>
                        </li>

                        {{-- My Courses --}}
                        <li>
                            <a class="dropdown-item" href="{{ route('student.my_courses') }}">
                                <i class="bi bi-journal-text me-1"></i> My Courses
                            </a>
                        </li>
 <li>
                            <a class="dropdown-item" href="{{ route('student.my_quiz_attempts') }}">
                                <i class="bi bi-question me-1"></i> My Quizes
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        {{-- Logout --}}
                        <li>
                            <form method="get" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-right me-1"></i> Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        @endguest


    </div>
</header>
