<!DOCTYPE html>
<html>
<head>
    <title>Laravel Reverb Test</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>
    <h1>Laravel Reverb Test</h1>
    <div id="notification"></div>
    <button id="triggerBtn" class="btn btn-primary mt-3">Trigger Event</button>

    <!-- Echo and Reverb JS -->
    <script type="module">
        import Echo from "https://cdn.jsdelivr.net/npm/laravel-echo/dist/echo.mjs";

        // Initialize Echo with Reverb
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: import.meta.env.VITE_REVERB_APP_KEY,
            wsHost: import.meta.env.VITE_REVERB_HOST,
            wsPort: import.meta.env.VITE_REVERB_PORT,
            forceTLS: import.meta.env.VITE_REVERB_SCHEME === 'https',
            enabledTransports: ['ws', 'wss'],
        });

        // Listen on private channel
        window.Echo.private('test-channel')
            .listen('.TestSignalEvent', (data) => {
                console.log('Event received: ', data);
                const d1 = document.getElementById('notification');
                d1.insertAdjacentHTML('beforeend', '<div class="alert alert-success alert-dismissible fade show"><span><i class="fa fa-circle-check"></i> '+data.message+'</span></div>');
            });

        // Trigger the event via button
        const btn = document.getElementById('triggerBtn');
        btn.addEventListener('click', () => {
            fetch('/send-test-signal')
                .then(res => res.json())
                .then(data => console.log(data));
        });
    </script>
</body>
</html>
