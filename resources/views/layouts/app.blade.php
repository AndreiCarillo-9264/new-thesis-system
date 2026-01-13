<!DOCTYPE html>
<html lang="en" class="h-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>CPC Nexboard - @yield('title', 'Dashboard')</title>

    <!-- Icons -->
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">

    <!-- Chart.js (global availability for all dashboards) -->
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
                <i class="bx bx-grid-alt"></i>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('dashboard.sales') }}" 
               class="nav-link {{ request()->routeIs('dashboard.sales') ? 'active' : '' }}">
                <i class="bx bx-cart"></i>
                <span>Sales</span>
            </a>

            <a href="{{ route('dashboard.production') }}" 
               class="nav-link {{ request()->routeIs('dashboard.production') ? 'active' : '' }}">
                <i class="bx bx-factory"></i>
                <span>Production</span>
            </a>

            <a href="{{ route('dashboard.inventory') }}" 
               class="nav-link {{ request()->routeIs('dashboard.inventory') ? 'active' : '' }}">
                <i class="bx bx-package"></i>
                <span>Inventory</span>
            </a>

            <a href="{{ route('dashboard.logistics') }}" 
               class="nav-link {{ request()->routeIs('dashboard.logistics') ? 'active' : '' }}">
                <i class="bx bx-truck"></i>
                <span>Logistics</span>
            </a>

            @if (strtolower(auth()->user()->department) === 'admin')
                <hr class="admin-divider">

                <a href="{{ route('admin.users.index') }}" 
                   class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="bx bx-cog"></i>
                    <span>User Management</span>
                </a>

                <a href="{{ route('admin.activity-logs.index') }}" 
                   class="nav-link {{ request()->routeIs('admin.activity-logs.*') ? 'active' : '' }}">
                    <i class="bx bx-history"></i>
                    <span>Activity Log</span>
                </a>
            @endif

            <hr>

            <!-- Bottom Actions -->
            <a href="#" class="nav-link">
                <i class="bx bx-bot"></i>
                <span>AI Assistant</span>
            </a>

            <a href="{{ route('logout') }}"
               class="nav-link"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="bx bx-log-out"></i>
                <span>Sign Out</span>
            </a>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </nav>
    </aside>

    <!-- Main Content Area -->
    <section class="main-content">
        <!-- Top Navbar -->
        <nav class="navbar fixed-top">
            <div class="container-fluid px-4 d-flex justify-content-between align-items-center">
                <div class="navbar-page-header d-flex align-items-center gap-3">
                    @yield('page-icon')
                    <div>
                        <h2 class="mb-0">@yield('page-title')</h2>
                        <p class="mb-0 text-muted small">@yield('page-subtitle')</p>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-4">
                    <!-- Notification -->
                    <a href="#" class="text-dark">
                        <i class="bx bx-bell bx-md"></i>
                    </a>

                    <!-- User Profile -->
                    <div class="d-flex align-items-center gap-2">
                        @if (auth()->user()->profile_photo_path)
                            <img src="{{ asset('storage/' . auth()->user()->profile_photo_path) }}"
                                 alt="{{ auth()->user()->name }}"
                                 class="rounded-circle"
                                 width="36"
                                 height="36"
                                 style="object-fit: cover; border: 2px solid var(--color-border);">
                        @else
                            <i class="bx bx-user-circle bx-md"></i>
                        @endif
                        <span class="fw-medium">{{ auth()->user()->name }}</span>
                    </div>
                </div>
            </div>
        </nav>
    
        <!-- Page Content -->
        <div class="content-wrapper">
            <div class="container-fluid px-5">
                @yield('content')
            </div>
        </div>
    </section>

    <!-- Scripts -->
    <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    @yield('scripts')
</body>
</html>