<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>@yield('title', 'Lulu Assist Go')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @vite(['resources/js/app.js']) {{-- Loads AdminLTE, Bootstrap, FontAwesome via Vite --}}
</head>
<body class="hold-transition login-page">

    @yield('content')

</body>
</html>
