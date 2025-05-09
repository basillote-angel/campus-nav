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
        <header class="w-full py-6 px-4 sm:px-6 lg:px-8 bg-white shadow-sm">
            <div class="max-w-7xl mx-auto flex justify-between items-center">
                <div class="flex items-center">
                    <span class="text-2xl font-bold text-sky-500">Your App</span>
                </div>
                
                @if (Route::has('login'))
                    <nav class="flex items-center space-x-4">
                        @auth
                            <a
                                href="{{ url('/dashboard') }}"
                                class="px-5 py-2 bg-sky-500 hover:bg-sky-600 text-white font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-sky-400 focus:ring-offset-2"
                            >
                                Dashboard
                            </a>
                        @else
                            <a
                                href="{{ route('login') }}"
                                class="px-5 py-2 text-gray-700 hover:text-sky-600 font-medium transition-colors"
                            >
                                Log in
                            </a>

                            @if (Route::has('register'))
                                <a
                                    href="{{ route('register') }}"
                                    class="px-5 py-2 bg-sky-500 hover:bg-sky-600 text-white font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-sky-400 focus:ring-offset-2"
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
                <h1 class="text-4xl sm:text-5xl font-bold text-gray-800 mb-6">Welcome to Your Application</h1>
                <p class="text-xl text-gray-600 mb-8">A modern application built with Laravel and styled with Tailwind CSS</p>
                
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    @guest
                        <a 
                            href="{{ route('register') }}" 
                            class="px-6 py-3 bg-sky-500 hover:bg-sky-600 text-white font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-sky-400 focus:ring-offset-2"
                        >
                            Get Started
                        </a>
                        <a 
                            href="{{ route('login') }}" 
                            class="px-6 py-3 border border-gray-300 hover:border-sky-400 text-gray-700 font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-sky-400 focus:ring-offset-2"
                        >
                            Sign In
                        </a>
                    @else
                        <a 
                            href="{{ url('/dashboard') }}" 
                            class="px-6 py-3 bg-sky-500 hover:bg-sky-600 text-white font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-sky-400 focus:ring-offset-2"
                        >
                            Go to Dashboard
                        </a>
                    @endguest
                </div>
            </div>
            
            <!-- Feature section -->
            <div class="mt-16 grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl w-full">
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <div class="w-12 h-12 bg-sky-100 text-sky-500 rounded-lg flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Fast & Efficient</h3>
                    <p class="text-gray-600">Built with performance in mind to provide the best user experience possible.</p>
                </div>
                
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <div class="w-12 h-12 bg-sky-100 text-sky-500 rounded-lg flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Secure</h3>
                    <p class="text-gray-600">Advanced security features to keep your data safe and protected.</p>
                </div>
                
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <div class="w-12 h-12 bg-sky-100 text-sky-500 rounded-lg flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Scalable</h3>
                    <p class="text-gray-600">Designed to grow with your needs, from small projects to enterprise applications.</p>
                </div>
            </div>
        </main>

        <footer class="bg-white py-8 border-t border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <p class="text-gray-500">&copy; {{ date('Y') }} Your Company. All rights reserved.</p>
            </div>
        </footer>
    </body>
</html>