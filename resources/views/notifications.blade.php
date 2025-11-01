@extends('layouts.app')

@section('content')
<div class="min-h-full bg-gradient-to-br from-blue-50 via-white to-indigo-50">
    <!-- Header Section -->
    <div class="bg-white shadow-sm border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
            
                    <h1 class="text-3xl font-bold text-[#123A7D]">Notifications</h1>
                    <p class="mt-1 text-sm text-gray-600">Manage item claim requests and system notifications</p>
                </div>
                 <div class="flex items-center space-x-3">
                    <div class="bg-[rgba(59,130,246,0.08)] text-[rgba(59,130,246,0.8)] px-3 py-1 rounded-full text-sm font-medium flex items-center">
                        <svg class="w-4 h-4 text-[rgba(59,130,246,0.8)] mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                            </path>
                        </svg>
                        {{ Auth::user()->name }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Pending Approvals</p>
                        <p class="text-3xl font-bold text-amber-600 mt-2">{{ $pendingCount }}</p>
                    </div>
                    <div class="bg-amber-100 p-3 rounded-lg">
                        <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Approved Today</p>
                        <p class="text-3xl font-bold text-green-600 mt-2">{{ $approvedToday }}</p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-lg">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Rejected Today</p>
                        <p class="text-3xl font-bold text-red-600 mt-2">{{ $rejectedToday }}</p>
                    </div>
                    <div class="bg-red-100 p-3 rounded-lg">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Claims</p>
                        <p class="text-3xl font-bold text-blue-600 mt-2">{{ $totalClaims }}</p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-lg">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex-1 max-w-md">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search Claims</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input type="text" id="search" placeholder="Search by item name, claimant name, or description..." class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <select id="status-filter" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Statuses</option>
                        <option value="pending_approval">Pending Approval</option>
                        <option value="claimed">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                    
                    <button id="refresh-btn" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Refresh
                    </button>
                </div>
            </div>
        </div>

        <!-- Notifications List -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-medium text-gray-900 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                    Claim Requests
                </h3>
                <p class="mt-1 text-sm text-gray-600">Review and manage item claim requests from users</p>
            </div>

            <div id="notifications-list" class="divide-y divide-gray-200">
                @forelse($notifications as $notification)
                    <div class="p-6 hover:bg-gray-50 transition-colors duration-200" data-item-id="{{ $notification->id }}" data-status="{{ $notification->status }}">
                        <div class="flex items-start justify-between">
                            <div class="flex items-start space-x-4">
                                <div class="w-12 h-12 {{ $notification->status === 'pending_approval' ? 'bg-amber-100' : ($notification->status === 'claimed' ? 'bg-green-100' : 'bg-red-100') }} rounded-full flex items-center justify-center">
                                    @if($notification->status === 'pending_approval')
                                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    @elseif($notification->status === 'claimed')
                                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    @else
                                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2 mb-2">
                                        <h4 class="text-lg font-semibold text-gray-900">
                                            @if($notification->status === 'pending_approval')
                                                Item Claim Request
                                            @elseif($notification->status === 'claimed')
                                                Item Claim Approved
                                            @else
                                                Item Claim Rejected
                                            @endif
                                        </h4>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            {{ $notification->status === 'pending_approval' ? 'bg-amber-100 text-amber-800' : 
                                               ($notification->status === 'claimed' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800') }}">
                                            @if($notification->status === 'pending_approval')
                                                Pending Approval
                                            @elseif($notification->status === 'claimed')
                                                Approved
                                            @else
                                                Rejected
                                            @endif
                                        </span>
                                    </div>
                                    <p class="text-gray-600 mb-2">
                                        <strong>{{ $notification->claimedBy ? $notification->claimedBy->name : 'Unknown User' }}</strong> 
                                        @if($notification->status === 'pending_approval')
                                            is claiming the item "<strong>{{ $notification->name }}</strong>" (ID: #{{ $notification->id }})
                                        @elseif($notification->status === 'claimed')
                                            's claim for "<strong>{{ $notification->name }}</strong>" has been approved
                                        @else
                                            's claim for "<strong>{{ $notification->name }}</strong>" has been rejected
                                        @endif
                                    </p>
                                    <div class="text-sm text-gray-500 space-y-1">
                                        <p><strong>Item Category:</strong> {{ ucfirst($notification->category) }}</p>
                                        @if($notification->claimed_at)
                                            <p><strong>Claim Date:</strong> {{ \Carbon\Carbon::parse($notification->claimed_at)->format('M d, Y \a\t g:i A') }}</p>
                                        @endif
                                        @if($notification->claimedBy)
                                            <p><strong>Claimant Email:</strong> {{ $notification->claimedBy->email }}</p>
                                        @endif
                                        @if($notification->approved_at)
                                            <p><strong>Approved By:</strong> {{ $notification->approvedBy ? $notification->approvedBy->name : 'Admin' }}</p>
                                            <p><strong>Approval Date:</strong> {{ \Carbon\Carbon::parse($notification->approved_at)->format('M d, Y \a\t g:i A') }}</p>
                                        @endif
                                        @if($notification->rejected_at)
                                            <p><strong>Rejected By:</strong> {{ $notification->rejectedBy ? $notification->rejectedBy->name : 'Admin' }}</p>
                                            <p><strong>Rejection Date:</strong> {{ \Carbon\Carbon::parse($notification->rejected_at)->format('M d, Y \a\t g:i A') }}</p>
                                            @if($notification->rejection_reason)
                                                <p><strong>Reason:</strong> {{ $notification->rejection_reason }}</p>
                                            @endif
                                        @endif
                                    </div>
                                    @if($notification->claim_message)
                                        <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                                            <p class="text-sm text-gray-700">
                                                <strong>Claimant Message:</strong> "{{ $notification->claim_message }}"
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="flex flex-col space-y-2">
                                @if($notification->status === 'pending_approval')
                                    <button onclick="approveClaim({{ $notification->id }})" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors duration-200">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Approve
                                    </button>
                                    <button onclick="rejectClaim({{ $notification->id }})" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors duration-200">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                        Reject
                                    </button>
                                @endif
                                <button onclick="viewItemDetails({{ $notification->id }})" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg font-medium text-sm text-gray-700 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    View Details
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div id="empty-state" class="p-12 text-center">
                        <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No notifications</h3>
                        <p class="text-gray-500">You're all caught up! No pending notifications at the moment.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<script>
// JavaScript for handling notifications functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search');
    const statusFilter = document.getElementById('status-filter');
    const refreshBtn = document.getElementById('refresh-btn');
    const notificationsList = document.getElementById('notifications-list');

    // Search functionality
    searchInput.addEventListener('input', function() {
        filterNotifications();
    });

    // Status filter functionality
    statusFilter.addEventListener('change', function() {
        filterNotifications();
    });

    // Refresh functionality
    refreshBtn.addEventListener('click', function() {
        // Add loading state
        refreshBtn.innerHTML = `
            <svg class="animate-spin w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
            Refreshing...
        `;
        
        // Reload the page
        setTimeout(() => {
            location.reload();
        }, 1000);
    });

    function filterNotifications() {
        const searchTerm = searchInput.value.toLowerCase();
        const statusFilterValue = statusFilter.value;
        const notificationItems = notificationsList.querySelectorAll('.p-6');

        let visibleCount = 0;

        notificationItems.forEach(item => {
            const text = item.textContent.toLowerCase();
            const status = item.dataset.status || '';
            
            const matchesSearch = text.includes(searchTerm);
            const matchesStatus = !statusFilterValue || status === statusFilterValue;

            if (matchesSearch && matchesStatus) {
                item.style.display = 'block';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });

        // Show/hide empty state
        const emptyState = document.getElementById('empty-state');
        if (visibleCount === 0 && emptyState) {
            notificationsList.style.display = 'none';
            emptyState.classList.remove('hidden');
        } else {
            notificationsList.style.display = 'block';
            if (emptyState) emptyState.classList.add('hidden');
        }
    }
});

// Function to approve a claim
function approveClaim(itemId) {
    if (confirm('Are you sure you want to approve this claim?')) {
        fetch(`/notifications/${itemId}/approve`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: data.message
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'An error occurred while processing your request.'
            });
        });
    }
}

// Function to reject a claim
function rejectClaim(itemId) {
    const reason = prompt('Please provide a reason for rejection (optional):');
    
    if (reason !== null) { // User didn't cancel
        fetch(`/notifications/${itemId}/reject`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                reason: reason
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: data.message
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'An error occurred while processing your request.'
            });
        });
    }
}

// Function to view item details
function viewItemDetails(itemId) {
    // Redirect to item details page or show modal
    window.open(`/item/${itemId}`, '_blank');
}
</script>

@endsection 