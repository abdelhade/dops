<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', __('dobs.app_name')) - {{ __('dobs.app_tagline') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    @yield('styles')
</head>
<body>
    <div class="app-container">
        <aside class="sidebar">
            <div class="brand">
                <div class="brand-icon">D</div>
                <span class="brand-name">{{ __('dobs.app_name') }}</span>
            </div>

            <div class="menu-section-title">{{ __('dobs.menu_core') }}</div>
            <ul class="nav-menu">
                <li class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <a href="{{ route('dashboard') }}">
                        <i class="fa-solid fa-chart-pie"></i>
                        <span>{{ __('dobs.nav_dashboard') }}</span>
                    </a>
                </li>
                <li class="nav-item {{ request()->routeIs('operations.*') ? 'active' : '' }}">
                    <a href="{{ route('operations.index') }}">
                        <i class="fa-solid fa-arrows-spin"></i>
                        <span>{{ __('dobs.nav_operations') }}</span>
                    </a>
                </li>
                <li class="nav-item {{ request()->routeIs('items.*') ? 'active' : '' }}">
                    <a href="{{ route('items.index') }}">
                        <i class="fa-solid fa-box-open"></i>
                        <span>{{ __('dobs.nav_items') }}</span>
                    </a>
                </li>
            </ul>

            <div class="menu-section-title">{{ __('dobs.menu_relationships') }}</div>
            <ul class="nav-menu">
                <li class="nav-item {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                    <a href="{{ route('categories.index') }}">
                        <i class="fa-solid fa-tags"></i>
                        <span>{{ __('dobs.nav_categories') }}</span>
                    </a>
                </li>
                <li class="nav-item {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                    <a href="{{ route('suppliers.index') }}">
                        <i class="fa-solid fa-truck-field"></i>
                        <span>{{ __('dobs.nav_suppliers') }}</span>
                    </a>
                </li>
                <li class="nav-item {{ request()->routeIs('paper-sizes.*') ? 'active' : '' }}">
                    <a href="{{ route('paper-sizes.index') }}">
                        <i class="fa-solid fa-maximize"></i>
                        <span>{{ __('dobs.nav_paper_sizes') }}</span>
                    </a>
                </li>
            </ul>

            <div class="sidebar-footer">
                <div class="user-profile">
                    <div class="avatar">AD</div>
                    <div class="user-info">
                        <span class="user-name">{{ __('dobs.user_name') }}</span>
                        <span class="user-role">{{ __('dobs.user_role') }}</span>
                    </div>
                </div>
            </div>
        </aside>

        <main class="main-content">
            <header class="top-header">
                <div>
                    <h1 class="page-title">@yield('header_title')</h1>
                    <p class="page-subtitle">@yield('header_subtitle', __('dobs.default_subtitle'))</p>
                </div>
                <div class="header-actions">
                    @yield('header_actions')
                </div>
            </header>

            @if (session('success'))
                <div class="alert alert-success">
                    <i class="fa-solid fa-circle-check"></i>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <div>{{ session('error') }}</div>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <div>
                        <ul class="error-list">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <div class="page-content-body">
                @yield('content')
            </div>
        </main>
    </div>

    @yield('scripts')
</body>
</html>
