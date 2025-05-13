<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite('resources/css/app.css')

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <link rel="icon" href="{{ asset('images/rel-logo-icon.png') }}" type="image/png">

    <title>Campus NAV</title>
</head>
<body class="flex h-screen">
    <x-sidebar />  <!-- Sidebar Component -->

    <main class="flex-1 p-6 bg-gray-100">
        @yield('content')
    </main>
</body>
</html>
