<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    @vite('resources/css/app.css')
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-8">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-gray-800">Create Account</h2>
            <p class="text-gray-600 mt-2">Join us today and get started</p>
        </div>

        @if ($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded" role="alert">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="/register" class="space-y-5">
            @csrf
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    required 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-400 focus:border-sky-400 transition-colors"
                >
            </div>
            
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
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                <input 
                    type="password" 
                    id="password_confirmation" 
                    name="password_confirmation" 
                    required 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-400 focus:border-sky-400 transition-colors"
                >
            </div>
            
            <div class="pt-2">
                <button 
                    type="submit" 
                    class="w-full py-2 px-4 bg-sky-500 hover:bg-sky-600 text-white font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-sky-400 focus:ring-offset-2"
                >
                    Create Account
                </button>
            </div>
        </form>
        
        <div class="mt-6 text-center">
            <p class="text-gray-600">
                Already have an account? 
                <a href="/login" class="text-sky-500 hover:text-sky-600 font-medium">
                    Login here
                </a>
            </p>
        </div>
    </div>
</body>
</html>