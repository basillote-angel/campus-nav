<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite('resources/css/app.css')

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <link rel="icon" href="{{ asset('images/navistfind_icon.png') }}" type="image/png">

    <!-- For fancy alert message -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <title>Campus NAV</title>
</head>
<body class="flex h-screen w-full">
    <x-sidebar />  <!-- Sidebar Component -->

    <main class="flex-1 p-6 overflow-hidden">
        @yield('content')
    </main>
</body>
</html>
