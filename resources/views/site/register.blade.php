@extends('site.layouts.app')
@section('pageTitle', 'Register')
@section('content')

<section id="enroll" class="enroll section">
    <div class="container" data-aos="fade-up" data-aos-delay="100">
        <div class="enrollment-form-wrapper">
            <div class="row justify-content-center">
                <div class="col-lg-10 col-xl-8">

                    <form class="enrollment-form" data-aos="fade-up" data-aos-delay="300" method="POST"
                        action="{{ route('register.store') }}">
                        @csrf

                        <h2 class="text-center mb-4 fw-bold">Create Your Student Account</h2>

                        {{-- Name --}}
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="name" class="form-label">Full Name *</label>
                                    <input type="text" id="name" name="name" value="{{ old('name') }}"
                                        class="form-control" required>
                                    @error('name')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Email + Username --}}
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email" class="form-label">Email Address *</label>
                                    <input type="email" id="email" name="email" value="{{ old('email') }}"
                                        class="form-control" required>
                                    @error('email')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="username" class="form-label">Username *</label>
                                    <input type="text" id="username" name="username" value="{{ old('username') }}"
                                        class="form-control" required>
                                    @error('username')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Passwords --}}
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password" class="form-label">Password *</label>
                                    <input type="password" id="password" name="password" class="form-control" required>
                                    @error('password')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password_confirmation" class="form-label">Confirm Password *</label>
                                    <input type="password" id="password_confirmation" name="password_confirmation"
                                        class="form-control" required>
                                </div>
                            </div>
                        </div>

                        {{-- Submit --}}
                        <div class="row">
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-enroll px-5 py-2">
                                    <i class="bi bi-check-circle me-2"></i>
                                    Register Now
                                </button>
                                <p class="enrollment-note mt-3 text-muted">
                                    <i class="bi bi-shield-check"></i>
                                    Your information is secure and will never be shared
                                </p>
                            </div>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
</section>

@endsection
