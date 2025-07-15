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
                    class="nav-link text-white {{ request()->is('admin/dashboard') ? 'active bg-primary' : '' }}">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>
            </li>

            {{-- Plant Users --}}
            <li class="nav-item mb-1">
                <a href="{{ route('plant-user.index') }}"
                    class="nav-link text-white  {{ request()->routeIs('plant-user.index') ? 'active bg-primary' : '' }}">
                    <i class="fas fa-users me-2"></i> Plant Users
                </a>
            </li>

            <li class="nav-item mb-1">
                <a href="{{ route('customers.index') }}"
                    class="nav-link text-white {{ request()->routeIs('customers.index') ? 'active bg-primary' : '' }}">
                    <i class="fas fa-users me-2"></i>Customers
                </a>
            </li>

            {{-- Nav Title: Master --}}
            <li class="nav-title text-uppercase  small px-3 mt-3 mb-2">Master</li>

            {{-- Plants --}}
            <li class="nav-item mb-1">
                <a href="{{ url('master/plants') }}"
                    class="nav-link text-white {{ request()->is('master/plants*') ? 'active bg-primary' : '' }}">
                    <i class="fas fa-industry me-2"></i> Plants
                </a>
            </li>
        </ul>
    </div>
</aside>
