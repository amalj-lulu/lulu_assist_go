<aside class="sidebar sidebar-dark bg-dark d-flex flex-column" style="width: 220px; min-height: 100vh;">
    {{-- Brand / Logo --}}
    <div class="sidebar-header py-3 px-4 border-bottom border-secondary">
        <a href="{{ url('dashboard') }}" class="text-white fw-bold text-decoration-none fs-5">
            Lulu Assist Go
        </a>
    </div>

    {{-- Navigation --}}
    <div class="sidebar-nav flex-grow-1 px-2 pt-3">
        <ul class="nav flex-column">
            {{-- Dashboard --}}
            <li class="nav-item mb-1">
                <a href="{{ route('dashboard') }}"
                    class="nav-link d-flex align-items-center text-white rounded px-3 py-2 {{ request()->is('admin/dashboard') ? 'bg-primary' : '' }}">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    <span class="small">Dashboard</span>
                </a>
            </li>

            {{-- Plant Users --}}
            <li class="nav-item mb-1">
                <a href="{{ route('plant-user.index') }}"
                    class="nav-link d-flex align-items-center text-white rounded px-3 py-2 {{ request()->routeIs('plant-user.index') ? 'bg-primary' : '' }}">
                    <i class="fas fa-users me-2"></i>
                    <span class="small">Plant Users</span>
                </a>
            </li>

            {{-- Customers --}}
            <li class="nav-item mb-1">
                <a href="{{ route('customers.index') }}"
                    class="nav-link d-flex align-items-center text-white rounded px-3 py-2 {{ request()->routeIs('customers.index') ? 'bg-primary' : '' }}">
                    <i class="fas fa-users me-2"></i>
                    <span class="small">Customers</span>
                </a>
            </li>

            {{-- Master Section Heading --}}
            <li class="nav-item mt-3 mb-1 px-3">
                <span class="text-uppercase text-light fw-semibold small opacity-75">Master</span>
            </li>

            {{-- Plants --}}
            <li class="nav-item mb-1">
                <a href="{{ url('master/plants') }}"
                    class="nav-link d-flex align-items-center text-white rounded px-3 py-2 {{ request()->is('master/plants*') ? 'bg-primary' : '' }}">
                    <i class="fas fa-industry me-2"></i>
                    <span class="small">Plants</span>
                </a>
            </li>

            {{-- Reports Section Heading --}}
            <li class="nav-item mt-3 mb-1 px-3">
                <span class="text-uppercase text-light fw-semibold small opacity-75">Reports</span>
            </li>

            {{-- Order Report --}}
            <li class="nav-item mb-1">
                <a href="{{ route('order-report') }}"
                    class="nav-link d-flex align-items-center text-white rounded px-3 py-2 {{ request()->routeIs('order-report') ? 'bg-primary' : '' }}">
                    <i class="fas fa-file-alt me-2"></i>
                    <span class="small">Order Report</span>
                </a>
            </li>

            {{-- Future reports can go here --}}
        </ul>
    </div>
</aside>
