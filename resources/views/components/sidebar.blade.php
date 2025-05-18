<aside class="w-64 h-screen bg-white shadow-md border-r border-gray-200">
    <div class="p-6 border-b border-gray-200 flex items-center justify-center">
        <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-16 w-16 scale-200">
    </div>
    <nav class="p-4">
        <ul class="space-y-1">
            <li>
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'bg-green-50 text-green-800' : 'text-gray-700 hover:bg-gray-100 hover:text-green-800' }} flex items-center px-4 py-3 rounded-md transition-colors duration-150 ease-in-out">
                    <x-heroicon-o-home class="h-5 w-5 mr-3 {{ request()->routeIs('dashboard') ? 'text-green-800' : 'text-gray-500' }}" />
                    Dashboard
                </a>
            </li>
            <li>
                <a href="{{ route('campus-map') }}" class="{{ request()->routeIs('campus-map') ? 'bg-green-50 text-green-800' : 'text-gray-700 hover:bg-gray-100 hover:text-green-800' }} flex items-center px-4 py-3 rounded-md transition-colors duration-150 ease-in-out">
                    <x-heroicon-o-map class="h-5 w-5 mr-3 {{ request()->routeIs('campus-map') ? 'text-green-800' : 'text-gray-500' }}" />
                    Campus Map
                </a>
            </li>
            <li>
                <a href="{{ route('item') }}" class="{{ request()->routeIs('item') ? 'bg-green-50 text-green-800' : 'text-gray-700 hover:bg-gray-100 hover:text-green-800' }} flex items-center px-4 py-3 rounded-md transition-colors duration-150 ease-in-out">
                    <x-heroicon-s-queue-list class="h-5 w-5 mr-3 {{ request()->routeIs('item') ? 'text-green-800' : 'text-gray-500' }}" />
                    Lost & Found
                </a>
            </li>
            
            {{-- Only show Manage Users if admin --}}
            @if(auth()->check() && auth()->user()->role === 'admin')
            <li>
                <a href="{{ route('manage-users') }}" class="{{ request()->routeIs('manage-users') ? 'bg-green-50 text-green-800' : 'text-gray-700 hover:bg-gray-100 hover:text-green-800' }} flex items-center px-4 py-3 rounded-md transition-colors duration-150 ease-in-out">
                    <x-heroicon-o-users class="h-5 w-5 mr-3 {{ request()->routeIs('manage-users') ? 'text-green-800' : 'text-gray-500' }}" />
                    Manage Users
                </a>
            </li>
            @endif

            <li>
                <a href="{{ route('profile') }}" class="{{ request()->routeIs('profile') ? 'bg-green-50 text-green-800' : 'text-gray-700 hover:bg-gray-100 hover:text-green-800' }} flex items-center px-4 py-3 rounded-md transition-colors duration-150 ease-in-out">
                    <x-heroicon-o-user class="h-5 w-5 mr-3 {{ request()->routeIs('profile') ? 'text-green-800' : 'text-gray-500' }}" />
                    Profile
                </a>
            </li>
        </ul>
        <div class="pt-6 mt-6 border-t border-gray-200">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex w-full items-center px-4 py-3 text-gray-700 hover:bg-gray-100 hover:text-green-800 rounded-md transition-colors duration-150 ease-in-out">
                    <x-eos-logout class="h-5 w-5 mr-3 text-gray-500" />
                    Logout
                </button>
            </form>
        </div>
    </nav>
</aside>