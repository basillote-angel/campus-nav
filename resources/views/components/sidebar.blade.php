<aside class="w-64 h-screen bg-white shadow-lg border-r border-gray-200 flex flex-col">
    <!-- Logo Section -->
    <div class="p-6 border-b border-gray-200 flex items-center justify-center bg-gradient-to-r from-blue-600 to-indigo-600">
        <div class="flex items-center space-x-3">
            <div class="text-white">
                <h1 class="text-lg font-bold">NaviStFind</h1>
                <p class="text-xs text-blue-100">Campus lost & found and navigationn System</p>
            </div>
        </div>
    </div>

    <!-- Navigation Section -->
    <nav class="flex-1 p-4 overflow-y-auto">
        <div class="space-y-2">
            <!-- Dashboard -->
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-600' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }} flex items-center px-4 py-3 rounded-lg transition-all duration-200 ease-in-out group">
                <div class="p-2 rounded-lg {{ request()->routeIs('dashboard') ? 'bg-blue-100' : 'bg-gray-100 group-hover:bg-blue-100' }} mr-3">
                    <svg class="h-5 w-5 {{ request()->routeIs('dashboard') ? 'text-blue-600' : 'text-gray-500 group-hover:text-blue-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"></path>
                    </svg>
                </div>
                <span class="font-medium">Dashboard</span>
            </a>

            <!-- Campus Navigation -->
            <a href="{{ route('campus-map') }}" class="{{ request()->routeIs('campus-map') ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-600' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }} flex items-center px-4 py-3 rounded-lg transition-all duration-200 ease-in-out group">
                <div class="p-2 rounded-lg {{ request()->routeIs('campus-map') ? 'bg-blue-100' : 'bg-gray-100 group-hover:bg-blue-100' }} mr-3">
                    <svg class="h-5 w-5 {{ request()->routeIs('campus-map') ? 'text-blue-600' : 'text-gray-500 group-hover:text-blue-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 4m0 13V4m-6 3l6-3"></path>
                    </svg>
                </div>
                <span class="font-medium">Campus Nav</span>
            </a>

            <!-- Lost & Found -->
            <a href="{{ route('item') }}" class="{{ request()->routeIs('item') ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-600' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }} flex items-center px-4 py-3 rounded-lg transition-all duration-200 ease-in-out group">
                <div class="p-2 rounded-lg {{ request()->routeIs('item') ? 'bg-blue-100' : 'bg-gray-100 group-hover:bg-blue-100' }} mr-3">
                    <svg class="h-5 w-5 {{ request()->routeIs('item') ? 'text-blue-600' : 'text-gray-500 group-hover:text-blue-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <span class="font-medium">Lost & Found</span>
            </a>
            
            <!-- Notifications (Admin Only) -->
            @if(auth()->check() && auth()->user()->role === 'admin')
            <a href="{{ route('notifications') }}" class="{{ request()->routeIs('notifications') ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-600' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }} flex items-center px-4 py-3 rounded-lg transition-all duration-200 ease-in-out group">
                <div class="p-2 rounded-lg {{ request()->routeIs('notifications') ? 'bg-blue-100' : 'bg-gray-100 group-hover:bg-blue-100' }} mr-3 relative">
                    <svg class="h-5 w-5 {{ request()->routeIs('notifications') ? 'text-blue-600' : 'text-gray-500 group-hover:text-blue-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                    <!-- Notification Badge -->
                    <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center animate-pulse">
                        {{ \App\Models\Item::where('status', 'pending_approval')->count() }}
                    </span>
                </div>
                <span class="font-medium">Notifications</span>
            </a>
            @endif

            <!-- Profile -->
            <a href="{{ route('profile') }}" class="{{ request()->routeIs('profile') ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-600' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }} flex items-center px-4 py-3 rounded-lg transition-all duration-200 ease-in-out group">
                <div class="p-2 rounded-lg {{ request()->routeIs('profile') ? 'bg-blue-100' : 'bg-gray-100 group-hover:bg-blue-100' }} mr-3">
                    <svg class="h-5 w-5 {{ request()->routeIs('profile') ? 'text-blue-600' : 'text-gray-500 group-hover:text-blue-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <span class="font-medium">Profile</span>
            </a>
        </div>
    </nav>

    <!-- User Section -->
    <div class="p-4 border-t border-gray-200 bg-gray-50">
        <div class="flex items-center space-x-3 mb-4">
            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center text-white font-bold text-sm">
                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ Auth::user()->name }}</p>
                <p class="text-xs text-gray-500 capitalize">{{ Auth::user()->role }}</p>
            </div>
        </div>
        
        <!-- Logout Button -->
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full flex items-center px-4 py-2 text-gray-700 hover:bg-red-50 hover:text-red-600 rounded-lg transition-all duration-200 ease-in-out group">
                <div class="p-2 rounded-lg bg-gray-100 group-hover:bg-red-100 mr-3">
                    <svg class="h-5 w-5 text-gray-500 group-hover:text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                </div>
                <span class="font-medium">Logout</span>
            </button>
        </form>
    </div>
</aside>