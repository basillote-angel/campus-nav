@props([
    'type' => 'success', // success, error, warning, info
    'message' => '',
    'id' => 'notification-banner',
    'dismissible' => true,
    'autoDismiss' => true,
    'duration' => 3000
])

@php
    $colors = [
        'success' => 'bg-green-50 border-green-200 text-green-800',
        'error' => 'bg-red-50 border-red-200 text-red-800',
        'warning' => 'bg-amber-50 border-amber-200 text-amber-800',
        'info' => 'bg-blue-50 border-blue-200 text-blue-800'
    ];
    
    $colorClass = $colors[$type] ?? $colors['success'];
@endphp

{{-- This component is included for the JavaScript functions only --}}

<script>
    function showNotificationBanner(message, type = 'success', duration = 3000) {
        // Remove existing banner if any
        const existingBanner = document.getElementById('notification-banner');
        if (existingBanner) {
            closeNotificationBanner('notification-banner');
        }
        
        const colors = {
            success: {
                bg: 'bg-green-50',
                border: 'border-green-200',
                text: 'text-green-800',
                hover: 'hover:bg-green-100',
                buttonText: 'text-green-600',
                buttonHover: 'hover:text-green-800'
            },
            error: {
                bg: 'bg-red-50',
                border: 'border-red-200',
                text: 'text-red-800',
                hover: 'hover:bg-red-100',
                buttonText: 'text-red-600',
                buttonHover: 'hover:text-red-800'
            },
            warning: {
                bg: 'bg-amber-50',
                border: 'border-amber-200',
                text: 'text-amber-800',
                hover: 'hover:bg-amber-100',
                buttonText: 'text-amber-600',
                buttonHover: 'hover:text-amber-800'
            },
            info: {
                bg: 'bg-blue-50',
                border: 'border-blue-200',
                text: 'text-blue-800',
                hover: 'hover:bg-blue-100',
                buttonText: 'text-blue-600',
                buttonHover: 'hover:text-blue-800'
            }
        };
        
        const style = colors[type] || colors.success;
        
        const banner = document.createElement('div');
        banner.id = 'notification-banner';
        banner.className = `fixed top-4 left-1/2 transform -translate-x-1/2 z-[10001] ${style.bg} ${style.border} ${style.text} border rounded-lg shadow-lg px-4 py-3 flex items-center justify-between gap-4 min-w-[300px] max-w-md mx-auto transition-all duration-300 opacity-0 translate-y-[-20px] pointer-events-none`;
        banner.setAttribute('role', 'alert');
        banner.setAttribute('aria-live', 'polite');
        
        banner.innerHTML = `
            <span class="text-sm font-medium flex-1">${message}</span>
            <button 
                type="button"
                onclick="closeNotificationBanner('notification-banner')"
                class="flex-shrink-0 ${style.buttonText} ${style.buttonHover} transition-colors p-1 rounded ${style.hover}"
                aria-label="Close notification"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        `;
        
        document.body.appendChild(banner);
        
        // Animate in
        setTimeout(() => {
            banner.classList.remove('opacity-0', 'translate-y-[-20px]', 'pointer-events-none');
            banner.classList.add('opacity-100', 'translate-y-0');
            banner.style.pointerEvents = 'auto';
        }, 10);
        
        // Auto dismiss
        if (duration > 0) {
            setTimeout(() => {
                closeNotificationBanner('notification-banner');
            }, duration);
        }
    }
    
    function closeNotificationBanner(bannerId) {
        const banner = document.getElementById(bannerId);
        if (banner) {
            banner.classList.remove('opacity-100', 'translate-y-0');
            banner.classList.add('opacity-0', 'translate-y-[-20px]');
            banner.style.pointerEvents = 'none';
            setTimeout(() => {
                if (banner.parentNode) {
                    banner.remove();
                }
            }, 300);
        }
    }
    
    // Make function globally accessible
    window.showNotificationBanner = showNotificationBanner;
    window.closeNotificationBanner = closeNotificationBanner;
</script>

