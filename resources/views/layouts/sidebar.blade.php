<nav class="col-md-3 col-lg-2 d-md-block bg-white sidebar collapse shadow-sm">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <i class="bi bi-house-door"></i> Dashboard
                </a>
            </li>
            <!-- Add more items -->
        </ul>
    </div>
</nav>
