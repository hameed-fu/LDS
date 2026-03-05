@extends('site.layouts.app')

@section('pageTitle', 'Study Groups')

@section('content')
<section class="py-5 bg-light" style="min-height:100vh;">
    <div class="container">

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold">📚 Study Groups</h3>

            <a href="#" class="btn btn-primary rounded-pill px-4"
               data-bs-toggle="modal"
               data-bs-target="#createGroupModal">
                + Create Group
            </a>
        </div>

        {{-- Success / Error --}}
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- Groups List --}}
        <div class="row">
            @forelse($groups as $group)

                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm border-0 rounded-4 h-100">

                        <div class="card-body d-flex flex-column">

                            <h5 class="fw-bold mb-2">
                                {{ $group->name }}
                            </h5>

                            <p class="text-muted small flex-grow-1">
                                {{ $group->description ?? 'No description provided.' }}
                            </p>

                            <div class="d-flex justify-content-between align-items-center mt-3">

                                <span class="badge bg-secondary">
                                    {{ $group->members_count }}
                                    /
                                    {{ $group->max_members }} Members
                                </span>

                                @if($group->members->contains(auth()->id()))
                                    <a href="{{ route('student.groups.show', $group) }}"
                                       class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                        Open
                                    </a>
                                @else
                                    <form action="{{ route('student.groups.join', $group) }}"
                                          method="POST">
                                        @csrf
                                        <button class="btn btn-success btn-sm rounded-pill px-3">
                                            Join
                                        </button>
                                    </form>
                                @endif

                            </div>

                        </div>
                    </div>
                </div>

            @empty
                <div class="text-center">
                    <p class="text-muted">No study groups available.</p>
                </div>
            @endforelse
        </div>

    </div>
</section>

@include('student.partials.create_group_modal')

@endsection