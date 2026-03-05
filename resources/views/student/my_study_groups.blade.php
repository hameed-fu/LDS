@extends('site.layouts.app')

@section('pageTitle', 'My Study Groups')

@section('content')
<section class="py-5 bg-light" style="min-height:100vh;">
    <div class="container">

        <div class="mb-4">
            <h3 class="fw-bold">My Study Groups</h3>
        </div>

        @forelse($groups as $group)

            <div class="card shadow-sm border-0 rounded-4 mb-3">
                <div class="card-body d-flex justify-content-between align-items-center">

                    <div>
                        <h5 class="mb-1 fw-bold">
                            {{ $group->name }}
                        </h5>

                        <span class="badge bg-success">
                            {{ $group->members_count }} Members
                        </span>
                    </div>

                    <a href="{{ route('student.groups.show', $group) }}"
                       class="btn btn-primary btn-sm rounded-pill px-3">
                        Open Chat
                    </a>

                </div>
            </div>

        @empty

            <div class="card shadow-sm border-0 rounded-4 text-center py-5">
                <h5 class="text-muted">You have not joined any groups yet.</h5>
                <a href="{{ route('student.groups.index') }}" class="btn btn-outline-primary mt-3 rounded-pill px-4">
                    Browse Groups
                </a>
            </div>

        @endforelse

    </div>
</section>
@endsection