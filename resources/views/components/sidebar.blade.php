<aside class="w-64 h-screen bg-[#123A7D] text-white shadow-lg flex flex-col border-r border-blue-900/20">
    <!-- Logo Section -->
    <div class="p-6 border-b border-blue-800/40 bg-[#123A7D] flex items-center justify-center">
        <div class="flex flex-col items-center text-center">
            <h1 class="text-xl font-extrabold text-white tracking-wide">NaviStFind</h1>
            <p class="text-xs text-blue-100 mt-1">Lost & Found + Campus Navigation</p>
        </div>
    </div>

    <!-- Navigation Section -->
    <nav class="flex-1 p-4 overflow-y-auto">
        <div class="space-y-2">
            <!-- Dashboard -->
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'bg-white text-[#123A7D] font-semibold' : 'text-white/80 hover:bg-white/10' }} flex items-center px-4 py-3 rounded-lg transition-all duration-300 ease-in-out group">
                <div class="p-2 rounded-lg {{ request()->routeIs('dashboard') ? 'bg-[#123A7D]/10' : 'bg-white/10 group-hover:bg-white/20' }} mr-3">
                    <svg class="h-5 w-5 {{ request()->routeIs('dashboard') ? 'text-[#123A7D]' : 'text-white group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"></path>
                    </svg>
                </div>
                <span>Dashboard</span>
            </a>

            <!-- Campus Navigation -->
            <a href="{{ route('campus-map') }}" class="{{ request()->routeIs('campus-map') ? 'bg-white text-[#123A7D] font-semibold' : 'text-white/80 hover:bg-white/10' }} flex items-center px-4 py-3 rounded-lg transition-all duration-300 ease-in-out group">
                <div class="p-2 rounded-lg {{ request()->routeIs('campus-map') ? 'bg-[#123A7D]/10' : 'bg-white/10 group-hover:bg-white/20' }} mr-3">
                    <svg class="h-5 w-5 {{ request()->routeIs('campus-map') ? 'text-[#123A7D]' : 'text-white group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 4m0 13V4m-6 3l6-3"></path>
                    </svg>
                </div>
                <span>Campus Nav</span>
            </a>

            <!-- Lost & Found -->
            <a href="{{ route('item') }}" class="{{ request()->routeIs('item') ? 'bg-white text-[#123A7D] font-semibold' : 'text-white/80 hover:bg-white/10' }} flex items-center px-4 py-3 rounded-lg transition-all duration-300 ease-in-out group">
                <div class="p-2 rounded-lg {{ request()->routeIs('item') ? 'bg-[#123A7D]/10' : 'bg-white/10 group-hover:bg-white/20' }} mr-3">
                    <svg class="h-5 w-5 {{ request()->routeIs('item') ? 'text-[#123A7D]' : 'text-white group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <span>Lost & Found</span>
            </a>

            <!-- Notifications (Admin Only) -->
            @if(auth()->check() && auth()->user()->role === 'admin')
            <a href="{{ route('notifications') }}" class="{{ request()->routeIs('notifications') ? 'bg-white text-[#123A7D] font-semibold' : 'text-white/80 hover:bg-white/10' }} flex items-center px-4 py-3 rounded-lg transition-all duration-300 ease-in-out group relative">
                <div class="p-2 rounded-lg {{ request()->routeIs('notifications') ? 'bg-[#123A7D]/10' : 'bg-white/10 group-hover:bg-white/20' }} mr-3 relative">
                    <svg class="h-5 w-5 {{ request()->routeIs('notifications') ? 'text-[#123A7D]' : 'text-white group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                    <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center animate-pulse">
                        {{ \App\Models\FoundItem::where('status', 'matched')->count() }}
                    </span>
                </div>
                <span>Notifications</span>
            </a>
            @endif

            <!-- Users (Admin Only) -->
            @if(auth()->check() && auth()->user()->role === 'admin')
            <a href="{{ route('users') }}" class="{{ request()->routeIs('users') ? 'bg-white text-[#123A7D] font-semibold' : 'text-white/80 hover:bg-white/10' }} flex items-center px-4 py-3 rounded-lg transition-all duration-300 ease-in-out group">
                <div class="p-2 rounded-lg {{ request()->routeIs('users') ? 'bg-[#123A7D]/10' : 'bg-white/10 group-hover:bg-white/20' }} mr-3">
                    <svg class="h-5 w-5 {{ request()->routeIs('users') ? 'text-[#123A7D]' : 'text-white group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5V9a2 2 0 00-2-2h-3M7 20H2v-9a2 2 0 012-2h3m5 11V9m0 0a3 3 0 10-6 0m6 0a3 3 0 106 0" />
                    </svg>
                </div>
                <span>Users</span>
            </a>
            @endif

            <!-- Profile -->
            <a href="{{ route('profile') }}" class="{{ request()->routeIs('profile') ? 'bg-white text-[#123A7D] font-semibold' : 'text-white/80 hover:bg-white/10' }} flex items-center px-4 py-3 rounded-lg transition-all duration-300 ease-in-out group">
                <div class="p-2 rounded-lg {{ request()->routeIs('profile') ? 'bg-[#123A7D]/10' : 'bg-white/10 group-hover:bg-white/20' }} mr-3">
                    <svg class="h-5 w-5 {{ request()->routeIs('profile') ? 'text-[#123A7D]' : 'text-white group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <span>Profile</span>
            </a>
        </div>
    </nav>

    <!-- User Section -->
    <div class="p-4 border-t border-blue-800/40 bg-[#123A7D]/95">
        <div class="flex items-center space-x-3 mb-4">
            <div class="w-10 h-10 bg-white text-[#123A7D] rounded-full flex items-center justify-center font-bold text-sm shadow-md">
                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-white truncate">{{ Auth::user()->name }}</p>
                <p class="text-xs text-blue-200 capitalize">{{ Auth::user()->role }}</p>
            </div>
        </div>

        <!-- Logout Button -->
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full flex items-center px-4 py-2 text-white/80 hover:bg-red-600 hover:text-white rounded-lg transition-all duration-300 ease-in-out group">
                <div class="p-2 rounded-lg bg-white/10 group-hover:bg-red-500 mr-3 transition">
                    <svg class="h-5 w-5 text-white group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                </div>
                <span>Logout</span>
            </button>
        </form>
    </div>
</aside>
