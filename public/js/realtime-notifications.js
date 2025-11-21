/**
 * Real-time Notification System for Admin Dashboard
 * Polls for new notifications and updates UI in real-time
 */

class RealtimeNotifications {
    constructor() {
        this.pollInterval = 5000; // Poll every 5 seconds
        this.pollTimer = null;
        this.lastCheckTime = null;
        this.apiBaseUrl = '/api';
        this.isPolling = false;
        this.unreadCount = 0;
        
        this.init();
    }

    init() {
        // Get CSRF token
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        
        // Check if user is authenticated (admin)
        if (!this.csrfToken) {
            console.warn('CSRF token not found. Notifications disabled.');
            return;
        }

        // Start polling when page is visible
        if (document.visibilityState === 'visible') {
            this.startPolling();
        }

        // Resume polling when page becomes visible
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') {
                this.startPolling();
            } else {
                this.stopPolling();
            }
        });

        // Initial check
        this.checkNotifications();
    }

    startPolling() {
        if (this.isPolling) return;
        
        this.isPolling = true;
        this.pollTimer = setInterval(() => {
            this.checkNotifications();
        }, this.pollInterval);
    }

    stopPolling() {
        if (this.pollTimer) {
            clearInterval(this.pollTimer);
            this.pollTimer = null;
        }
        this.isPolling = false;
    }

    async checkNotifications() {
        try {
            // Use web route for session-based auth instead of API route
            const response = await fetch('/notifications/updates', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                credentials: 'same-origin', // Use same-origin for session cookies
            });

            if (!response.ok) {
                if (response.status === 401 || response.status === 419) {
                    // Not authenticated or CSRF token expired, stop polling
                    console.warn('Authentication failed. Stopping notification polling.');
                    this.stopPolling();
                    return;
                }
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            this.handleNotificationUpdate(data);
        } catch (error) {
            console.error('Error checking notifications:', error);
            // Don't stop polling on error, just log it
        }
    }

    handleNotificationUpdate(data) {
        const newUnreadCount = data.unread_count || 0;
        const pendingClaimsCount = data.pending_claims_count || 0;
        const recentNotifications = data.recent_notifications || [];
        const previousCount = this.unreadCount;

        // Update Claims badge with pending claims count (not notification count)
        // The badge on Claims menu should show pending claims, not notifications
        if (pendingClaimsCount !== undefined) {
            this.updateBadge(pendingClaimsCount);
        }

        // Update notification count for page title
        if (newUnreadCount !== this.unreadCount) {
            // Show browser notification for new notifications (only if count increased)
            if (newUnreadCount > previousCount && recentNotifications.length > 0) {
                // Get the most recent notification that's actually new
                const newestNotification = recentNotifications[0];
                this.showBrowserNotification(newestNotification);
            }
            
            this.unreadCount = newUnreadCount;
            
            // Update page title with notification count
            this.updatePageTitle(newUnreadCount);
        }

        // Update notification dropdown if it exists
        if (recentNotifications.length > 0) {
            this.updateNotificationDropdown(recentNotifications);
        }
    }

    updateBadge(count) {
        // Update Claims badge in sidebar (shows pending claims count)
        const badgeElements = document.querySelectorAll('.notification-badge, [data-notification-badge]');
        badgeElements.forEach(badge => {
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.classList.remove('hidden');
                badge.setAttribute('data-pending-claims-count', count);
                // Only add pulse animation if count is high (urgent)
                if (count >= 5) {
                    badge.classList.add('animate-pulse');
                } else {
                    badge.classList.remove('animate-pulse');
                }
            } else {
                badge.classList.add('hidden');
                badge.classList.remove('animate-pulse');
                badge.setAttribute('data-pending-claims-count', '0');
            }
        });
    }

    updatePageTitle(count) {
        // Update page title if there are unread notifications
        if (count > 0) {
            const originalTitle = document.title.replace(/^\(\d+\)\s*/, '');
            document.title = `(${count}) ${originalTitle}`;
        } else {
            document.title = document.title.replace(/^\(\d+\)\s*/, '');
        }
    }

    updateNotificationDropdown(notifications) {
        const dropdown = document.getElementById('notification-dropdown');
        if (!dropdown) return;

        const list = dropdown.querySelector('.notification-list');
        if (!list) return;

        // Clear existing items
        list.innerHTML = '';

        // Add new notifications
        notifications.slice(0, 5).forEach(notification => {
            const item = this.createNotificationItem(notification);
            list.appendChild(item);
        });

        // Show "View All" link if there are more
        if (notifications.length > 5) {
            const viewAllLink = document.createElement('a');
            viewAllLink.href = '/admin/claims?tab=pending';
            viewAllLink.className = 'block text-center py-2 text-sm text-blue-600 hover:text-blue-800';
            viewAllLink.textContent = 'View All Notifications';
            list.appendChild(viewAllLink);
        }
    }

    createNotificationItem(notification) {
        const item = document.createElement('div');
        item.className = 'px-4 py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100';
        item.dataset.notificationId = notification.id;
        
        const timeAgo = this.formatTimeAgo(notification.created_at);
        
        item.innerHTML = `
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <div class="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium text-gray-900">${this.escapeHtml(notification.title)}</p>
                    <p class="text-sm text-gray-500 mt-1">${this.escapeHtml(notification.body)}</p>
                    <p class="text-xs text-gray-400 mt-1">${timeAgo}</p>
                </div>
            </div>
        `;

        // Add click handler to navigate to claim
        if (notification.related_id) {
            item.addEventListener('click', () => {
                window.location.href = `/admin/claims?tab=pending&highlight=${notification.related_id}`;
            });
        }

        return item;
    }

    async showBrowserNotification(notification) {
        // Request permission if not granted
        if (Notification.permission === 'default') {
            await Notification.requestPermission();
        }

        if (Notification.permission === 'granted') {
            const notificationObj = new Notification(notification.title, {
                body: notification.body,
                icon: '/images/navistfind_icon.png',
                badge: '/images/navistfind_icon.png',
                tag: `notification-${notification.id}`,
                requireInteraction: false,
            });

            notificationObj.onclick = () => {
                window.focus();
                if (notification.related_id) {
                    window.location.href = `/admin/claims?tab=pending&highlight=${notification.related_id}`;
                }
                notificationObj.close();
            };
        }
    }

    formatTimeAgo(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffMs = now - date;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMs / 3600000);
        const diffDays = Math.floor(diffMs / 86400000);

        if (diffMins < 1) return 'Just now';
        if (diffMins < 60) return `${diffMins} minute${diffMins > 1 ? 's' : ''} ago`;
        if (diffHours < 24) return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
        if (diffDays < 7) return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`;
        return date.toLocaleDateString();
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Removed getAuthToken() - using session-based auth instead
    // Session cookies are automatically sent with same-origin requests

    // Public method to manually refresh
    refresh() {
        this.checkNotifications();
    }

    // Cleanup
    destroy() {
        this.stopPolling();
    }
}

// Initialize when DOM is ready
let realtimeNotifications = null;

document.addEventListener('DOMContentLoaded', () => {
    // Only initialize for admin pages
    if (document.body.dataset.userRole === 'admin' || 
        window.location.pathname.startsWith('/admin')) {
        realtimeNotifications = new RealtimeNotifications();
    }
});

// Export for manual control
window.RealtimeNotifications = RealtimeNotifications;


