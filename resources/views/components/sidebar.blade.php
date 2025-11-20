@php
    $isCollapsed = false;
    use App\Enums\FoundItemStatus;
    $pendingCount = \App\Models\FoundItem::where('status', FoundItemStatus::CLAIM_PENDING->value)->count();
    $urgentCount = \App\Models\FoundItem::where('status', FoundItemStatus::CLAIM_PENDING->value)
        ->whereNotNull('claimed_at')
        ->where('claimed_at', '<', now()->subHours(24))
        ->count();
    $badgeColor = $urgentCount > 0 ? 'bg-red-500' : ($pendingCount > 0 ? 'bg-amber-500' : 'bg-blue-500');
@endphp

<!-- Mobile Menu Toggle Button -->
<button 
    id="mobile-menu-toggle" 
    class="md:hidden fixed top-4 left-4 z-[10000] bg-gradient-to-br from-white to-gray-50 text-gray-900 p-3 rounded-xl shadow-lg hover:shadow-xl hover:from-gray-50 hover:to-gray-100 transition-all duration-300 border border-gray-200/50 ring-1 ring-gray-200/50 hover:ring-[#123A7D]/20"
    onclick="toggleMobileSidebar()"
    aria-label="Toggle menu"
>
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
    </svg>
</button>

<!-- Mobile Backdrop -->
<div 
    id="sidebar-backdrop" 
    class="hidden md:hidden fixed inset-0 bg-black/50 z-[9998] transition-opacity duration-300 backdrop-blur-sm"
    onclick="event.stopPropagation(); closeMobileSidebar();"
></div>

<!-- Sidebar -->
<aside 
    id="sidebar" 
    class="fixed md:fixed w-64 md:w-60 md:translate-x-0 transition-all duration-300 ease-in-out z-[9999] h-screen bg-gradient-to-b from-white via-white to-gray-50/30 border-r border-gray-200/50 shadow-xl md:shadow-none flex flex-col {{ $isCollapsed ? 'md:w-16' : 'md:w-60' }}"
    data-collapsed="{{ $isCollapsed ? 'true' : 'false' }}"
>
    <!-- Logo Section -->
    <div class="{{ $isCollapsed ? 'p-4' : 'p-5' }} border-b border-gray-200/50 bg-gradient-to-br from-[#123A7D]/5 via-white to-white flex items-center {{ $isCollapsed ? 'justify-center' : 'justify-between' }} relative">
        <div class="flex items-center gap-3 {{ $isCollapsed ? 'justify-center' : '' }}">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 cursor-pointer hover:scale-105 transition-all duration-200 overflow-hidden" {{ $isCollapsed ? 'onclick="toggleSidebar()" title="Expand sidebar"' : '' }}>
                <img src="{{ asset('images/logo-icon.png') }}" alt="NaviStFind Logo" class="w-16 h-16 object-contain scale-125">
            </div>
            @if(!$isCollapsed)
                <div class="flex flex-col min-w-0 logo-text-container">
                    <h1 class="text-base font-bold text-gray-900 tracking-tight leading-none">NaviStFind</h1>
                    <p class="text-xs text-gray-500 mt-0.5">Admin Portal</p>
                </div>
            @endif
        </div>
        <!-- Desktop Collapse Toggle -->
        <button 
            onclick="toggleSidebar()" 
            class="hidden md:flex absolute -right-3 top-1/2 -translate-y-1/2 w-7 h-7 bg-white border border-gray-200 rounded-full shadow-lg hover:shadow-xl hover:border-Gray-300 transition-all duration-200 items-center justify-center group {{ $isCollapsed ? 'hidden' : '' }}"
            aria-label="Toggle sidebar"
            title="{{ $isCollapsed ? 'Expand sidebar' : 'Collapse sidebar' }}"
        >
            <svg class="w-3.5 h-3.5 text-gray-600 group-hover:text-gray-900 transition-transform duration-300 {{ $isCollapsed ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
            </svg>
        </button>
        <!-- Mobile Close Button -->
        <button 
            onclick="event.stopPropagation(); closeMobileSidebar();" 
            class="md:hidden p-1.5 hover:bg-gray-100 rounded-lg transition-colors"
            aria-label="Close menu"
        >
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>

    <!-- Navigation Section -->
    <nav class="flex-1 p-3 overflow-y-auto overflow-x-hidden custom-scrollbar" id="sidebar-nav">
        <div class="space-y-1" id="nav-menu-items">
            <!-- Main Section -->
            <div class="mb-6">
                @if(!$isCollapsed)
                    <p class="px-3 py-2 text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-2">Main</p>
                @endif
                <div class="space-y-0.5">
                    <!-- Dashboard -->
                    <a 
                        href="{{ route('dashboard') }}" 
                        class="nav-item group flex items-center px-3 py-2.5 rounded-xl transition-all duration-200 relative {{ request()->routeIs('dashboard') ? 'bg-[#123A7D]/10 text-[#123A7D] font-medium' : 'text-gray-700 hover:bg-gray-50' }}"
                        data-tooltip="Dashboard"
                    >
                        <div class="flex items-center justify-center w-9 h-9 rounded-lg {{ request()->routeIs('dashboard') ? 'bg-[#123A7D]/15 text-[#123A7D]' : 'text-gray-500 group-hover:text-gray-700 group-hover:bg-gray-100' }} transition-all duration-200 {{ $isCollapsed ? 'mx-auto' : 'mr-3' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"></path>
                            </svg>
                        </div>
                        <span class="nav-item-text {{ $isCollapsed ? 'hidden' : '' }} flex-1">Dashboard</span>
                        @if(request()->routeIs('dashboard'))
                            <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-8 bg-gradient-to-b from-[#123A7D] to-[#10316A] rounded-r-full {{ $isCollapsed ? 'hidden' : '' }} shadow-sm"></div>
                        @endif
                    </a>

                    <!-- Campus Navigation -->
                    <a 
                        href="{{ route('campus-map') }}" 
                        class="nav-item group flex items-center px-3 py-3 rounded-xl transition-all duration-200 relative {{ request()->routeIs('campus-map') ? 'bg-gradient-to-r from-[#123A7D]/10 to-[#123A7D]/5 text-[#123A7D] font-semibold shadow-sm' : 'text-gray-700 hover:bg-gradient-to-r hover:from-gray-50 hover:to-gray-50/50' }}"
                        data-tooltip="Campus Map"
                    >
                        <div class="flex items-center justify-center w-10 h-10 rounded-xl {{ request()->routeIs('campus-map') ? 'bg-gradient-to-br from-[#123A7D] to-[#10316A] text-white shadow-md' : 'text-gray-500 group-hover:text-[#123A7D] group-hover:bg-[#123A7D]/10' }} transition-all duration-200 {{ $isCollapsed ? 'mx-auto' : 'mr-3' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 4m0 13V4m-6 3l6-3"></path>
                            </svg>
                        </div>
                        <span class="nav-item-text {{ $isCollapsed ? 'hidden' : '' }} flex-1 text-sm">Campus Nav</span>
                        @if(request()->routeIs('campus-map'))
                            <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-8 bg-gradient-to-b from-[#123A7D] to-[#10316A] rounded-r-full {{ $isCollapsed ? 'hidden' : '' }} shadow-sm"></div>
                        @endif
                    </a>

                    <!-- Lost & Found -->
                    <a 
                        href="{{ route('item') }}" 
                        class="nav-item group flex items-center px-3 py-3 rounded-xl transition-all duration-200 relative {{ request()->routeIs('item') || request()->routeIs('items.*') ? 'bg-gradient-to-r from-[#123A7D]/10 to-[#123A7D]/5 text-[#123A7D] font-semibold shadow-sm' : 'text-gray-700 hover:bg-gradient-to-r hover:from-gray-50 hover:to-gray-50/50' }}"
                        data-tooltip="Lost & Found"
                    >
                        <div class="flex items-center justify-center w-10 h-10 rounded-xl {{ request()->routeIs('item') || request()->routeIs('items.*') ? 'bg-gradient-to-br from-[#123A7D] to-[#10316A] text-white shadow-md' : 'text-gray-500 group-hover:text-[#123A7D] group-hover:bg-[#123A7D]/10' }} transition-all duration-200 {{ $isCollapsed ? 'mx-auto' : 'mr-3' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                        <span class="nav-item-text {{ $isCollapsed ? 'hidden' : '' }} flex-1 text-sm">Lost & Found</span>
                        @if(request()->routeIs('item') || request()->routeIs('items.*'))
                            <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-8 bg-gradient-to-b from-[#123A7D] to-[#10316A] rounded-r-full {{ $isCollapsed ? 'hidden' : '' }} shadow-sm"></div>
                        @endif
                    </a>
                </div>
            </div>

            <!-- Admin Tools Section -->
            @if(auth()->check() && (auth()->user()->role === 'admin' || auth()->user()->role === 'staff'))
            <div class="mb-6">
                @if(!$isCollapsed)
                    <p class="px-3 py-2.5 text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Admin</p>
                @endif
                <div class="space-y-1">
                    <!-- Claims Management (Admin Only) -->
                    @if(auth()->user()->role === 'admin')
                    <a 
                        href="{{ route('admin.claims.index') }}" 
                        class="nav-item group flex items-center px-3 py-3 rounded-xl transition-all duration-200 relative {{ request()->routeIs('admin.claims.*') ? 'bg-gradient-to-r from-[#123A7D]/10 to-[#123A7D]/5 text-[#123A7D] font-semibold shadow-sm' : 'text-gray-700 hover:bg-gradient-to-r hover:from-gray-50 hover:to-gray-50/50' }}"
                        data-tooltip="Claims Management{{ $pendingCount > 0 ? ' (' . $pendingCount . ')' : '' }}"
                    >
                        <div class="flex items-center justify-center w-10 h-10 rounded-xl {{ request()->routeIs('admin.claims.*') ? 'bg-gradient-to-br from-[#123A7D] to-[#10316A] text-white shadow-md' : 'text-gray-500 group-hover:text-[#123A7D] group-hover:bg-[#123A7D]/10' }} transition-all duration-200 {{ $isCollapsed ? 'mx-auto' : 'mr-3' }} relative">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            @if($pendingCount > 0)
                                <span 
                                    data-notification-badge
                                    data-pending-claims-count="{{ $pendingCount }}"
                                    class="notification-badge absolute -top-1 -right-1 {{ $badgeColor }} text-white text-[10px] font-bold rounded-full min-w-[20px] h-[20px] flex items-center justify-center px-1.5 shadow-lg ring-2 ring-white {{ $urgentCount > 0 ? 'animate-pulse' : '' }}"
                                >
                                    {{ $pendingCount > 99 ? '99+' : $pendingCount }}
                                </span>
                            @else
                                <span 
                                    data-notification-badge
                                    data-pending-claims-count="0"
                                    class="notification-badge absolute -top-1 -right-1 {{ $badgeColor }} text-white text-[10px] font-bold rounded-full min-w-[20px] h-[20px] flex items-center justify-center px-1.5 shadow-lg ring-2 ring-white hidden"
                                >
                                    0
                                </span>
                            @endif
                        </div>
                        <span class="nav-item-text {{ $isCollapsed ? 'hidden' : '' }} flex-1 text-sm">Claims</span>
                        @if(request()->routeIs('admin.claims.*'))
                            <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-8 bg-gradient-to-b from-[#123A7D] to-[#10316A] rounded-r-full {{ $isCollapsed ? 'hidden' : '' }} shadow-sm"></div>
                        @endif
                    </a>
                    @endif

                    <!-- Users (Admin Only) -->
                    @if(auth()->user()->role === 'admin')
                    <a 
                        href="{{ route('users') }}" 
                        class="nav-item group flex items-center px-3 py-3 rounded-xl transition-all duration-200 relative {{ request()->routeIs('users') || request()->routeIs('users.*') ? 'bg-gradient-to-r from-[#123A7D]/10 to-[#123A7D]/5 text-[#123A7D] font-semibold shadow-sm' : 'text-gray-700 hover:bg-gradient-to-r hover:from-gray-50 hover:to-gray-50/50' }}"
                        data-tooltip="Users"
                    >
                        <div class="flex items-center justify-center w-10 h-10 rounded-xl {{ request()->routeIs('users') || request()->routeIs('users.*') ? 'bg-gradient-to-br from-[#123A7D] to-[#10316A] text-white shadow-md' : 'text-gray-500 group-hover:text-[#123A7D] group-hover:bg-[#123A7D]/10' }} transition-all duration-200 {{ $isCollapsed ? 'mx-auto' : 'mr-3' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        </div>
                        <span class="nav-item-text {{ $isCollapsed ? 'hidden' : '' }} flex-1 text-sm">Users</span>
                        @if(request()->routeIs('users') || request()->routeIs('users.*'))
                            <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-8 bg-gradient-to-b from-[#123A7D] to-[#10316A] rounded-r-full {{ $isCollapsed ? 'hidden' : '' }} shadow-sm"></div>
                        @endif
                    </a>
                    @endif
                </div>
            </div>
            @endif

            <!-- Account Section -->
            <div class="mb-4">
                @if(!$isCollapsed)
                    <p class="px-3 py-2.5 text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Account</p>
                @endif
                <div class="space-y-1">
                    <!-- Profile -->
                    <a 
                        href="{{ route('profile') }}" 
                        class="nav-item group flex items-center px-3 py-3 rounded-xl transition-all duration-200 relative {{ request()->routeIs('profile') ? 'bg-gradient-to-r from-[#123A7D]/10 to-[#123A7D]/5 text-[#123A7D] font-semibold shadow-sm' : 'text-gray-700 hover:bg-gradient-to-r hover:from-gray-50 hover:to-gray-50/50' }}"
                        data-tooltip="Profile"
                    >
                        <div class="flex items-center justify-center w-10 h-10 rounded-xl {{ request()->routeIs('profile') ? 'bg-gradient-to-br from-[#123A7D] to-[#10316A] text-white shadow-md' : 'text-gray-500 group-hover:text-[#123A7D] group-hover:bg-[#123A7D]/10' }} transition-all duration-200 {{ $isCollapsed ? 'mx-auto' : 'mr-3' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <span class="nav-item-text {{ $isCollapsed ? 'hidden' : '' }} flex-1 text-sm">Profile</span>
                        @if(request()->routeIs('profile'))
                            <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-8 bg-gradient-to-b from-[#123A7D] to-[#10316A] rounded-r-full {{ $isCollapsed ? 'hidden' : '' }} shadow-sm"></div>
                        @endif
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- User Section -->
    <div class="p-4 border-t border-gray-200/50 bg-gradient-to-b from-white to-gray-50/30">
        <!-- User Info -->
        <div class="flex items-center gap-3 mb-4 {{ $isCollapsed ? 'justify-center' : '' }}">
            <div class="w-12 h-12 bg-gradient-to-br from-[#123A7D] via-[#1a4fa3] to-[#10316A] text-white rounded-xl flex items-center justify-center font-bold text-base shadow-lg ring-2 ring-[#123A7D]/10 hover:ring-[#123A7D]/20 flex-shrink-0 cursor-pointer hover:shadow-xl hover:scale-105 transition-all duration-200" onclick="toggleUserMenu()">
                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
            </div>
            @if(!$isCollapsed)
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900 truncate">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-gray-500 capitalize font-medium">{{ Auth::user()->role }}</p>
                </div>
            @endif
        </div>

        <!-- User Menu Dropdown -->
        <div id="user-menu" class="hidden {{ $isCollapsed ? '' : 'mb-3' }} bg-white rounded-xl border border-gray-200/50 shadow-lg overflow-hidden ring-1 ring-gray-200/50">
            <a href="{{ route('profile') }}" class="flex items-center px-4 py-2.5 text-gray-700 hover:bg-gradient-to-r hover:from-[#123A7D]/5 hover:to-[#123A7D]/5 hover:text-[#123A7D] transition-all duration-200 text-sm font-medium">
                <svg class="w-4 h-4 mr-3 text-gray-500 group-hover:text-[#123A7D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                View Profile
            </a>
        </div>

        <!-- Logout Button -->
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button 
                type="submit" 
                class="w-full flex items-center px-3 py-3 text-gray-700 hover:bg-gradient-to-r hover:from-red-50 hover:to-red-50/50 hover:text-red-600 rounded-xl transition-all duration-200 group nav-item logout-button {{ $isCollapsed ? 'justify-center' : '' }} font-medium"
                title="Logout"
                data-tooltip="Logout"
            >
                <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gray-100 group-hover:bg-red-100 text-gray-500 group-hover:text-red-600 transition-all duration-200 {{ $isCollapsed ? 'mx-auto' : 'mr-3' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                </div>
                <span class="logout-text nav-item-text {{ $isCollapsed ? 'hidden' : '' }} text-sm">Logout</span>
            </button>
        </form>
    </div>
</aside>

<!-- Tooltip Container (for collapsed sidebar) -->
<div id="sidebar-tooltip" class="hidden fixed z-[60] bg-gray-900 text-white text-xs font-medium rounded-lg px-3 py-2 shadow-xl pointer-events-none whitespace-nowrap">
    <span id="tooltip-text"></span>
</div>

<style>
/* Custom Scrollbar for Sidebar */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: linear-gradient(to bottom, rgba(18, 58, 125, 0.2), rgba(16, 49, 106, 0.2));
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(to bottom, rgba(18, 58, 125, 0.3), rgba(16, 49, 106, 0.3));
}

/* Smooth Transitions */
#sidebar,
#sidebar * {
    transition-property: width, transform, opacity, background-color, color, border-color;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 300ms;
}

@media (max-width: 768px) {
    #sidebar {
        transform: translateX(-100%) !important;
        will-change: transform;
        z-index: 9999 !important;
    }
    
    #sidebar.open {
        transform: translateX(0) !important;
        z-index: 9999 !important;
    }
    
    #sidebar-backdrop {
        will-change: opacity;
        z-index: 9998 !important;
    }
    
    #sidebar-backdrop:not(.hidden) {
        opacity: 1;
        display: block !important;
    }
    
    /* Ensure sidebar is above Leaflet map and all other elements */
    #mobile-menu-toggle {
        z-index: 10000 !important;
    }
}

/* Desktop: Ensure sidebar is always visible */
@media (min-width: 768px) {
    #sidebar {
        transform: translateX(0) !important;
    }
}

/* Tooltip Animation */
@keyframes tooltipFadeIn {
    from {
        opacity: 0;
        transform: translateX(-8px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

#sidebar-tooltip {
    animation: tooltipFadeIn 0.15s ease-out;
}
</style>

<script>
// Sidebar Toggle Functions
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    if (!sidebar) return;
    
    const isCollapsed = sidebar.dataset.collapsed === 'true';
    const newState = !isCollapsed;
    
    sidebar.dataset.collapsed = newState ? 'true' : 'false';
    sidebar.classList.toggle('md:w-16', newState);
    sidebar.classList.toggle('md:w-60', !newState);
    
    // Update localStorage
    localStorage.setItem('sidebar_collapsed', newState ? 'true' : 'false');
    
    // Hide tooltips when expanding
    if (!newState) {
        hideTooltip();
    }
    
    // Update nav item text visibility
    document.querySelectorAll('.nav-item-text').forEach(text => {
        if (newState) {
            text.classList.add('hidden');
        } else {
            text.classList.remove('hidden');
        }
    });
    
    // Update section headers
    document.querySelectorAll('.px-3.py-2.text-\\[10px\\]').forEach(header => {
        if (newState) {
            header.classList.add('hidden');
        } else {
            header.classList.remove('hidden');
        }
    });
    
    // Update logo section
    const logoSection = sidebar.querySelector('.p-5, .p-4');
    if (logoSection) {
        if (newState) {
            logoSection.classList.remove('p-5');
            logoSection.classList.add('p-4');
            logoSection.classList.add('justify-center');
            logoSection.classList.remove('justify-between');
        } else {
            logoSection.classList.remove('p-4');
            logoSection.classList.add('p-5');
            logoSection.classList.remove('justify-center');
            logoSection.classList.add('justify-between');
        }
    }
    
    // Update logo container centering
    const logoContainer = logoSection ? logoSection.querySelector('.flex.items-center') : null;
    if (logoContainer) {
        if (newState) {
            logoContainer.classList.add('justify-center');
        } else {
            logoContainer.classList.remove('justify-center');
        }
    }
    
    // Update logo text visibility
    const logoTextContainer = sidebar.querySelector('.logo-text-container');
    if (logoTextContainer) {
        if (newState) {
            logoTextContainer.classList.add('hidden');
        } else {
            logoTextContainer.classList.remove('hidden');
        }
    }
    
    // Update logo icon click handler
    const logoIconContainer = logoSection ? logoSection.querySelector('.w-12.h-12') : null;
    if (logoIconContainer) {
        if (newState) {
            logoIconContainer.setAttribute('onclick', 'toggleSidebar()');
            logoIconContainer.setAttribute('title', 'Expand sidebar');
        } else {
            logoIconContainer.removeAttribute('onclick');
            logoIconContainer.removeAttribute('title');
        }
    }
    
    // Update collapse button visibility
    const collapseButton = sidebar.querySelector('button[onclick="toggleSidebar()"]');
    if (collapseButton) {
        if (newState) {
            collapseButton.classList.add('hidden');
        } else {
            collapseButton.classList.remove('hidden');
        }
    }
    
    // Update user section layout
    const userSection = sidebar.querySelector('.p-4.border-t');
    if (userSection) {
        const userInfo = userSection.querySelector('.flex.items-center');
        if (userInfo) {
            if (newState) {
                userInfo.classList.add('justify-center');
            } else {
                userInfo.classList.remove('justify-center');
            }
        }
        const userText = userSection.querySelector('.flex-1');
        if (userText) {
            if (newState) {
                userText.classList.add('hidden');
            } else {
                userText.classList.remove('hidden');
            }
        }
        
        // Update logout button
        const logoutButton = userSection.querySelector('.logout-button');
        if (logoutButton) {
            if (newState) {
                logoutButton.classList.add('justify-center');
            } else {
                logoutButton.classList.remove('justify-center');
            }
        }
        const logoutText = userSection.querySelector('.logout-text');
        if (logoutText) {
            if (newState) {
                logoutText.classList.add('hidden');
            } else {
                logoutText.classList.remove('hidden');
            }
        }
    }
}

function toggleMobileSidebar() {
    const sidebar = document.getElementById('sidebar');
    const backdrop = document.getElementById('sidebar-backdrop');
    const hamburgerButton = document.getElementById('mobile-menu-toggle');
    const body = document.body;
    
    if (!sidebar || !backdrop) return;
    
    const isOpen = sidebar.classList.contains('open');
    
    if (isOpen) {
        // Close sidebar
        sidebar.classList.remove('open');
        backdrop.classList.add('hidden');
        if (hamburgerButton) {
            hamburgerButton.classList.remove('hidden');
        }
        body.style.overflow = '';
    } else {
        // Open sidebar
        if (hamburgerButton) {
            hamburgerButton.classList.add('hidden');
        }
        backdrop.classList.remove('hidden');
        // Small delay to ensure backdrop is visible first
        setTimeout(() => {
            sidebar.classList.add('open');
        }, 10);
        body.style.overflow = 'hidden';
    }
}

function closeMobileSidebar() {
    const sidebar = document.getElementById('sidebar');
    const backdrop = document.getElementById('sidebar-backdrop');
    const hamburgerButton = document.getElementById('mobile-menu-toggle');
    const body = document.body;
    
    if (!sidebar || !backdrop) return;
    
    sidebar.classList.remove('open');
    backdrop.classList.add('hidden');
    if (hamburgerButton) {
        hamburgerButton.classList.remove('hidden');
    }
    body.style.overflow = '';
    
    try {
        if (typeof event !== 'undefined' && event) {
            event.stopPropagation();
            event.preventDefault();
        }
    } catch (e) {
        // Event may not be in scope
    }
}

// User Menu Toggle
function toggleUserMenu() {
    const menu = document.getElementById('user-menu');
    if (menu) {
        menu.classList.toggle('hidden');
    }
}

// Close user menu when clicking outside
document.addEventListener('click', function(e) {
    const userMenu = document.getElementById('user-menu');
    const userAvatar = document.querySelector('.w-10.h-10');
    
    if (userMenu && !userMenu.contains(e.target) && !userAvatar?.contains(e.target)) {
        userMenu.classList.add('hidden');
    }
});

// Enhanced Tooltip System
const tooltip = document.getElementById('sidebar-tooltip');
const tooltipText = document.getElementById('tooltip-text');
let tooltipTimeout;

function showTooltip(element, text) {
    if (!tooltip || !tooltipText) return;
    
    const rect = element.getBoundingClientRect();
    const sidebar = document.getElementById('sidebar');
    const isCollapsed = sidebar?.dataset.collapsed === 'true';
    
    if (!isCollapsed) return;
    
    tooltipText.textContent = text;
    
    // Position tooltip to the right of the sidebar
    const sidebarRect = sidebar.getBoundingClientRect();
    tooltip.style.left = (sidebarRect.right + 12) + 'px';
    tooltip.style.top = (rect.top + (rect.height / 2) - (tooltip.offsetHeight / 2)) + 'px';
    
    tooltip.classList.remove('hidden');
}

function hideTooltip() {
    if (tooltip) {
        tooltip.classList.add('hidden');
    }
    if (tooltipTimeout) {
        clearTimeout(tooltipTimeout);
    }
}

// Initialize tooltips for collapsed sidebar
document.addEventListener('DOMContentLoaded', function() {
    const navItems = document.querySelectorAll('.nav-item');
    const sidebar = document.getElementById('sidebar');
    
    navItems.forEach(item => {
        const tooltipText = item.getAttribute('data-tooltip') || item.querySelector('.nav-item-text')?.textContent || '';
        
        item.addEventListener('mouseenter', function() {
            if (sidebar?.dataset.collapsed === 'true' && tooltipText) {
                tooltipTimeout = setTimeout(() => {
                    showTooltip(this, tooltipText);
                }, 200); // Small delay for better UX
            }
        });
        
        item.addEventListener('mouseleave', function() {
            if (tooltipTimeout) {
                clearTimeout(tooltipTimeout);
            }
            hideTooltip();
        });
    });
});

// Initialize sidebar state on load
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    
    if (!sidebar) return;
    
    // Check localStorage for saved state
    const savedState = localStorage.getItem('sidebar_collapsed');
    if (savedState === 'true' && window.innerWidth >= 768) {
        sidebar.dataset.collapsed = 'true';
        sidebar.classList.remove('md:w-60');
        sidebar.classList.add('md:w-16');
        
        // Hide text elements
        document.querySelectorAll('.nav-item-text').forEach(text => {
            text.classList.add('hidden');
        });
        
        // Hide section headers
        document.querySelectorAll('.px-3.py-2.text-\\[10px\\]').forEach(header => {
            header.classList.add('hidden');
        });
        
        // Update logo
        const logoText = sidebar.querySelector('.flex.flex-col');
        if (logoText) {
            logoText.classList.add('hidden');
        }
        
        // Update user section
        const userSection = sidebar.querySelector('.p-4.border-t');
        if (userSection) {
            const userInfo = userSection.querySelector('.flex.items-center');
            if (userInfo) {
                userInfo.classList.add('justify-center');
            }
            const userText = userSection.querySelector('.flex-1');
            if (userText) {
                userText.classList.add('hidden');
            }
            
            // Update logout button
            const logoutButton = userSection.querySelector('.logout-button');
            if (logoutButton) {
                logoutButton.classList.add('justify-center');
            }
            const logoutText = userSection.querySelector('.logout-text');
            if (logoutText) {
                logoutText.classList.add('hidden');
            }
        }
    }
    
    // Auto-close mobile sidebar on window resize to desktop
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 768) {
            closeMobileSidebar();
        }
    });
});
</script>
