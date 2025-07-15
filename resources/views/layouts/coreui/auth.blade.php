<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Auth | Lulu Assist Go')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            background: #f4f6f9;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .auth-wrapper {
            max-width: 400px;
            width: 100%;
        }

        .auth-logo {
            font-weight: 700;
            font-size: 1.75rem;
            color: #0d6efd;
        }

        .card {
            border-radius: 1rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
    </style>

    @stack('styles')
</head>
<body>
    <div class="auth-wrapper">
        @yield('content')
    </div>

    @stack('scripts')
</body>
</html>
