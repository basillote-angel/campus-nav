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
        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-gray-800">Welcome Back</h2>
            <p class="text-gray-600 mt-2">Please sign in to your account</p>
        </div>

        @if (session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded" role="alert">
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
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    required 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-400 focus:border-sky-400 transition-colors"
                >
            </div>
            
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-400 focus:border-sky-400 transition-colors"
                >
            </div>
            
            <div>
                <button 
                    type="submit" 
                    class="w-full py-2 px-4 bg-sky-500 hover:bg-sky-600 text-white font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-sky-400 focus:ring-offset-2"
                >
                    Sign In
                </button>
            </div>
        </form>
        
        <div class="mt-6 text-center">
            <p class="text-gray-600">
                Don't have an account? 
                <a href="/register" class="text-sky-500 hover:text-sky-600 font-medium">
                    Register here
                </a>
            </p>
        </div>
    </div>
</body>
</html