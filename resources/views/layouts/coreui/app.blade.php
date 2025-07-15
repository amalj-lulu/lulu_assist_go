<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Dashboard')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<body class="bg-light">
    <div class="d-flex">
        {{-- Dark Sidebar --}}
        @include('layouts.coreui.sidebar')

        <div class="flex-grow-1 d-flex flex-column" style="min-height: 100vh;">
            {{-- Light Top Navbar --}}
            @include('layouts.coreui.nav')

            {{-- Page Content --}}
            <main class="flex-grow-1 p-4 bg-light">
                @yield('content')
            </main>

            {{-- Footer --}}
            @include('layouts.coreui.footer')
        </div>
    </div>

    {{-- Global Modal --}}
    <div class="modal fade" id="globalModal" tabindex="-1" aria-hidden="true" data-clear-on-close="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
            </div>
        </div>
    </div>

    @stack('scripts')
</body>

</html>
