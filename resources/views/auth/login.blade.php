<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    @vite('resources/css/app.css')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/3.13.3/cdn.min.js" defer></script>
</head>

<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-[#123A7D]">Welcome Back</h2>
            <p class="text-[#1C2A40] mt-2">Please sign in to your account</p>
        </div>

        <!-- Success Message -->
        @if (session('success'))
            <div class="bg-blue-100 border-l-4 border-[#123A7D] text-[#123A7D] p-4 mb-6 rounded" role="alert">
                <p>{{ session('success') }}</p>
            </div>
        @endif

        <!-- Error Message -->
        @if ($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded" role="alert">
                <p>{{ $errors->first() }}</p>
            </div>
        @endif

        <!-- Login Form -->
        <form method="POST" action="/login" class="space-y-6">
            @csrf
            <div>
                <label for="email" class="block text-sm font-medium text-[#1C2A40] mb-1">Email Address</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    required 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#123A7D] focus:border-[#123A7D] outline-none transition-colors"
                >
            </div>
            
            <div>
                <label for="password" class="block text-sm font-medium text-[#1C2A40] mb-1">Password</label>
                <div class="relative">
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#123A7D] focus:border-[#123A7D] outline-none transition-colors"
                    >
                    <button 
                        type="button" 
                        onclick="togglePassword('password')" 
                        class="absolute right-2 bottom-[10px] h-6 w-6 cursor-pointer"
                    >
                        <x-heroicon-c-eye-slash id="eye-slash" class="text-gray-500 hidden"/>
                        <x-heroicon-c-eye id="eye" class="text-gray-500"/>
                    </button>
                </div>
            </div>
            
            <div>
                <button 
                    type="submit" 
                    class="w-full py-2 px-4 bg-[#123A7D] text-white font-medium rounded-lg border-2 border-[#123A7D] transition-all duration-300 hover:bg-white hover:text-[#123A7D] hover:shadow-md focus:outline-none focus:ring-2 focus:ring-[#123A7D]/50 focus:ring-offset-2"
                >
                    Log In
                </button>
            </div>
        </form>
    </div>

    <script>
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
