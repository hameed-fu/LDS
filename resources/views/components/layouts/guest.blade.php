<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title . ' - ' . config('app.name') : config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen font-sans antialiased bg-base-200/50 dark:bg-base-200">


     
    <div class="flex items-center justify-center min-h-screen bg-gray-100">
        <x-card class="md:w-1/2 lg:w-1/3 mx-auto mt-20 p-8">
            <div class="mb-10 text-center">
                <h2 style="font-weight: bold; font-size: 28px">LDS</h2>
                {{-- <img src="https://placehold.co/200x90" alt="Cool image here" class="mx-auto"> --}}
            </div>
            {{ $slot }}
        </x-card>
    </div>
     
    <x-toast />
</body>

</html>
