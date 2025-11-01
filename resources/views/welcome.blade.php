<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Portal - Campus Nav</title>
    @vite('resources/css/app.css')

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <style>
        body {
            font-family: 'Instrument Sans', sans-serif;
        }
    </style>
</head>

<body class="bg-slate-50 text-[#1C2A40] min-h-screen flex items-center justify-center p-4">

    <div class="bg-white w-full max-w-4xl flex flex-col md:flex-row rounded-xl shadow-2xl overflow-hidden">
        
        <div class="w-full md:w-1/2 p-8 md:p-12 flex flex-col justify-center 
                    bg-gradient-to-br from-[#123A7D] to-[#10316A] text-white">
           
            <h1 class="text-4xl font-bold mb-4 leading-tight">
                Welcome to <br> NavistFind
            </h1>
            <p class="text-xl text-white/80 leading-relaxed">
                Admin Portal for managing users, posts, and system analytics.
            </p>
        </div>

        <div class="w-full md:w-1/2 p-8 md:p-12">
            
           <div class="flex justify-center mb-6">
    <img class="h-28 w-auto" src="{{ asset('images/logo.png') }}" alt="Campus Nav Logo" />
</div>
            
            <h2 class="text-3xl font-bold text-[#123A7D] mb-8 text-center">
                Admin Sign In
            </h2>

            @if (session('success'))
                <div classC="bg-blue-100 border-l-4 border-[#123A7D] text-[#123A7D] p-4 mb-6 rounded" role="alert">
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded" role="alert">
                    <p>{{ $errors->first() }}</p>
                </div>
            @endif

            <form method="POST" action="/login" class="space-y-6">
                @csrf
                
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#123A7D] focus:border-[#123A7D] outline-none transition-colors"
                        placeholder="admin@example.com"
                    >
                </div>
                
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <a href="#" class="text-sm font-medium text-[#123A7D] hover:underline">
                            Forgot Password?
                        </a>
                    </div>
                    <div class="relative">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#123A7D] focus:border-[#123A7D] outline-none transition-colors"
                            placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;"
                        >
                        <button 
                            type="button" 
                            onclick="togglePassword()" 
                            class="absolute inset-y-0 right-0 flex items-center px-4 text-gray-500 hover:text-[#123A7D] transition-colors"
                            aria-label="Toggle password visibility"
                        >
                            <x-heroicon-c-eye-slash id="eye-slash" class="h-5 w-5 hidden"/>
                            <x-heroicon-c-eye id="eye" class="h-5 w-5"/>
                        </button>
                    </div>
                </div>
                
                <div>
                    <button 
                        type="submit" 
                        class="w-full py-3 px-4 bg-[#123A7D] text-white font-medium rounded-lg border-2 border-[#123A7D] 
                               transition-all duration-300 
                               hover:bg-[#10316A] hover:shadow-lg
                               focus:outline-none focus:ring-2 focus:ring-[#123A7D]/50 focus:ring-offset-2
                               transform hover:-translate-y-0.5"
                    >
                        Log In
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Your password toggle script (untouched)
        function togglePassword() {
            const input = document.getElementById('password');
            const eyeSlash = document.getElementById('eye-slash');
            const eye = document.getElementById('eye');
            
            if (input.type === 'password') {
                input.type = 'text';
                eye.style.display = 'none';
                eyeSlash.style.display = 'inline-block';
            } else {
                input.type = 'password';
                eye.style.display = 'inline-block';
                eyeSlash.style.display = 'none';
            }
        }
    </script>
</body>
</html>