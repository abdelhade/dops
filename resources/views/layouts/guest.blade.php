<!DOCTYPE html>
<html lang="ar" dir="rtl" data-theme="monokai">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', __('dobs.login_title')) - {{ __('dobs.app_name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ @filemtime(public_path('css/style.css')) ?: 1 }}">
    <script>
        (function () {
            if (localStorage.getItem('dobs-theme') === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        })();
    </script>
</head>
<body class="guest-body">
    <div class="guest-wrapper">
        @yield('content')
    </div>
    <script src="{{ asset('js/autofocus.js') }}?v={{ @filemtime(public_path('js/autofocus.js')) ?: 1 }}"></script>
    <script src="{{ asset('js/shortcuts.js') }}"></script>
</body>
</html>
