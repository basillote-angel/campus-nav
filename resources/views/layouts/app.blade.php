<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite('resources/css/app.css')

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <link rel="icon" href="{{ asset('images/navistfind_icon.png') }}" type="image/png">

    <!-- CSRF Token for AJAX requests -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- For fancy alert message -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <title>Campus NAV</title>
</head>
<body class="flex h-screen w-full overflow-hidden">
    <x-sidebar />  <!-- Sidebar Component -->

    <main class="flex-1 p-6 overflow-y-auto transition-all duration-300" id="main-content">
        @yield('content')
    </main>
    
    {{-- Include notification banner component --}}
    @include('components.ui.notification-banner')
    
    {{-- Include modern loading modal component --}}
    @include('components.ui.loading-modal')
    
    <script>
    // Adjust main content padding-left when sidebar is fixed on desktop
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('main-content');
        
        if (!sidebar || !mainContent) return;
        
        function updateMainPadding() {
            const isCollapsed = sidebar.dataset.collapsed === 'true';
            if (window.innerWidth >= 768) {
                // Desktop: sidebar is fixed at 240px (w-60 = 15rem), content starts immediately after
                // When collapsed, sidebar is 64px (w-16 = 4rem)
                const sidebarWidth = isCollapsed ? 64 : 240; // in pixels
                mainContent.style.paddingLeft = (sidebarWidth / 16) + 'rem'; // Convert px to rem
            } else {
                // Mobile: sidebar is overlay, no padding needed
                mainContent.style.paddingLeft = '';
            }
        }
        
        // Listen for sidebar toggle
        const observer = new MutationObserver(updateMainPadding);
        observer.observe(sidebar, { attributes: true, attributeFilter: ['data-collapsed'] });
        
        // Update on resize
        window.addEventListener('resize', updateMainPadding);
        
        // Initial update
        updateMainPadding();
        
        // Also update when sidebar is toggled
        const originalToggle = window.toggleSidebar;
        if (originalToggle) {
            window.toggleSidebar = function() {
                originalToggle();
                setTimeout(updateMainPadding, 50);
            };
        }
    });
    </script>
    
    {{-- Real-time Notifications Script --}}
    @if(auth()->check() && auth()->user()->role === 'admin')
        <script src="{{ asset('js/realtime-notifications.js') }}"></script>
    @endif
    
    {{-- Scripts Stack for page-specific JavaScript --}}
    @stack('scripts')
</body>
</html>
