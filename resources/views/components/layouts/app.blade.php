<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title . ' - ' . config('app.name') : config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('css')
</head>

<body class="font-sans antialiased bg-base-200/50 dark:bg-base-200">

    {{-- Navbar --}}
    <x-nav sticky full-width>
        <x-slot:brand>
            {{-- Drawer toggle (visible on mobile) --}}
            <label for="main-drawer" class="lg:hidden mr-3">
                <x-icon name="o-bars-3" class="cursor-pointer" />
            </label>
            {{-- Brand --}}
            <x-app-brand />
        </x-slot:brand>

        <x-slot:actions>
            <x-button label="Messages" icon="o-envelope" link="###" class="btn-ghost btn-sm" responsive />
            <x-button label="Notifications" icon="o-bell" link="###" class="btn-ghost btn-sm" responsive />
        </x-slot:actions>
    </x-nav>

    {{-- Main Content with sidebar/drawer --}}
    <x-main with-nav full-width>
        <x-slot:sidebar drawer="main-drawer" collapsible class="bg-base-200">

            {{-- User Info --}}
            @if($user = auth()->user())
                <x-list-item :item="$user" value="name" sub-value="email" no-separator no-hover class="pt-2">
                    <x-slot:actions>
                        <x-button icon="o-power" class="btn-circle btn-ghost btn-xs"
                                  tooltip-left="Log out" no-wire-navigate link="/logout" />
                    </x-slot:actions>
                </x-list-item>
                <x-menu-separator />
            @endif

            {{-- Sidebar Menu --}}
            <x-menu activate-by-route>

                  <x-menu-item title="Dashboard" icon="o-user" link="{{ route('dashboard') }}" />
                {{-- User Management --}}
                <x-menu-sub title="User Management" icon="o-user">
                    <x-menu-item title="Users" icon="o-user" link="{{ route('user.index') }}" />
                    <x-menu-item title="Enrollments" icon="o-user" link="{{ route('enrollments.index') }}" />
                    <x-menu-item title="Certificates" icon="o-user" link="{{ route('certificates.index') }}" />
                </x-menu-sub>

                <x-menu-separator />

                {{-- Course Management --}}
                <x-menu-sub title="Courses" icon="o-book-open">
                    <x-menu-item title="Courses" icon="o-book-open" link="{{ route('course.index') }}" />
                    <x-menu-item title="Lessons" icon="o-clipboard-document" link="{{ route('lessons.index') }}" />
                    <x-menu-item title="Exercises" icon="o-academic-cap" link="{{ route('exercises.index') }}" />
                </x-menu-sub>

                <x-menu-separator />

                {{-- Quiz Management --}}
                <x-menu-sub title="Quizzes" icon="o-question-mark-circle">
                    <x-menu-item title="Quizzes" icon="o-clipboard-document-check" link="{{ route('quizzes.index') }}" />
                    <x-menu-item title="Questions" icon="o-chat-bubble-left-right" link="{{ route('questions.index') }}" />
                    <x-menu-item title="Options" icon="o-list-bullet" link="{{ route('options.index') }}" />
                    <x-menu-item title="Quiz Attempts" icon="o-pencil-square" link="{{ route('quiz_attempts') }}" />
                </x-menu-sub>
            </x-menu>
        </x-slot:sidebar>

        {{-- Content --}}
        <x-slot:content>
            {{ $slot }}
        </x-slot:content>
    </x-main>

    {{-- Toasts --}}
    <x-toast />
    @stack('scripts')
</body>
</html>
