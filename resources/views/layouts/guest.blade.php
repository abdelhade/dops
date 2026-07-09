<!DOCTYPE html>
<html lang="ar" dir="rtl" data-theme="monokai">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', __('dobs.login_title')) - {{ __('dobs.app_name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('icon-192.png') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#8b5cf6">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Dops">
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
    <style>
        .click-ripple {
            position: absolute;
            border-radius: 50%;
            transform: scale(0);
            animation: ripple-animation 0.6s linear;
            background: rgba(var(--primary-rgb, 100, 150, 255), 0.4);
            pointer-events: none;
            width: 20px;
            height: 20px;
            margin-top: -10px;
            margin-left: -10px;
            z-index: 9999;
        }

        @keyframes ripple-animation {
            to {
                transform: scale(15);
                opacity: 0;
            }
        }

        .login-card {
            transition: transform 0.1s ease-out, box-shadow 0.1s ease-out;
            transform-style: preserve-3d;
            will-change: transform;
        }

        .parallax-bg {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            z-index: -1;
            overflow: hidden;
            pointer-events: none;
        }

        .shape {
            position: absolute;
            border-radius: 50%;
            filter: blur(60px);
            opacity: 0.4;
            transition: transform 0.1s ease-out;
            will-change: transform;
        }

        .shape-1 {
            width: 400px; height: 400px;
            background: #4facfe;
            top: -100px; left: -100px;
        }

        .shape-2 {
            width: 500px; height: 500px;
            background: #00f2fe;
            bottom: -150px; right: -150px;
        }
        
        [data-theme="dark"] .shape-1 { background: #6a11cb; }
        [data-theme="dark"] .shape-2 { background: #2575fc; }
    </style>
</head>
<body class="guest-body">
    <div class="parallax-bg">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
    </div>
    <div class="guest-wrapper">
        @yield('content')
    </div>
    <script src="{{ asset('js/autofocus.js') }}?v={{ @filemtime(public_path('js/autofocus.js')) ?: 1 }}"></script>
    <script src="{{ asset('js/shortcuts.js') }}"></script>
    <script>
        document.addEventListener('mousemove', function(e) {
            const cards = document.querySelectorAll('.login-card');
            const x = (window.innerWidth / 2 - e.pageX) / 40;
            const y = (window.innerHeight / 2 - e.pageY) / 40;

            cards.forEach(card => {
                card.style.transform = `perspective(1000px) rotateY(${x}deg) rotateX(${y}deg)`;
                // Optional: Add dynamic shadow
                card.style.boxShadow = `${-x}px ${-y}px 20px rgba(0,0,0,0.1)`;
            });

            const shapes = document.querySelectorAll('.shape');
            shapes.forEach((shape, index) => {
                const speed = (index + 1) * 3;
                const xOffset = (window.innerWidth / 2 - e.pageX) / speed;
                const yOffset = (window.innerHeight / 2 - e.pageY) / speed;
                shape.style.transform = `translate(${xOffset}px, ${yOffset}px)`;
            });
        });

        document.addEventListener('mouseleave', function() {
            const cards = document.querySelectorAll('.login-card');
            cards.forEach(card => {
                card.style.transform = `perspective(1000px) rotateY(0deg) rotateX(0deg)`;
                card.style.boxShadow = '';
            });
        });

        document.addEventListener('click', function(e) {
            const ripple = document.createElement('div');
            ripple.className = 'click-ripple';
            ripple.style.left = `${e.pageX}px`;
            ripple.style.top = `${e.pageY}px`;
            document.body.appendChild(ripple);

            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    </script>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => console.log('Service Worker registered', reg))
                    .catch(err => console.error('Service Worker registration failed', err));
            });
        }
    </script>
</body>
</html>
