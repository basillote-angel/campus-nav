@extends('layouts.app')

@section('content')

<div class="min-h-full bg-gradient-to-br from-blue-50 via-white to-indigo-50">
    {{-- Page Header --}}
    <x-ui.page-header 
        title="Users Management"
        description="Manage admin users and mobile app users"
    >
        @if(($tab ?? 'admin') === 'admin')
            <x-ui.button-primary 
                id="add-user-button"
                type="button"
                variant="primary" 
                size="lg" 
                iconLeft="M12 4v16m8-8H4"
                class="relative z-10"
            >
                Add New Admin User
            </x-ui.button-primary>
        @endif
    </x-ui.page-header>

    {{-- Main Content Area --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-8">
        {{-- Success/Error Messages - Converted to banner notifications via JavaScript --}}
        @if(session('success'))
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    if (typeof showNotificationBanner === 'function') {
                        showNotificationBanner('{{ session('success') }}', 'success', 3000);
                    }
                });
            </script>
        @elseif(session('error'))
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    if (typeof showNotificationBanner === 'function') {
                        showNotificationBanner('{{ session('error') }}', 'error', 4000);
                    }
                });
            </script>
        @endif

        {{-- Tab Navigation with Search --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
            {{-- Search Section --}}
            <div class="p-4 border-b border-gray-200">
                <input 
                    type="text" 
                    id="search-input"
                    value="{{ request()->search }}" 
                    placeholder="Search users by name or email..." 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#123A7D]/50 focus:border-[#123A7D] text-sm"
                />
            </div>
            
            {{-- Tab Navigation --}}
            <div class="border-b border-gray-200">
                <nav class="flex -mb-px" aria-label="Tabs">
                    <a 
                        href="{{ route('users', ['tab' => 'admin'] + request()->except(['tab', 'page'])) }}" 
                        class="flex-1 py-4 px-6 text-center border-b-2 font-medium text-sm transition-colors {{ ($tab ?? 'admin') === 'admin' ? 'border-[#123A7D] text-[#123A7D]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                    >
                        <div class="flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                            Admin Users
                        </div>
                    </a>
                    <a 
                        href="{{ route('users', ['tab' => 'mobile'] + request()->except(['tab', 'page'])) }}" 
                        class="flex-1 py-4 px-6 text-center border-b-2 font-medium text-sm transition-colors {{ ($tab ?? 'admin') === 'mobile' ? 'border-[#123A7D] text-[#123A7D]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                    >
                        <div class="flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                            Mobile Users
                        </div>
                    </a>
                </nav>
            </div>
        </div>

        {{-- Users Table --}}
        <div id="users-table">
            @include('components.user-table', ['users' => $users, 'tab' => $tab ?? 'admin'])
        </div>
    </div>
</div>

{{-- Add User Modal --}}
<div id="add-user-modal" class="fixed inset-0 bg-black/50 z-[10000] flex items-center justify-center p-4 hidden" onclick="if(event.target === this) hideModal('add-user-modal')">
    <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
        {{-- Header --}}
        <div class="bg-gradient-to-r from-[#123A7D] to-[#10316A] text-white px-6 py-4 rounded-t-xl flex items-center justify-between sticky top-0 z-10">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-white/20 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-bold">Add New User</h2>
                    <p class="text-sm text-white/80">Create a new admin user</p>
                </div>
            </div>
            <button 
                type="button"
                onclick="hideModal('add-user-modal')"
                class="text-white hover:text-gray-200 transition-colors p-2 hover:bg-white/10 rounded-lg"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <div class="p-6">
            <form id="add-user-form">
                @csrf

                <div class="space-y-6">
                    {{-- Basic Information Section --}}
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6 border border-blue-100">
                        <div class="flex items-center gap-2 mb-4">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <h3 class="text-lg font-semibold text-gray-900">Basic Information</h3>
                        </div>

                        <div class="space-y-4">
                            {{-- Name --}}
                            <div>
                                <x-ui.input
                                    label="Full Name"
                                    name="name"
                                    id="add-user-name"
                                    placeholder="Enter full name (e.g., John Doe)"
                                    required
                                />
                                <p class="mt-1 text-xs text-gray-500">Enter the full name of the admin user</p>
                            </div>

                            {{-- Email --}}
                            <div>
                                <x-ui.input
                                    label="Email Address"
                                    name="email"
                                    id="add-user-email"
                                    type="email"
                                    placeholder="admin@example.com"
                                    required
                                />
                                <p class="mt-1 text-xs text-gray-500" id="email-hint">Must be a valid email address</p>
                                <p class="mt-1 text-xs text-red-600 hidden" id="email-error">This email is already registered</p>
                            </div>
                        </div>
                    </div>

                    {{-- Security Section --}}
                    <div class="bg-white border border-gray-200 rounded-xl p-6">
                        <div class="flex items-center gap-2 mb-4">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            <h3 class="text-lg font-semibold text-gray-900">Security</h3>
                        </div>
                        <div class="space-y-4">
                            {{-- Password --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Password
                                    <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input 
                                        id="add-user-password"
                                        name="password"
                                        type="password" 
                                        placeholder="Enter a strong password (min. 8 characters)" 
                                        class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#123A7D]/50 focus:border-[#123A7D] text-sm"
                                        required
                                        minlength="8"
                                    />
                                    <button 
                                        type="button"
                                        id="toggle-password"
                                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700 focus:outline-none"
                                        onclick="togglePasswordVisibility('add-user-password', 'toggle-password')"
                                    >
                                        <svg id="eye-icon-password" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        <svg id="eye-off-icon-password" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.29 3.29m0 0L3 3m3.29 3.29L3 3m3.29 3.29l3.29 3.29m7.532 7.532l3.29 3.29M21 21l-3.29-3.29m0 0L21 21m-3.29-3.29L21 21m-3.29-3.29l-3.29-3.29"></path>
                                        </svg>
                                    </button>
                                </div>
                                {{-- Password Strength Indicator --}}
                                <div class="mt-2">
                                    <div class="flex items-center gap-2 mb-1">
                                        <div id="password-strength-bar" class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                                            <div id="password-strength-fill" class="h-full transition-all duration-300" style="width: 0%"></div>
                                        </div>
                                        <span id="password-strength-text" class="text-xs font-medium text-gray-500">Weak</span>
                                    </div>
                                    <div class="text-xs text-gray-500 space-y-1">
                                        <p id="password-requirement-length" class="text-gray-400">• At least 8 characters</p>
                                        <p id="password-requirement-uppercase" class="text-gray-400">• One uppercase letter</p>
                                        <p id="password-requirement-lowercase" class="text-gray-400">• One lowercase letter</p>
                                        <p id="password-requirement-number" class="text-gray-400">• One number</p>
                                        <p id="password-requirement-special" class="text-gray-400">• One special character</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Confirm Password --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Confirm Password
                                    <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input 
                                        id="confirm_password"
                                        type="password" 
                                        placeholder="Re-enter password to confirm" 
                                        class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#123A7D]/50 focus:border-[#123A7D] text-sm"
                                        required
                                    />
                                    <button 
                                        type="button"
                                        id="toggle-confirm-password"
                                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700 focus:outline-none"
                                        onclick="togglePasswordVisibility('confirm_password', 'toggle-confirm-password')"
                                    >
                                        <svg id="eye-icon-confirm" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        <svg id="eye-off-icon-confirm" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.29 3.29m0 0L3 3m3.29 3.29L3 3m3.29 3.29l3.29 3.29m7.532 7.532l3.29 3.29M21 21l-3.29-3.29m0 0L21 21m-3.29-3.29L21 21m-3.29-3.29l-3.29-3.29"></path>
                                        </svg>
                                    </button>
                                </div>
                                <p id="password-match-indicator" class="mt-1 text-xs hidden"></p>
                            </div>
                        </div>
                    </div>

                    {{-- Role --}}
                    <input type="hidden" name="role" value="admin" />

                    {{-- Actions Section --}}
                    <div class="bg-gray-50 border border-gray-200 rounded-xl p-6">
                        <div id="form-errors" class="mb-4 text-red-600 text-sm space-y-1"></div>
                        <div class="flex flex-wrap gap-3">
                            <button 
                                type="button"
                                onclick="hideModal('add-user-modal')"
                                class="flex-1 min-w-[140px] px-6 py-3 bg-white text-gray-700 rounded-lg hover:bg-gray-100 transition-colors text-sm font-semibold text-center flex items-center justify-center gap-2 shadow-sm hover:shadow-md border border-gray-300"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Cancel
                            </button>
                            <button 
                                type="submit"
                                class="flex-1 min-w-[140px] px-6 py-3 bg-gradient-to-r from-[#123A7D] to-[#10316A] text-white rounded-lg hover:from-[#10316A] hover:to-[#0d2757] transition-colors text-sm font-semibold flex items-center justify-center gap-2 shadow-sm hover:shadow-md"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Add User
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Helper functions
    function showModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }
    }

    function hideModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
        }
    }

    function showAddUserModal() {
        try {
            showModal('add-user-modal');
            const form = document.getElementById('add-user-form');
            if (form) {
                form.reset();
            }
            
            // Reset password visibility to hidden
            const passwordInput = document.getElementById('add-user-password');
            const confirmPasswordInput = document.getElementById('confirm_password');
            if (passwordInput) {
                passwordInput.type = 'password';
            }
            if (confirmPasswordInput) {
                confirmPasswordInput.type = 'password';
            }
            
            // Reset eye icons to show (password is hidden)
            const eyeIconPassword = document.getElementById('eye-icon-password');
            const eyeOffIconPassword = document.getElementById('eye-off-icon-password');
            if (eyeIconPassword) eyeIconPassword.classList.remove('hidden');
            if (eyeOffIconPassword) eyeOffIconPassword.classList.add('hidden');
            
            const eyeIconConfirm = document.getElementById('eye-icon-confirm');
            const eyeOffIconConfirm = document.getElementById('eye-off-icon-confirm');
            if (eyeIconConfirm) eyeIconConfirm.classList.remove('hidden');
            if (eyeOffIconConfirm) eyeOffIconConfirm.classList.add('hidden');
            
            const formErrors = document.getElementById('form-errors');
            if (formErrors) {
                formErrors.innerHTML = '';
            }
            
            // Reset password strength indicators (if functions exist)
            if (typeof resetPasswordStrength === 'function') {
                resetPasswordStrength();
            }
            
            if (typeof resetPasswordMatch === 'function') {
                resetPasswordMatch();
            }
            
            // Reset email error
            const emailError = document.getElementById('email-error');
            const emailHint = document.getElementById('email-hint');
            if (emailError) {
                emailError.classList.add('hidden');
            }
            if (emailHint) {
                emailHint.classList.remove('hidden');
            }
        } catch (error) {
            console.error('Error opening add user modal:', error);
            // Fallback: just show the modal
            const modal = document.getElementById('add-user-modal');
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.style.overflow = 'hidden';
            }
        }
    }
    
    // Make function globally accessible
    window.showAddUserModal = showAddUserModal;
    window.hideModal = hideModal;
    
    // Toggle password visibility
    function togglePasswordVisibility(inputId, toggleButtonId) {
        const passwordInput = document.getElementById(inputId);
        const toggleButton = document.getElementById(toggleButtonId);
        
        if (!passwordInput || !toggleButton) {
            return;
        }
        
        // Determine which icons to use based on button ID
        const isPassword = toggleButtonId === 'toggle-password';
        const eyeIcon = document.getElementById(isPassword ? 'eye-icon-password' : 'eye-icon-confirm');
        const eyeOffIcon = document.getElementById(isPassword ? 'eye-off-icon-password' : 'eye-off-icon-confirm');
        
        if (passwordInput.type === 'password') {
            // Show password
            passwordInput.type = 'text';
            if (eyeIcon) eyeIcon.classList.add('hidden');
            if (eyeOffIcon) eyeOffIcon.classList.remove('hidden');
        } else {
            // Hide password
            passwordInput.type = 'password';
            if (eyeIcon) eyeIcon.classList.remove('hidden');
            if (eyeOffIcon) eyeOffIcon.classList.add('hidden');
        }
    }
    
    // Make toggle function globally accessible
    window.togglePasswordVisibility = togglePasswordVisibility;

    // Set up button click handler - multiple attempts to ensure it works
    function setupAddUserButton() {
        const addUserButton = document.getElementById('add-user-button');
        if (addUserButton) {
            // Clear any existing event listeners by removing and re-adding the listener
            const clickHandler = function(e) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                
                console.log('Add user button clicked - opening modal');
                
                try {
                    if (typeof showAddUserModal === 'function') {
                        showAddUserModal();
                    } else {
                        // Direct fallback: show modal manually
                        const modal = document.getElementById('add-user-modal');
                        if (modal) {
                            modal.classList.remove('hidden');
                            modal.classList.add('flex');
                            document.body.style.overflow = 'hidden';
                            console.log('Modal opened via fallback');
                        } else {
                            console.error('Modal not found');
                        }
                    }
                } catch (error) {
                    console.error('Error opening modal:', error);
                    // Last resort: try to show modal directly
                    const modal = document.getElementById('add-user-modal');
                    if (modal) {
                        modal.style.display = 'flex';
                        modal.classList.remove('hidden');
                        document.body.style.overflow = 'hidden';
                    }
                }
                
                return false;
            };
            
            // Remove old listener if exists
            addUserButton.removeEventListener('click', clickHandler);
            // Add new listener
            addUserButton.addEventListener('click', clickHandler, true); // Use capture phase
            
            // Ensure button properties
            addUserButton.style.pointerEvents = 'auto';
            addUserButton.style.cursor = 'pointer';
            addUserButton.style.userSelect = 'none';
            addUserButton.setAttribute('tabindex', '0');
            
            console.log('Add user button configured successfully');
            return true;
        } else {
            console.log('Add user button not found yet');
            return false;
        }
    }

    // Multiple attempts to set up the button
    // Immediate attempt
    setupAddUserButton();
    
    // On DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setupAddUserButton();
        });
    } else {
        setupAddUserButton();
    }

    // Additional attempts with delays (in case of dynamic content)
    setTimeout(setupAddUserButton, 100);
    setTimeout(setupAddUserButton, 300);
    setTimeout(setupAddUserButton, 500);
    setTimeout(setupAddUserButton, 1000);

    // Search functionality
    const searchInput = document.getElementById('search-input');
    const usersTable = document.getElementById('users-table');

    let debounceTimeout;
    const debounce = (func, delay = 500) => {
        clearTimeout(debounceTimeout);
        debounceTimeout = setTimeout(() => func(), delay);
    };

    const fetchUsers = () => {
        const search = searchInput.value;
        const tab = '{{ $tab ?? "admin" }}';
        const query = new URLSearchParams({ search, tab }).toString();

        fetch(`{{ route('users') }}?${query}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(data => {
            usersTable.innerHTML = data;
            // Re-initialize event listeners after table update
            initializeTableScripts();
        })
        .catch(error => console.error('Error fetching users:', error));
    };

    searchInput.addEventListener('input', () => debounce(fetchUsers));

    // Password Strength Checker
    function checkPasswordStrength(password) {
        if (!password) {
            return { strength: 0, text: 'Weak', color: 'bg-gray-400', requirements: {} };
        }

        const requirements = {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            number: /[0-9]/.test(password),
            special: /[^A-Za-z0-9]/.test(password)
        };

        const metCount = Object.values(requirements).filter(Boolean).length;
        let strength = 0;
        let text = 'Weak';
        let color = 'bg-red-500';

        if (metCount <= 2) {
            strength = 20;
            text = 'Weak';
            color = 'bg-red-500';
        } else if (metCount === 3) {
            strength = 50;
            text = 'Fair';
            color = 'bg-yellow-500';
        } else if (metCount === 4) {
            strength = 75;
            text = 'Good';
            color = 'bg-blue-500';
        } else {
            strength = 100;
            text = 'Strong';
            color = 'bg-green-500';
        }

        return { strength, text, color, requirements };
    }

    function updatePasswordStrength() {
        const password = document.getElementById('add-user-password').value;
        const strength = checkPasswordStrength(password);
        
        const fill = document.getElementById('password-strength-fill');
        const text = document.getElementById('password-strength-text');
        
        fill.style.width = strength.strength + '%';
        fill.className = 'h-full transition-all duration-300 ' + strength.color;
        text.textContent = strength.text;
        text.className = 'text-xs font-medium ' + (strength.strength >= 75 ? 'text-green-600' : strength.strength >= 50 ? 'text-yellow-600' : 'text-red-600');

        // Update requirement indicators
        Object.keys(strength.requirements).forEach(key => {
            const el = document.getElementById('password-requirement-' + key);
            if (el) {
                if (strength.requirements[key]) {
                    el.classList.remove('text-gray-400');
                    el.classList.add('text-green-600');
                    el.textContent = el.textContent.replace('•', '✓');
                } else {
                    el.classList.remove('text-green-600');
                    el.classList.add('text-gray-400');
                    el.textContent = el.textContent.replace('✓', '•');
                }
            }
        });
    }

    function resetPasswordStrength() {
        const fill = document.getElementById('password-strength-fill');
        const text = document.getElementById('password-strength-text');
        fill.style.width = '0%';
        fill.className = 'h-full transition-all duration-300';
        text.textContent = 'Weak';
        text.className = 'text-xs font-medium text-gray-500';

        // Reset requirement indicators
        ['length', 'uppercase', 'lowercase', 'number', 'special'].forEach(key => {
            const el = document.getElementById('password-requirement-' + key);
            if (el) {
                el.classList.remove('text-green-600');
                el.classList.add('text-gray-400');
                el.textContent = el.textContent.replace('✓', '•');
            }
        });
    }

    function checkPasswordMatch() {
        const password = document.getElementById('add-user-password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        const indicator = document.getElementById('password-match-indicator');

        if (!confirmPassword) {
            indicator.classList.add('hidden');
            return false;
        }

        indicator.classList.remove('hidden');

        if (password === confirmPassword && password.length > 0) {
            indicator.textContent = '✓ Passwords match';
            indicator.className = 'mt-1 text-xs text-green-600';
            return true;
        } else {
            indicator.textContent = '✗ Passwords do not match';
            indicator.className = 'mt-1 text-xs text-red-600';
            return false;
        }
    }

    function resetPasswordMatch() {
        const indicator = document.getElementById('password-match-indicator');
        indicator.classList.add('hidden');
    }

    // Email validation
    function validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    // Check if email exists (debounced)
    let emailCheckTimeout;
    function checkEmailExists(email) {
        clearTimeout(emailCheckTimeout);
        
        if (!email || !validateEmail(email)) {
            document.getElementById('email-error').classList.add('hidden');
            document.getElementById('email-hint').classList.remove('hidden');
            return;
        }

        emailCheckTimeout = setTimeout(() => {
            // In a real implementation, you'd check against the database via AJAX
            // For now, we'll skip this and validate on submit
            document.getElementById('email-hint').classList.remove('hidden');
        }, 500);
    }

    // Attach event listeners when DOM is ready
    function initializeAddUserForm() {
        const passwordInput = document.getElementById('add-user-password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const emailInput = document.getElementById('add-user-email');

        if (passwordInput) {
            passwordInput.addEventListener('input', updatePasswordStrength);
        }

        if (confirmPasswordInput) {
            confirmPasswordInput.addEventListener('input', checkPasswordMatch);
        }

        if (emailInput) {
            emailInput.addEventListener('blur', function() {
                const email = emailInput.value;
                if (email && !validateEmail(email)) {
                    const emailError = document.getElementById('email-error');
                    const emailHint = document.getElementById('email-hint');
                    if (emailError) {
                        emailError.textContent = 'Please enter a valid email address';
                        emailError.classList.remove('hidden');
                    }
                    if (emailHint) {
                        emailHint.classList.add('hidden');
                    }
                } else {
                    const emailError = document.getElementById('email-error');
                    const emailHint = document.getElementById('email-hint');
                    if (emailError) {
                        emailError.classList.add('hidden');
                    }
                    if (emailHint) {
                        emailHint.classList.remove('hidden');
                    }
                }
            });
        }
    }

    // Add User Form Handler
    const addUserForm = document.getElementById('add-user-form');
    if (addUserForm) {
        addUserForm.addEventListener('submit', (e) => {
            e.preventDefault();

            const errorContainer = document.getElementById('form-errors');
            errorContainer.innerHTML = '';

            // Get form values
            const name = document.getElementById('add-user-name').value.trim();
            const email = document.getElementById('add-user-email').value.trim();
            const password = document.getElementById('add-user-password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            // Validation
            const errors = [];

            if (!name || name.length < 2) {
                errors.push('Full name must be at least 2 characters long.');
            }

            if (!email || !validateEmail(email)) {
                errors.push('Please enter a valid email address.');
            }

            if (!password || password.length < 8) {
                errors.push('Password must be at least 8 characters long.');
            }

            // Check password strength
            const strength = checkPasswordStrength(password);
            if (strength.strength < 50) {
                errors.push('Password is too weak. Please use a stronger password.');
            }

            if (password !== confirmPassword) {
                errors.push('Passwords do not match.');
            }

            if (errors.length > 0) {
                errorContainer.innerHTML = '<div class="space-y-1">' + errors.map(err => '<p>• ' + err + '</p>').join('') + '</div>';
                return;
            }

            const formData = new FormData(addUserForm);
            const submitBtn = addUserForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="flex items-center gap-2"><svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Adding...</span>';

            // Show modern loading modal
            if (typeof showSaving === 'function') {
                showSaving('Adding user...', { subMessage: 'Please wait while we process your request' });
            }

            fetch("{{ route('users.store') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
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
                        showNotificationBanner('Admin user added successfully.', 'success', 3000);
                    }
                    
                    hideModal('add-user-modal');
                    addUserForm.reset();
                    resetPasswordStrength();
                    resetPasswordMatch();
                    
                    // Refresh the table if on admin tab
                    const currentTab = '{{ $tab ?? "admin" }}';
                    if (currentTab === 'admin') {
                        fetchUsers();
                    } else {
                        window.location.reload();
                    }
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
                        errorMessages.push('Failed to create user. Please try again.');
                    }
                    
                    if (errorMessages.length > 0) {
                        errorContainer.innerHTML = '<div class="space-y-1">' + errorMessages.map(err => '<p>• ' + err + '</p>').join('') + '</div>';
                        
                        // Also show error banner notification
                        if (typeof showNotificationBanner === 'function') {
                            const errorMessage = errorMessages.join(', ');
                            showNotificationBanner(errorMessage, 'error', 5000);
                        }
                    } else {
                        // Show generic error banner
                        if (typeof showNotificationBanner === 'function') {
                            showNotificationBanner('Failed to create user. Please try again.', 'error', 5000);
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                
                // Hide loading modal
                if (typeof hideLoadingModal === 'function') {
                    hideLoadingModal();
                }
                
                errorContainer.innerHTML = '<p>• An error occurred. Please try again.</p>';
                
                // Show error banner notification
                if (typeof showNotificationBanner === 'function') {
                    showNotificationBanner(error.message || 'An error occurred. Please try again.', 'error', 5000);
                }
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    }

    // Initialize table scripts (for delete confirmation, etc.)
    function initializeTableScripts() {
        // Delete confirmation scripts are in user-table component
    }

    // Initialize on load
    document.addEventListener('DOMContentLoaded', function() {
        initializeTableScripts();
        initializeAddUserForm();
        
        // Button setup is now handled by setupAddUserButton() function above
    });
</script>
@endpush
