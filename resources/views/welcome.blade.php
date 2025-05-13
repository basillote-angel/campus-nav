<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Welcome</title>
        @vite('resources/css/app.css')

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    </head>
    <body class="bg-gray-50 text-gray-800 min-h-screen flex flex-col">
        <header class="w-full px-4 sm:px-6 lg:px-8 bg-white shadow-sm">
            <div class="max-w-7xl mx-auto flex justify-between items-center">
                <div class="flex items-center">
                    <img class="h-20 scale-150" src="{{ asset('images/logo.png') }}"/>
                </div>
                
                @if (Route::has('login'))
                    <nav class="flex items-center space-x-4">
                        @auth
                            <a
                                href="{{ url('/dashboard') }}"
                                class="px-5 py-2 bg-green-600 hover:bg-green-600 text-white font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                            >
                                Dashboard
                            </a>
                        @else
                            <a
                                href="{{ route('login') }}"
                                class="px-5 py-2 text-gray-700 hover:text-green-600 font-medium transition-colors"
                            >
                                Log in
                            </a>

                            @if (Route::has('register'))
                                <a
                                    href="{{ route('register') }}"
                                    class="px-5 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                                >
                                    Register
                                </a>
                            @endif
                        @endauth
                    </nav>
                @endif
            </div>
        </header>

        <main class="flex-grow flex flex-col items-center justify-center p-6 lg:p-8">
            <div class="text-center max-w-3xl">
                <h1 class="text-4xl sm:text-5xl font-bold text-gray-800 mb-6">Welcome to Campus Nav</h1>
                <p class="text-xl text-gray-600 mb-8">
                    Your ultimate companion for exploring campus life—find buildings, events, and amenities with ease, and never miss a beat.
                </p>

                
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    @guest
                        <a 
                            href="{{ route('register') }}" 
                            class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                        >
                            Get Started
                        </a>
                        <a 
                            href="{{ route('login') }}" 
                            class="px-6 py-3 border border-gray-300 hover:border-green-500 text-gray-700 font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                        >
                            Sign In
                        </a>
                    @else
                        <a 
                            href="{{ url('/dashboard') }}" 
                            class="px-6 py-3 bg-green-600 hover:bg-green-600 text-white font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                        >
                            Go to Dashboard
                        </a>
                    @endguest
                </div>
            </div>
        </main>

        <footer class="bg-white py-8 border-t border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <p class="text-gray-500">&copy; {{ date('Y') }} DNSC. All rights reserved.</p>
            </div>
        </footer>
    </body>
</html>