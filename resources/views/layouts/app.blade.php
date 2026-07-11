<!DOCTYPE html>
<html lang="ar" dir="rtl" data-theme="monokai">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', __('dobs.app_name')) - {{ __('dobs.app_tagline') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('icon-192.png') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#8b5cf6">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Dops">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
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
    @yield('styles')
</head>
<body>
    <div class="app-container">
        <aside class="sidebar" id="appSidebar">
            <div class="sidebar-top">
                <div class="brand">
                    <div class="brand-icon">D</div>
                    <a href="{{ route('dashboard') }}" class="brand-name-link">
                    <span class="brand-name">{{ __('dobs.app_name') }}</span>
                    </a>
                </div>
                <button
                    type="button"
                    class="sidebar-close"
                    id="sidebarClose"
                    aria-label="{{ __('dobs.toggle_sidebar') }}"
                >
                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                </button>
            </div>

            <ul class="nav-menu">
                <li class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <a href="{{ route('dashboard') }}">
                        <i class="fa-solid fa-chart-pie"></i>
                        <span>{{ __('dobs.nav_dashboard') }}</span>
                    </a>
                </li>
                @if (auth()->user()->hasPermission('operations', 'read'))
                <li class="nav-item {{ request()->routeIs('operations.*') ? 'active' : '' }}">
                    <a href="{{ route('operations.index') }}">
                        <i class="fa-solid fa-arrows-spin"></i>
                        <span>{{ __('dobs.nav_operations') }}</span>
                    </a>
                </li>
                @endif

                @if (auth()->user()->hasPermission('operation-statuses', 'read'))
                <li class="nav-item {{ request()->routeIs('operation-statuses.*') ? 'active' : '' }}">
                    <a href="{{ route('operation-statuses.index') }}">
                        <i class="fa-solid fa-clipboard-list"></i>
                        <span>{{ __('dobs.nav_operation_statuses') }}</span>
                    </a>
                </li>
                @endif

                @if (auth()->user()->hasPermission('operation-kinds', 'read'))
                <li class="nav-item {{ request()->routeIs('operation-kinds.*') ? 'active' : '' }}">
                    <a href="{{ route('operation-kinds.index') }}">
                        <i class="fa-solid fa-tags"></i>
                        <span>{{ __('dobs.nav_operation_kinds') }}</span>
                    </a>
                </li>
                @endif
                
                @if (auth()->user()->hasPermission('items', 'read'))
                <li class="nav-item {{ request()->routeIs('items.*') ? 'active' : '' }}">
                    <a href="{{ route('items.index') }}">
                        <i class="fa-solid fa-box-open"></i>
                        <span>{{ __('dobs.nav_items') }}</span>
                    </a>
                </li>
                @endif

                @if (auth()->user()->hasPermission('clients', 'read'))
                <li class="nav-item {{ request()->routeIs('clients.*') ? 'active' : '' }}">
                    <a href="{{ route('clients.index') }}">
                        <i class="fa-solid fa-user-tie"></i>
                        <span>{{ __('dobs.nav_clients') }}</span>
                    </a>
                </li>
                @endif

                @if (auth()->user()->hasPermission('categories', 'read'))
                <li class="nav-item {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                    <a href="{{ route('categories.index') }}">
                        <i class="fa-solid fa-tags"></i>
                        <span>{{ __('dobs.nav_categories') }}</span>
                    </a>
                </li>
                @endif

                @if (auth()->user()->hasPermission('suppliers', 'read'))
                <li class="nav-item {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                    <a href="{{ route('suppliers.index') }}">
                        <i class="fa-solid fa-truck-field"></i>
                        <span>{{ __('dobs.nav_suppliers') }}</span>
                    </a>
                </li>
                @endif

                @if (auth()->user()->hasPermission('paper-types', 'read'))
                <li class="nav-item {{ request()->routeIs('paper-types.*') ? 'active' : '' }}">
                    <a href="{{ route('paper-types.index') }}">
                        <i class="fa-solid fa-scroll"></i>
                        <span>{{ __('dobs.nav_paper_types') }}</span>
                    </a>
                </li>
                @endif

                @if (auth()->user()->hasPermission('services', 'read'))
                <li class="nav-item {{ request()->routeIs('services.*') ? 'active' : '' }}">
                    <a href="{{ route('services.index') }}">
                        <i class="fa-solid fa-handshake"></i>
                        <span>{{ __('dobs.nav_services') }}</span>
                    </a>
                </li>
                @endif

                @if (auth()->user()->hasPermission('operation-movements', 'read'))
                <li class="nav-item {{ request()->routeIs('operation-movements.*') ? 'active' : '' }}">
                    <a href="{{ route('operation-movements.index') }}">
                        <i class="fa-solid fa-truck-ramp-box"></i>
                        <span>{{ __('dobs.nav_operation_movements') }}</span>
                    </a>
                </li>
                @endif

                @if (auth()->user()->isAdmin())
                <li class="nav-item {{ request()->routeIs('activities.*') ? 'active' : '' }}">
                    <a href="{{ route('activities.index') }}">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        <span>{{ __('dobs.nav_activities') }}</span>
                    </a>
                </li>
                @endif
                @if (auth()->user()->isAdmin() || auth()->user()->isManager())
                <li class="nav-group {{ request()->routeIs('reports.*') ? 'is-open active' : '' }}">
                    <button
                        type="button"
                        class="nav-group-toggle"
                        aria-expanded="{{ request()->routeIs('reports.*') ? 'true' : 'false' }}"
                    >
                        <i class="fa-solid fa-chart-column"></i>
                        <span>{{ __('dobs.nav_reports') }}</span>
                        <i class="fa-solid fa-chevron-down nav-group-chevron" aria-hidden="true"></i>
                    </button>
                    <ul class="nav-submenu">
                        <li class="nav-subitem {{ request()->routeIs('reports.statistics') ? 'active' : '' }}">
                            <a href="{{ route('reports.statistics') }}">
                                <span>{{ __('dobs.report_statistics') }}</span>
                            </a>
                        </li>
                        <li class="nav-subitem {{ request()->routeIs('reports.paper-materials-summary') ? 'active' : '' }}">
                            <a href="{{ route('reports.paper-materials-summary') }}">
                                <span>{{ __('dobs.report_paper_materials_summary') }}</span>
                            </a>
                        </li>
                        <li class="nav-subitem {{ request()->routeIs('reports.general-operations-summary') ? 'active' : '' }}">
                            <a href="{{ route('reports.general-operations-summary') }}">
                                <span>{{ __('dobs.report_general_operations_summary') }}</span>
                            </a>
                        </li>
                        <li class="nav-subitem {{ request()->routeIs('reports.operations-kanban*') ? 'active' : '' }}">
                            <a href="{{ route('reports.operations-kanban') }}">
                                <span>{{ __('dobs.report_operations_kanban') }}</span>
                            </a>
                        </li>
                    </ul>
                </li>
                @endif
                @auth
                    @if (auth()->user()->canManageUsers())
                        <li class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                            <a href="{{ route('users.index') }}">
                                <i class="fa-solid fa-users-gear"></i>
                                <span>{{ __('dobs.nav_users') }}</span>
                            </a>
                        </li>
                    @endif
                @endauth
            </ul>

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

        <div class="sidebar-overlay" id="sidebarOverlay" aria-hidden="true"></div>

        <main class="main-content">
            <header class="top-header">
                <button
                    type="button"
                    class="sidebar-toggle"
                    id="sidebarToggle"
                    aria-label="{{ __('dobs.toggle_sidebar') }}"
                    aria-expanded="false"
                    aria-controls="appSidebar"
                >
                    <i class="fa-solid fa-bars" aria-hidden="true"></i>
                </button>
                <div class="top-header-text">
                    <h1 class="page-title">@yield('header_title')</h1>
                    <p class="page-subtitle">@yield('header_subtitle', __('dobs.default_subtitle'))</p>
                </div>
                <div class="header-actions">
                    @if (auth()->user()?->isAdmin())
                        <a
                            href="{{ route('settings.edit') }}"
                            class="header-icon-link {{ request()->routeIs('settings.*') ? 'is-active' : '' }}"
                            title="{{ __('dobs.settings_title') }}"
                            aria-label="{{ __('dobs.settings_title') }}"
                        >
                            <i class="fa-solid fa-gear" aria-hidden="true"></i>
                        </a>
                    @endif
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

    @if (auth()->user()?->isAdmin())
        @include('partials.delete-password-modal')
        <script>
            window.DOBS_DELETE_LANG = {
                defaultConfirm: @json(__('dobs.delete_password_default_confirm')),
                passwordRequired: @json(__('dobs.delete_password_required')),
            };
        </script>
        <script src="{{ asset('js/delete-password.js') }}?v={{ @filemtime(public_path('js/delete-password.js')) ?: 1 }}"></script>
    @endif
    <script src="{{ asset('js/sidebar.js') }}?v={{ @filemtime(public_path('js/sidebar.js')) ?: 1 }}"></script>
    <script src="{{ asset('js/autofocus.js') }}?v={{ @filemtime(public_path('js/autofocus.js')) ?: 1 }}"></script>
    <script src="{{ asset('js/shortcuts.js') }}"></script>
    <script src="{{ asset('js/spreadsheet-import.js') }}?v={{ @filemtime(public_path('js/spreadsheet-import.js')) ?: 1 }}"></script>
    @yield('scripts')
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
