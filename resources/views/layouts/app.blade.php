<!DOCTYPE html>
<html lang="en" class="h-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>CPC Nexboard - @yield('title', 'Dashboard')</title>

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="d-flex flex-column min-vh-100">

    <!-- Fixed Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="{{ asset('assets/images/system-logo.webp') }}" 
                 alt="CPC Logo" 
                 class="system-logo">
            <h1 class="system-name">CPC Nexboard</h1>
        </div>

        <nav class="sidebar-nav flex-grow-1">
            <!-- Main Navigation -->
            <a href="{{ route('dashboard.main') }}" 
               class="nav-link {{ request()->routeIs('dashboard.main') ? 'active' : '' }}">
                <img src="{{ asset('assets/icons/dashboard.svg') }}" width="24" height="24" alt="">
                <span>Dashboard</span>
            </a>

            <a href="{{ route('dashboard.sales') }}" 
               class="nav-link {{ request()->routeIs('dashboard.sales') ? 'active' : '' }}">
                <img src="{{ asset('assets/icons/sales.svg') }}" width="24" height="24" alt="">
                <span>Sales</span>
            </a>

            <a href="{{ route('dashboard.production') }}" 
               class="nav-link {{ request()->routeIs('dashboard.production') ? 'active' : '' }}">
                <img src="{{ asset('assets/icons/production.svg') }}" width="24" height="24" alt="">
                <span>Production</span>
            </a>

            <a href="{{ route('dashboard.inventory') }}" 
               class="nav-link {{ request()->routeIs('dashboard.inventory') ? 'active' : '' }}">
                <img src="{{ asset('assets/icons/inventory.svg') }}" width="24" height="24" alt="">
                <span>Inventory</span>
            </a>

            <a href="{{ route('dashboard.logistics') }}" 
               class="nav-link {{ request()->routeIs('dashboard.logistics') ? 'active' : '' }}">
                <img src="{{ asset('assets/icons/logistics.svg') }}" width="24" height="24" alt="">
                <span>Logistics</span>
            </a>

            @if (strtolower(auth()->user()->department ?? '') === 'admin')
                <hr class="my-3">

                <a href="{{ route('admin.users.index') }}" 
                   class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <img src="{{ asset('assets/icons/user-management.svg') }}" width="24" height="24" alt="">
                    <span>Users</span>
                </a>

                <a href="{{ route('admin.activity-logs.index') }}" 
                   class="nav-link {{ request()->routeIs('admin.activity-logs.*') ? 'active' : '' }}">
                    <img src="{{ asset('assets/icons/activity-log.svg') }}" width="24" height="24" alt="">
                    <span>Activity Log</span>
                </a>
            @endif

            <hr class="my-3">

            <!-- Bottom Actions -->
            <a href="#" class="nav-link">
                <img src="{{ asset('assets/icons/chatbot.svg') }}" width="24" height="24" alt="">
                <span>AI Assistant</span>
            </a>

            <a href="{{ route('logout') }}"
               class="nav-link"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <img src="{{ asset('assets/icons/logout.svg') }}" width="24" height="24" alt="">
                <span>Sign Out</span>
            </a>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </nav>
    </aside>

    <!-- Main Content Area -->
    <main class="main-content">
        <!-- Top Navbar -->
        <nav class="navbar fixed-top">
            <div class="container-fluid px-4 d-flex justify-content-between align-items-center">
                <!-- LEFT: Page Icon + Title + Subtitle -->
                <div class="navbar-page-header d-flex align-items-center gap-3">
                    @yield('page-icon')
                    <div class="d-flex flex-column justify-content-center">
                        <h2 class="mb-0">@yield('page-title')</h2>
                        <p class="mb-0 text-muted small">@yield('page-subtitle')</p>
                    </div>
                </div>

                <!-- RIGHT SIDE: Bell + (Name + Email below) + Profile Picture -->
                <div class="d-flex align-items-center gap-4">
                    <!-- Bell + Name/Email (mirroring left side pattern) -->
                    <div class="d-flex align-items-center gap-3">
                        <!-- Bell icon -->
                        <img src="{{ asset('assets/icons/notification.svg') }}" width="28" height="28" alt="Notifications">

                        <!-- Stacked Name + Email (like title + subtitle) -->
                        <div class="d-flex flex-column justify-content-center">
                            <span class="fw-medium">{{ auth()->user()->name }}</span>
                            <small class="text-muted">{{ auth()->user()->email }}</small>
                        </div>

                        <!-- Profile Picture (standalone, aligned center) -->
                        @if (auth()->user()->profile_photo_path)
                            <img src="{{ Storage::url(auth()->user()->profile_photo_path) }}"
                                alt="{{ auth()->user()->name }}"
                                class="rounded-circle"
                                width="42" height="42"
                                style="object-fit: cover; border: 2px solid var(--color-border);">
                        @else
                            <img src="{{ asset('assets/icons/user.svg') }}" width="42" height="42" alt="Profile" class="rounded-circle">
                        @endif
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <div class="content-wrapper">
            <div class="container-fluid px-5 pt-4">
                @yield('content')
            </div>
        </div>
    </main>

    <!-- Scripts -->
    <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    @yield('scripts')
</body>
</html>