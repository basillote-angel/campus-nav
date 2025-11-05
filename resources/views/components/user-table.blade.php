<x-ui.card class="overflow-hidden p-0">
    {{-- Mobile: Show scroll hint, Desktop: Auto scroll --}}
    <div class="w-full overflow-x-auto table-scroll-hint" style="-webkit-overflow-scrolling: touch;">
        <table class="w-full divide-y divide-gray-200">
            <thead class="bg-gradient-to-r from-[#123A7D] to-[#10316A]">
                <tr>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider w-12">
                        <input 
                            type="checkbox" 
                            id="select-all-checkbox"
                            class="w-4 h-4 text-[#123A7D] bg-white border-gray-300 rounded focus:ring-[#123A7D] focus:ring-2 cursor-pointer"
                            onclick="toggleSelectAll()"
                        />
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                        Name
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                        Email
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                        Role
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                        Status
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                        Registered
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider bg-gradient-to-r from-[#123A7D] to-[#10316A] sticky right-0 z-10 shadow-lg">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($users as $user)
                    <tr 
                        class="hover:bg-blue-50 cursor-pointer transition-colors duration-200 border-l-4 border-transparent hover:border-[#123A7D]"
                        onclick="showUserDetailsModal({{ $user->id }})"
                        data-user-id="{{ $user->id }}"
                        data-user-name="{{ $user->name }}"
                        data-user-email="{{ $user->email }}"
                        data-user-role="{{ $user->role }}"
                        data-user-created="{{ $user->created_at->format('M d, Y') }}"
                        data-user-created-relative="{{ $user->created_at->diffForHumans() }}"
                    >
                        {{-- Checkbox --}}
                        <td class="px-4 py-3 whitespace-nowrap" onclick="event.stopPropagation()">
                            <input 
                                type="checkbox" 
                                class="user-checkbox w-4 h-4 text-[#123A7D] bg-white border-gray-300 rounded focus:ring-[#123A7D] focus:ring-2 cursor-pointer"
                                value="{{ $user->id }}"
                                onchange="updateBulkActionsUI()"
                            />
                        </td>

                        {{-- Name --}}
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-[#123A7D] to-[#10316A] text-white rounded-xl flex items-center justify-center font-semibold text-sm shadow-sm">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="text-sm font-semibold text-gray-900 truncate max-w-xs" title="{{ $user->name }}">
                                        {{ $user->name }}
                                    </div>
                                </div>
                            </div>
                        </td>

                        {{-- Email --}}
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="text-sm text-gray-600 truncate max-w-xs" title="{{ $user->email }}">
                                {{ $user->email }}
                            </div>
                        </td>

                        {{-- Role --}}
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($user->role === 'admin')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    Admin
                                </span>
                            @elseif($user->role === 'staff')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    Staff
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ ucfirst($user->role) }}
                                </span>
                            @endif
                        </td>

                        {{-- Status --}}
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Active
                            </span>
                        </td>

                        {{-- Registered Date --}}
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="text-sm text-gray-600">
                                {{ $user->created_at->format('M d, Y') }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ $user->created_at->diffForHumans() }}
                            </div>
                        </td>

                        {{-- Actions --}}
                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium bg-white sticky right-0 z-10 shadow-lg" onclick="event.stopPropagation()">
                            <div class="flex items-center gap-2">
                                <button 
                                    type="button"
                                    onclick="event.stopPropagation(); editUserFromTable({{ $user->id }});"
                                    class="text-[#123A7D] hover:text-[#10316A] transition-colors p-2 hover:bg-blue-50 rounded-lg cursor-pointer"
                                    title="Edit User"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                                <form 
                                    id="delete-form-{{ $user->id }}" 
                                    method="POST" 
                                    action="{{ route('users.destroy', $user->id) }}" 
                                    class="inline-block"
                                    onsubmit="event.preventDefault(); confirmDelete({{ $user->id }});"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="button"
                                        onclick="confirmDelete({{ $user->id }})"
                                        class="text-red-600 hover:text-red-700 transition-colors p-2 hover:bg-red-50 rounded-lg"
                                        title="Delete User"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <x-ui.empty-state 
                                title="No users found"
                                description="No users match your search criteria."
                            />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($users->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Showing 
                    <span class="font-medium">{{ $users->firstItem() }}</span>
                    to 
                    <span class="font-medium">{{ $users->lastItem() }}</span>
                    of 
                    <span class="font-medium">{{ $users->total() }}</span>
                    results
                </div>
                <div class="flex gap-2">
                    {{ $users->appends(request()->query())->links('pagination::tailwind') }}
                </div>
            </div>
        </div>
    @endif
</x-ui.card>

{{-- Bulk Actions Toolbar --}}
<div id="bulkActionsToolbar" class="hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-lg z-50 px-6 py-4">
    <div class="max-w-7xl mx-auto flex items-center justify-between">
        <div class="flex items-center gap-4">
            <span id="selectedCount" class="text-sm font-medium text-gray-700">0 items selected</span>
            <button 
                onclick="clearSelection()"
                class="text-sm text-gray-600 hover:text-gray-900 transition-colors"
            >
                Clear selection
            </button>
        </div>
        <div class="flex items-center gap-3">
            <button 
                onclick="bulkDelete()"
                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm font-medium flex items-center gap-2"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
                Delete Selected
            </button>
        </div>
    </div>
</div>

<script>
    // Select All functionality
    function toggleSelectAll() {
        const selectAllCheckbox = document.getElementById('select-all-checkbox');
        const checkboxes = document.querySelectorAll('.user-checkbox');
        
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAllCheckbox.checked;
        });
        
        updateBulkActionsUI();
    }

    // Update bulk actions UI
    function updateBulkActionsUI() {
        const checkboxes = document.querySelectorAll('.user-checkbox');
        const selected = Array.from(checkboxes).filter(cb => cb.checked);
        const toolbar = document.getElementById('bulkActionsToolbar');
        const selectedCount = document.getElementById('selectedCount');
        const selectAllCheckbox = document.getElementById('select-all-checkbox');
        
        if (selected.length > 0) {
            toolbar.classList.remove('hidden');
            selectedCount.textContent = `${selected.length} ${selected.length === 1 ? 'item' : 'items'} selected`;
        } else {
            toolbar.classList.add('hidden');
        }
        
        // Update select all checkbox state
        if (selectAllCheckbox) {
            if (selected.length === 0) {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = false;
            } else if (selected.length === checkboxes.length) {
                selectAllCheckbox.checked = true;
                selectAllCheckbox.indeterminate = false;
            } else {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = true;
            }
        }
    }

    // Clear selection
    function clearSelection() {
        const checkboxes = document.querySelectorAll('.user-checkbox');
        const selectAllCheckbox = document.getElementById('select-all-checkbox');
        
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        }
        
        updateBulkActionsUI();
    }

    // Bulk Delete
    function bulkDelete() {
        const checkboxes = document.querySelectorAll('.user-checkbox:checked');
        const selectedIds = Array.from(checkboxes).map(cb => cb.value);
        
        if (selectedIds.length === 0) {
            return;
        }
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Are you sure?',
                text: `You are about to delete ${selectedIds.length} ${selectedIds.length === 1 ? 'user' : 'users'}. This action cannot be undone!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete them!',
                cancelButtonText: 'Cancel',
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show deleting modal
                    if (typeof showDeleting === 'function') {
                        showDeleting(`Deleting ${selectedIds.length} ${selectedIds.length === 1 ? 'user' : 'users'}...`);
                    }
                    
                    // Delete users one by one (could be optimized with bulk endpoint)
                    const deletePromises = selectedIds.map(id => {
                        return fetch(`/users/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                    });
                    
                    Promise.all(deletePromises).then(() => {
                        // Hide loading modal
                        if (typeof hideLoadingModal === 'function') {
                            hideLoadingModal();
                        }
                        
                        // Show success banner notification
                        if (typeof showNotificationBanner === 'function') {
                            showNotificationBanner(`${selectedIds.length} ${selectedIds.length === 1 ? 'user' : 'users'} deleted successfully.`, 'success', 3000);
                        }
                        
                        // Reload table
                        const searchInput = document.getElementById('search-input');
                        if (searchInput) {
                            const event = new Event('input');
                            searchInput.dispatchEvent(event);
                        } else {
                            window.location.reload();
                        }
                    }).catch(error => {
                        console.error('Error deleting users:', error);
                        
                        // Hide loading modal
                        if (typeof hideLoadingModal === 'function') {
                            hideLoadingModal();
                        }
                        
                        // Show error banner notification
                        if (typeof showNotificationBanner === 'function') {
                            showNotificationBanner(error.message || 'Failed to delete some users. Please try again.', 'error', 5000);
                        }
                    });
                }
            });
        }
    }

    // Delete confirmation for single user
    function confirmDelete(userId) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Are you sure?',
                text: "This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show deleting modal
                    if (typeof showDeleting === 'function') {
                        showDeleting('Deleting user...');
                    }
                    
                    document.getElementById('delete-form-' + userId).submit();
                }
            });
        } else {
            // Fallback if SweetAlert2 is not loaded
            if (confirm('Are you sure you want to delete this user? This action cannot be undone!')) {
                // Show deleting modal
                if (typeof showDeleting === 'function') {
                    showDeleting('Deleting user...');
                }
                
                document.getElementById('delete-form-' + userId).submit();
            }
        }
    }

    // Show User Details Modal
    function showUserDetailsModal(userId) {
        const row = document.querySelector(`tr[data-user-id="${userId}"]`);
        if (!row) return;

        const userName = row.getAttribute('data-user-name');
        const userEmail = row.getAttribute('data-user-email');
        const userRole = row.getAttribute('data-user-role');
        const userCreated = row.getAttribute('data-user-created');
        const userCreatedRelative = row.getAttribute('data-user-created-relative');

        // Get role badge color
        let roleBadgeClass = 'bg-gray-100 text-gray-800';
        let roleBadgeText = userRole.charAt(0).toUpperCase() + userRole.slice(1);
        if (userRole === 'admin') {
            roleBadgeClass = 'bg-purple-100 text-purple-800';
        } else if (userRole === 'staff') {
            roleBadgeClass = 'bg-blue-100 text-blue-800';
        }

        // Create modal HTML
        const modalHTML = `
            <div id="user-details-modal" class="fixed inset-0 bg-black/50 z-[10000] flex items-center justify-center p-4" onclick="if(event.target === this) closeUserDetailsModal()">
                <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
                    {{-- Header --}}
                    <div class="bg-gradient-to-r from-[#123A7D] to-[#10316A] text-white px-6 py-4 rounded-t-xl flex items-center justify-between sticky top-0 z-10">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center font-semibold text-lg">
                                ${userName.charAt(0).toUpperCase()}
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold">User Details</h2>
                                <p class="text-sm text-white/80">View user information</p>
                            </div>
                        </div>
                        <button 
                            type="button"
                            onclick="closeUserDetailsModal()"
                            class="text-white hover:text-gray-200 transition-colors p-2 hover:bg-white/10 rounded-lg"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="p-6">
                        <div class="space-y-6">
                            {{-- User Avatar and Basic Info --}}
                            <div class="flex items-center gap-6 pb-6 border-b border-gray-200">
                                <div class="w-20 h-20 bg-gradient-to-br from-[#123A7D] to-[#10316A] text-white rounded-2xl flex items-center justify-center font-bold text-2xl shadow-lg">
                                    ${userName.charAt(0).toUpperCase()}
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-2xl font-bold text-gray-900 mb-1">${userName}</h3>
                                    <p class="text-gray-600 mb-2">${userEmail}</p>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ${roleBadgeClass}">
                                        ${roleBadgeText}
                                    </span>
                                </div>
                            </div>

                            {{-- User Information --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex items-center gap-2 mb-2">
                                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                        <span class="text-sm font-medium text-gray-700">Full Name</span>
                                    </div>
                                    <p class="text-gray-900 font-semibold">${userName}</p>
                                </div>

                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex items-center gap-2 mb-2">
                                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                        <span class="text-sm font-medium text-gray-700">Email Address</span>
                                    </div>
                                    <p class="text-gray-900 font-semibold">${userEmail}</p>
                                </div>

                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex items-center gap-2 mb-2">
                                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                        </svg>
                                        <span class="text-sm font-medium text-gray-700">Role</span>
                                    </div>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ${roleBadgeClass}">
                                        ${roleBadgeText}
                                    </span>
                                </div>

                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex items-center gap-2 mb-2">
                                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <span class="text-sm font-medium text-gray-700">Registration Date</span>
                                    </div>
                                    <p class="text-gray-900 font-semibold">${userCreated}</p>
                                    <p class="text-xs text-gray-500 mt-1">${userCreatedRelative}</p>
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="flex gap-3 pt-4 border-t border-gray-200">
                                <button 
                                    type="button"
                                    onclick="closeUserDetailsModal(); editUserFromTable(${userId});"
                                    class="flex-1 px-4 py-2 bg-gradient-to-r from-[#123A7D] to-[#10316A] text-white rounded-lg hover:from-[#10316A] hover:to-[#0d2757] transition-colors text-sm font-semibold flex items-center justify-center gap-2 cursor-pointer"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                    Edit User
                                </button>
                                <button 
                                    type="button"
                                    onclick="closeUserDetailsModal(); confirmDelete(${userId});"
                                    class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm font-semibold flex items-center justify-center gap-2"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    Delete User
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Remove existing modal if any
        const existingModal = document.getElementById('user-details-modal');
        if (existingModal) {
            existingModal.remove();
        }

        // Add modal to body
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        document.body.style.overflow = 'hidden';
    }

    function closeUserDetailsModal() {
        const modal = document.getElementById('user-details-modal');
        if (modal) {
            modal.remove();
            document.body.style.overflow = '';
        }
    }

    // Make functions globally accessible
    window.showUserDetailsModal = showUserDetailsModal;
    window.closeUserDetailsModal = closeUserDetailsModal;

    // Edit User from Table
    async function editUserFromTable(userId) {
        try {
            // Show modern loading modal
            if (typeof showLoading === 'function') {
                showLoading('Loading user data...');
            }
            
            // Fetch user data
            const response = await fetch(`{{ url('/users') }}/${userId}/edit`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });
            
            if (!response.ok) {
                const errorText = await response.text();
                console.error('Response error:', response.status, errorText);
                throw new Error(`Failed to load user data (${response.status}): ${errorText.substring(0, 100)}`);
            }
            
            const data = await response.json();
            
            if (!data || !data.user) {
                throw new Error('Invalid response format from server');
            }
            
            const user = data.user;
            
            // Close loading modal
            if (typeof hideLoadingModal === 'function') {
                hideLoadingModal();
            }
            
            // Create and show edit modal
            showEditUserModal(user);
        } catch (error) {
            console.error('Error loading user:', error);
            // Ensure loading modal is closed
            if (typeof hideLoadingModal === 'function') {
                hideLoadingModal();
            }
            
            // Show error banner notification
            if (typeof showNotificationBanner === 'function') {
                showNotificationBanner(error.message || 'Failed to load user data. Please try again.', 'error', 5000);
            } else {
                alert(error.message || 'Failed to load user data. Please try again.');
            }
        }
    }
    
    // Legacy loading modal functions (kept for backward compatibility)
    // Now using modern loading modal from loading-modal component
    function showLoadingModal() {
        if (typeof showLoading === 'function') {
            return showLoading('Loading...');
        }
        // Fallback if modern modal not available
        let modal = document.getElementById('loading-modal');
        if (modal) {
            return modal;
        }
        modal = document.createElement('div');
        modal.id = 'loading-modal';
        modal.className = 'fixed inset-0 bg-black/50 z-[10000] flex items-center justify-center';
        modal.innerHTML = `
            <div class="bg-white rounded-xl shadow-xl p-8">
                <div class="flex items-center gap-4">
                    <svg class="animate-spin h-8 w-8 text-[#123A7D]" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-lg font-medium text-gray-700">Loading...</span>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        document.body.style.overflow = 'hidden';
        return modal;
    }

    function closeLoadingModal(modal) {
        if (typeof hideLoadingModal === 'function') {
            hideLoadingModal();
        } else if (modal && modal.parentNode) {
            modal.remove();
            document.body.style.overflow = '';
        }
    }
    
    // Show Edit User Modal
    function showEditUserModal(user) {
        // Remove existing modal if any
        const existingModal = document.getElementById('edit-user-modal');
        if (existingModal) {
            existingModal.remove();
        }
        
        // Escape HTML to prevent XSS
        const escapeHtml = (text) => {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        };
        
        // Create modal HTML
        const modalHTML = `
            <div id="edit-user-modal" data-user-id="${user.id}" class="fixed inset-0 bg-black/50 z-[10000] flex items-center justify-center p-0 md:p-4" onclick="if(event.target === this) closeEditUserModal()">
                <div class="bg-white rounded-none md:rounded-xl shadow-2xl max-w-2xl w-full h-full md:h-auto md:max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
                    <div class="bg-gradient-to-r from-[#123A7D] to-[#10316A] text-white px-4 py-4 md:px-6 md:py-4 rounded-none md:rounded-t-xl flex items-center justify-between sticky top-0 z-10">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-white/20 rounded-lg">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xl md:text-2xl font-bold">Edit User</h2>
                                <p class="text-xs md:text-sm text-white/80">Update user information and settings</p>
                            </div>
                        </div>
                        <button onclick="closeEditUserModal()" class="text-white hover:text-gray-200 transition-colors p-2 hover:bg-white/10 rounded-lg cursor-pointer">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="p-4 md:p-6">
                        <form id="edit-user-form">
                            @csrf
                            @method('PUT')
                            
                            <div class="space-y-6">
                                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6 border border-blue-100">
                                    <div class="flex items-center gap-2 mb-4">
                                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                        <h3 class="text-lg font-semibold text-gray-900">Basic Information</h3>
                                    </div>
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Full Name <span class="text-red-500">*</span></label>
                                            <input type="text" name="name" id="edit-user-name" value="${escapeHtml(user.name)}" placeholder="Enter full name (e.g., John Doe)" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#123A7D]/50 focus:border-[#123A7D] text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Email Address <span class="text-red-500">*</span></label>
                                            <input type="email" name="email" id="edit-user-email" value="${escapeHtml(user.email)}" placeholder="user@example.com" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#123A7D]/50 focus:border-[#123A7D] text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Role <span class="text-red-500">*</span></label>
                                            <select name="role" id="edit-user-role" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#123A7D]/50 focus:border-[#123A7D] text-sm">
                                                <option value="admin" ${user.role === 'admin' ? 'selected' : ''}>Admin</option>
                                                <option value="staff" ${user.role === 'staff' ? 'selected' : ''}>Staff</option>
                                                <option value="student" ${user.role === 'student' ? 'selected' : ''}>Student</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="bg-white border border-gray-200 rounded-xl p-6">
                                    <div class="flex items-center gap-2 mb-4">
                                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                        </svg>
                                        <h3 class="text-lg font-semibold text-gray-900">Security</h3>
                                    </div>
                                    <div class="space-y-4">
                                        <p class="text-sm text-gray-600 mb-4">Leave password fields empty to keep the current password unchanged.</p>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">New Password <span class="text-gray-500 text-xs">(Optional)</span></label>
                                            <div class="relative">
                                                <input type="password" id="edit-user-password" name="password" placeholder="Enter new password (min. 8 characters)" class="w-full px-4 py-2.5 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#123A7D]/50 focus:border-[#123A7D] text-sm">
                                                <button type="button" onclick="togglePasswordVisibilityEdit('edit-user-password', 'toggle-edit-password')" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700">
                                                    <svg id="eye-icon-edit-password" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                    <svg id="eye-off-icon-edit-password" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.29 3.29m0 0L3 3m3.29 3.29L3 3m3.29 3.29l3.29 3.29m7.532 7.532l3.29 3.29M21 21l-3.29-3.29m0 0L21 21m-3.29-3.29L21 21m-3.29-3.29l-3.29-3.29"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="bg-gray-50 border border-gray-200 rounded-none md:rounded-xl p-4 md:p-6">
                                    <div id="edit-user-errors" class="mb-4 text-red-600 text-sm space-y-1"></div>
                                    <div class="flex flex-col sm:flex-row gap-3">
                                        <button type="button" onclick="closeEditUserModal()" class="flex-1 min-w-[140px] px-6 py-3 bg-white text-gray-700 rounded-lg hover:bg-gray-100 transition-colors text-sm font-semibold text-center flex items-center justify-center gap-2 shadow-sm hover:shadow-md border border-gray-300 cursor-pointer">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                            Cancel
                                        </button>
                                        <button type="submit" id="save-user-btn" class="flex-1 min-w-[140px] px-6 py-3 bg-gradient-to-r from-[#123A7D] to-[#10316A] text-white rounded-lg hover:from-[#10316A] hover:to-[#0d2757] transition-colors text-sm font-semibold flex items-center justify-center gap-2 shadow-sm hover:shadow-md cursor-pointer">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            Save Changes
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        document.body.style.overflow = 'hidden';
        
        // Handle form submission
        const form = document.getElementById('edit-user-form');
        form.addEventListener('submit', handleEditUserSubmit);
    }
    
    // Toggle password visibility for edit user modal
    function togglePasswordVisibilityEdit(inputId, toggleButtonId) {
        const passwordInput = document.getElementById(inputId);
        const eyeIcon = document.getElementById('eye-icon-edit-password');
        const eyeOffIcon = document.getElementById('eye-off-icon-edit-password');
        
        if (passwordInput && eyeIcon && eyeOffIcon) {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.add('hidden');
                eyeOffIcon.classList.remove('hidden');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('hidden');
                eyeOffIcon.classList.add('hidden');
            }
        }
    }
    
    // Handle Edit User Form Submission
    function handleEditUserSubmit(e) {
        e.preventDefault();
        
        const form = e.target;
        const submitBtn = document.getElementById('save-user-btn');
        const errorContainer = document.getElementById('edit-user-errors');
        const formData = new FormData(form);
        
        // Get user ID from modal
        const modal = document.getElementById('edit-user-modal');
        const userId = modal ? modal.getAttribute('data-user-id') : null;
        
        if (!userId) {
            alert('User ID not found');
            return;
        }
        
        // Disable submit button
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Saving...';
        
        // Clear previous errors
        errorContainer.innerHTML = '';
        
        // Show modern loading modal
        if (typeof showSaving === 'function') {
            showSaving('Saving changes...');
        }
        
        fetch(`{{ url('/users') }}/${userId}/update`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || form.querySelector('input[name="_token"]')?.value,
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => {
            return response.json().then(data => {
                return { status: response.status, data: data };
            });
        })
        .then(({ status, data }) => {
            // Hide loading modal
            if (typeof hideLoadingModal === 'function') {
                hideLoadingModal();
            }
            
            if (data.success) {
                // Show success banner notification
                if (typeof showNotificationBanner === 'function') {
                    showNotificationBanner(data.message || 'User updated successfully.', 'success', 3000);
                }
                
                // Close modal and reload after a short delay
                setTimeout(() => {
                    closeEditUserModal();
                    window.location.reload();
                }, 500);
            } else {
                // Handle validation errors from backend
                let errorMessages = [];
                
                if (status === 422 && data.errors) {
                    // Laravel validation errors
                    Object.keys(data.errors).forEach(key => {
                        if (Array.isArray(data.errors[key])) {
                            data.errors[key].forEach(msg => errorMessages.push(msg));
                        } else {
                            errorMessages.push(data.errors[key]);
                        }
                    });
                } else if (data.message) {
                    errorMessages.push(data.message);
                } else {
                    errorMessages.push('Failed to update user. Please try again.');
                }
                
                if (errorMessages.length > 0) {
                    errorContainer.innerHTML = '<div class="space-y-1">' + errorMessages.map(err => `<p>• ${err}</p>`).join('') + '</div>';
                    
                    // Also show error banner notification
                    if (typeof showNotificationBanner === 'function') {
                        const errorMessage = errorMessages.join(', ');
                        showNotificationBanner(errorMessage, 'error', 5000);
                    }
                } else {
                    // Show generic error banner
                    if (typeof showNotificationBanner === 'function') {
                        showNotificationBanner('Failed to update user. Please try again.', 'error', 5000);
                    }
                }
            }
        })
        .catch(error => {
            console.error('Error updating user:', error);
            
            // Hide loading modal
            if (typeof hideLoadingModal === 'function') {
                hideLoadingModal();
            }
            
            // Show error in container if not already shown
            if (errorContainer && !errorContainer.innerHTML) {
                errorContainer.innerHTML = `<p class="text-red-600">${error.message || 'An error occurred. Please try again.'}</p>`;
            }
            
            // Show error banner notification
            if (typeof showNotificationBanner === 'function') {
                showNotificationBanner(error.message || 'Failed to update user. Please try again.', 'error', 5000);
            }
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = `
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Save Changes
            `;
        });
    }
    
    // Close Edit User Modal
    function closeEditUserModal() {
        const modal = document.getElementById('edit-user-modal');
        if (modal) {
            modal.remove();
            document.body.style.overflow = '';
        }
    }
    
    // Make functions globally accessible
    window.editUserFromTable = editUserFromTable;
    window.closeEditUserModal = closeEditUserModal;
    window.togglePasswordVisibilityEdit = togglePasswordVisibilityEdit;

    // Initialize on load
    document.addEventListener('DOMContentLoaded', function() {
        updateBulkActionsUI();
    });
</script>
