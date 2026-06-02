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
        <aside class="sidebar" id="appSidebar">
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
                <li class="nav-item {{ request()->routeIs('clients.*') ? 'active' : '' }}">
                    <a href="{{ route('clients.index') }}">
                        <i class="fa-solid fa-user-tie"></i>
                        <span>{{ __('dobs.nav_clients') }}</span>
                    </a>
                </li>
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
          
            </ul>

            <div class="menu-section-title">{{ __('dobs.menu_production') }}</div>
            <ul class="nav-menu">
                <li class="nav-item {{ request()->routeIs('materials.*') ? 'active' : '' }}">
                    <a href="{{ route('materials.index') }}">
                        <i class="fa-solid fa-pallet"></i>
                        <span>{{ __('dobs.nav_materials') }}</span>
                    </a>
                </li>
                <li class="nav-item {{ request()->routeIs('paper-types.*') ? 'active' : '' }}">
                    <a href="{{ route('paper-types.index') }}">
                        <i class="fa-solid fa-scroll"></i>
                        <span>{{ __('dobs.nav_paper_types') }}</span>
                    </a>
                </li>
                <li class="nav-item {{ request()->routeIs('services.*') ? 'active' : '' }}">
                    <a href="{{ route('services.index') }}">
                        <i class="fa-solid fa-bell-concierge"></i>
                        <span>{{ __('dobs.nav_services') }}</span>
                    </a>
                </li>
            </ul>

            <div class="menu-section-title">{{ __('dobs.menu_workflow') }}</div>
            <ul class="nav-menu">
                <li class="nav-item {{ request()->routeIs('stages.*') ? 'active' : '' }}">
                    <a href="{{ route('stages.index') }}">
                        <i class="fa-solid fa-bars-progress"></i>
                        <span>{{ __('dobs.nav_stages') }}</span>
                    </a>
                </li>
                <li class="nav-item {{ request()->routeIs('activities.*') ? 'active' : '' }}">
                    <a href="{{ route('activities.index') }}">
                        <i class="fa-solid fa-list-check"></i>
                        <span>{{ __('dobs.nav_activities') }}</span>
                    </a>
                </li>
            </ul>

            @auth
                @if (auth()->user()->canManageUsers())
                    <div class="menu-section-title">{{ __('dobs.menu_admin') }}</div>
                    <ul class="nav-menu">
                        <li class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                            <a href="{{ route('users.index') }}">
                                <i class="fa-solid fa-users-gear"></i>
                                <span>{{ __('dobs.nav_users') }}</span>
                            </a>
                        </li>
                    </ul>
                @endif
            @endauth

            <div class="sidebar-footer">
                @auth
                    <div class="user-profile">
                        <div class="avatar">{{ auth()->user()->initials() }}</div>
                        <div class="user-info">
                            <span class="user-name">{{ auth()->user()->name }}</span>
                            <span class="user-role">{{ auth()->user()->roleLabel() }}</span>
                        </div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" class="logout-form">
                        @csrf
                        <button type="submit" class="btn btn-secondary btn-sm btn-block-logout">
                            <i class="fa-solid fa-right-from-bracket"></i> {{ __('dobs.logout') }}
                        </button>
                    </form>
                @endauth
            </div>
        </aside>

        <div class="sidebar-overlay" id="sidebarOverlay" hidden aria-hidden="true"></div>

        <main class="main-content">
            <header class="app-head">
                <button
                    type="button"
                    class="sidebar-toggle"
                    id="sidebarToggle"
                    aria-label="{{ __('dobs.toggle_sidebar') }}"
                    aria-expanded="true"
                    aria-controls="appSidebar"
                >
                    <i class="fa-solid fa-bars" aria-hidden="true"></i>
                </button>
            </header>

            <header class="top-header">
                <div class="top-header-text">
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

    <script>
        (function () {
            var toggle = document.getElementById('sidebarToggle');
            var overlay = document.getElementById('sidebarOverlay');
            var mq = window.matchMedia('(max-width: 992px)');

            function isMobile() {
                return mq.matches;
            }

            function setMobileOpen(open) {
                document.body.classList.toggle('sidebar-open', open);
                if (overlay) {
                    overlay.hidden = !open;
                    overlay.setAttribute('aria-hidden', open ? 'false' : 'true');
                }
                updateToggleAria();
            }

            function setDesktopCollapsed(collapsed) {
                document.body.classList.toggle('sidebar-collapsed', collapsed);
                updateToggleAria();
            }

            function updateToggleAria() {
                if (!toggle) {
                    return;
                }
                var expanded = isMobile()
                    ? document.body.classList.contains('sidebar-open')
                    : !document.body.classList.contains('sidebar-collapsed');
                toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            }

            function closeSidebar() {
                if (isMobile()) {
                    setMobileOpen(false);
                } else {
                    setDesktopCollapsed(true);
                }
            }

            if (toggle) {
                toggle.addEventListener('click', function () {
                    if (isMobile()) {
                        setMobileOpen(!document.body.classList.contains('sidebar-open'));
                    } else {
                        setDesktopCollapsed(!document.body.classList.contains('sidebar-collapsed'));
                    }
                });
            }

            if (overlay) {
                overlay.addEventListener('click', closeSidebar);
            }

            document.querySelectorAll('.sidebar .nav-item a').forEach(function (link) {
                link.addEventListener('click', function () {
                    if (mq.matches) {
                        closeSidebar();
                    }
                });
            });

            mq.addEventListener('change', function () {
                document.body.classList.remove('sidebar-open', 'sidebar-collapsed');
                if (overlay) {
                    overlay.hidden = true;
                    overlay.setAttribute('aria-hidden', 'true');
                }
                updateToggleAria();
            });

            updateToggleAria();
        })();
    </script>
    <script src="{{ asset('js/shortcuts.js') }}"></script>
    @yield('scripts')
</body>
</html>
