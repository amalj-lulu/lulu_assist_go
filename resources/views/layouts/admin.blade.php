@php use Illuminate\Support\Facades\Auth; @endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin Panel') - Lulu Assist Go</title>
    @vite(['resources/js/app.js'])
</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        {{-- TOP NAVBAR --}}
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
                </li>
            </ul>

            <!-- Right -->
            <ul class="navbar-nav ml-auto">
                <!-- User Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle mr-1"></i> {{ Auth::user()->name }}
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <!-- Profile Link -->
                        <a class="dropdown-item" href="{{ route('profile.edit') }}">
                            <i class="fas fa-user-edit mr-2"></i> Edit Profile
                        </a>
                        <div class="dropdown-divider"></div>
                        <!-- Logout -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="dropdown-item text-danger" type="submit">
                                <i class="fas fa-sign-out-alt mr-2"></i> Logout
                            </button>
                        </form>
                    </div>
                </li>
            </ul>
        </nav>

        {{-- SIDEBAR --}}
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <a href="{{ route('dashboard') }}" class="brand-link">
                <span class="brand-text font-weight-light">Lulu Assist</span>
            </a>

            <div class="sidebar">
                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview">
                        <li class="nav-item">
                            <a href="{{ route('dashboard') }}"
                                class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('plant-user.index') }}"
                                class="nav-link {{ request()->routeIs('plant-user.index') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-users"></i>
                                <p>Plant Users 123</p>
                            </a>
                        </li>
                        <li class="nav-item has-treeview {{ request()->is('master/plants*') ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ request()->is('master/plants*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-cogs"></i>
                                <p>
                                    Master
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('plants.index') }}"
                                        class="nav-link {{ request()->is('master/plants') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Plant Management</p>
                                    </a>
                                </li>
                                {{-- Add more sub-items here as needed --}}
                            </ul>
                        </li>

                        {{-- Add more sidebar items here --}}
                    </ul>
                </nav>
            </div>
        </aside>

        {{-- MAIN CONTENT --}}
        <div class="content-wrapper">
            <div class="content-header">
                <div class="container-fluid">
                    <h1 class="m-0">@yield('page-title')</h1>
                </div>
            </div>

            <section class="content">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </section>
        </div>

        {{-- FOOTER --}}
        <footer class="main-footer text-sm">
            <strong>&copy; {{ date('Y') }} Lulu Assist Go</strong> — All rights reserved.
        </footer>
    </div>
</body>

</html>
