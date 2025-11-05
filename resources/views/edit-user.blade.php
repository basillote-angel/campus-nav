@extends('layouts.app')

@section('content')

<div class="min-h-full bg-gradient-to-br from-blue-50 via-white to-indigo-50">
    {{-- Page Header --}}
    <div class="bg-white border-b border-gray-200 mb-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center gap-4">
                <a 
                    href="{{ route('users') }}" 
                    class="text-gray-600 hover:text-[#123A7D] transition-colors p-2 hover:bg-gray-100 rounded-lg"
                    title="Back to Users"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"></path>
                    </svg>
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-[#123A7D]">Edit User</h1>
                    <p class="mt-1 text-sm text-gray-600">Update user information and settings</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content Area --}}
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 pb-8">
        {{-- Success/Error Messages --}}
        @if(session('success'))
            <x-ui.alert type="success" dismissible="true" class="mb-6">
                {{ session('success') }}
            </x-ui.alert>
        @endif

        @if($errors->any())
            <x-ui.alert type="error" dismissible="true" class="mb-6">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        @endif

        {{-- Edit User Form Card --}}
        <x-ui.card class="mb-6">
            <form id="edit-user-form" action="{{ route('users.update', $user->id) }}" method="POST">
                @csrf
                @method('PUT')

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
                                    id="edit-user-name"
                                    value="{{ old('name', $user->name) }}"
                                    placeholder="Enter full name (e.g., John Doe)"
                                    required
                                />
                                <p class="mt-1 text-xs text-gray-500">Enter the full name of the user</p>
                            </div>

                            {{-- Email --}}
                            <div>
                                <x-ui.input
                                    label="Email Address"
                                    name="email"
                                    id="edit-user-email"
                                    type="email"
                                    value="{{ old('email', $user->email) }}"
                                    placeholder="user@example.com"
                                    required
                                />
                                <p class="mt-1 text-xs text-gray-500" id="email-hint">Must be a valid email address</p>
                                <p class="mt-1 text-xs text-red-600 hidden" id="email-error"></p>
                            </div>

                            {{-- Role --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Role
                                    <span class="text-red-500">*</span>
                                </label>
                                <select 
                                    name="role" 
                                    id="edit-user-role"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#123A7D]/50 focus:border-[#123A7D] text-sm"
                                    required
                                >
                                    <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                                    <option value="staff" {{ old('role', $user->role) === 'staff' ? 'selected' : '' }}>Staff</option>
                                    <option value="student" {{ old('role', $user->role) === 'student' ? 'selected' : '' }}>Student</option>
                                </select>
                                <p class="mt-1 text-xs text-gray-500">Select the user's role in the system</p>
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
                            <p class="text-sm text-gray-600 mb-4">Leave password fields empty to keep the current password unchanged.</p>

                            {{-- Password --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    New Password
                                    <span class="text-gray-500 text-xs">(Optional)</span>
                                </label>
                                <div class="relative">
                                    <input 
                                        id="edit-user-password"
                                        name="password"
                                        type="password" 
                                        placeholder="Enter new password (min. 8 characters)" 
                                        class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#123A7D]/50 focus:border-[#123A7D] text-sm"
                                        minlength="8"
                                    />
                                    <button 
                                        type="button"
                                        id="toggle-edit-password"
                                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700 focus:outline-none"
                                        onclick="togglePasswordVisibility('edit-user-password', 'toggle-edit-password')"
                                    >
                                        <svg id="eye-icon-edit-password" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        <svg id="eye-off-icon-edit-password" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.29 3.29m0 0L3 3m3.29 3.29L3 3m3.29 3.29l3.29 3.29m7.532 7.532l3.29 3.29M21 21l-3.29-3.29m0 0L21 21m-3.29-3.29L21 21m-3.29-3.29l-3.29-3.29"></path>
                                        </svg>
                                    </button>
                                </div>
                                {{-- Password Strength Indicator --}}
                                <div class="mt-2">
                                    <div class="flex items-center gap-2 mb-1">
                                        <div id="password-strength-bar-edit" class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                                            <div id="password-strength-fill-edit" class="h-full transition-all duration-300" style="width: 0%"></div>
                                        </div>
                                        <span id="password-strength-text-edit" class="text-xs font-medium text-gray-500">Weak</span>
                                    </div>
                                    <div class="text-xs text-gray-500 space-y-1">
                                        <p id="password-requirement-length-edit" class="text-gray-400">• At least 8 characters</p>
                                        <p id="password-requirement-uppercase-edit" class="text-gray-400">• One uppercase letter</p>
                                        <p id="password-requirement-lowercase-edit" class="text-gray-400">• One lowercase letter</p>
                                        <p id="password-requirement-number-edit" class="text-gray-400">• One number</p>
                                        <p id="password-requirement-special-edit" class="text-gray-400">• One special character</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Confirm Password --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Confirm New Password
                                    <span class="text-gray-500 text-xs">(Optional)</span>
                                </label>
                                <div class="relative">
                                    <input 
                                        id="confirm_password_edit"
                                        type="password" 
                                        placeholder="Re-enter new password to confirm" 
                                        class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#123A7D]/50 focus:border-[#123A7D] text-sm"
                                    />
                                    <button 
                                        type="button"
                                        id="toggle-confirm-password-edit"
                                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700 focus:outline-none"
                                        onclick="togglePasswordVisibility('confirm_password_edit', 'toggle-confirm-password-edit')"
                                    >
                                        <svg id="eye-icon-confirm-edit" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        <svg id="eye-off-icon-confirm-edit" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.29 3.29m0 0L3 3m3.29 3.29L3 3m3.29 3.29l3.29 3.29m7.532 7.532l3.29 3.29M21 21l-3.29-3.29m0 0L21 21m-3.29-3.29L21 21m-3.29-3.29l-3.29-3.29"></path>
                                        </svg>
                                    </button>
                                </div>
                                <p id="password-match-indicator-edit" class="mt-1 text-xs hidden"></p>
                            </div>
                        </div>
                    </div>

                    {{-- Actions Section --}}
                    <div class="bg-gray-50 border border-gray-200 rounded-xl p-6">
                        <div id="form-errors" class="mb-4 text-red-600 text-sm space-y-1"></div>
                        <div class="flex flex-wrap gap-3">
                            <a 
                                href="{{ route('users') }}"
                                class="flex-1 min-w-[140px] px-6 py-3 bg-white text-gray-700 rounded-lg hover:bg-gray-100 transition-colors text-sm font-semibold text-center flex items-center justify-center gap-2 shadow-sm hover:shadow-md border border-gray-300"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Cancel
                            </a>
                            <button 
                                type="submit"
                                class="flex-1 min-w-[140px] px-6 py-3 bg-gradient-to-r from-[#123A7D] to-[#10316A] text-white rounded-lg hover:from-[#10316A] hover:to-[#0d2757] transition-colors text-sm font-semibold flex items-center justify-center gap-2 shadow-sm hover:shadow-md"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Save Changes
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </x-ui.card>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Password Strength Checker (same as add user)
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

    function updatePasswordStrengthEdit() {
        const password = document.getElementById('edit-user-password').value;
        const strength = checkPasswordStrength(password);
        
        const fill = document.getElementById('password-strength-fill-edit');
        const text = document.getElementById('password-strength-text-edit');
        
        if (fill && text) {
            fill.style.width = strength.strength + '%';
            fill.className = 'h-full transition-all duration-300 ' + strength.color;
            text.textContent = strength.text;
            text.className = 'text-xs font-medium ' + (strength.strength >= 75 ? 'text-green-600' : strength.strength >= 50 ? 'text-yellow-600' : 'text-red-600');

            // Update requirement indicators
            Object.keys(strength.requirements).forEach(key => {
                const el = document.getElementById('password-requirement-' + key + '-edit');
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
    }

    function resetPasswordStrengthEdit() {
        const fill = document.getElementById('password-strength-fill-edit');
        const text = document.getElementById('password-strength-text-edit');
        if (fill && text) {
            fill.style.width = '0%';
            fill.className = 'h-full transition-all duration-300';
            text.textContent = 'Weak';
            text.className = 'text-xs font-medium text-gray-500';

            ['length', 'uppercase', 'lowercase', 'number', 'special'].forEach(key => {
                const el = document.getElementById('password-requirement-' + key + '-edit');
                if (el) {
                    el.classList.remove('text-green-600');
                    el.classList.add('text-gray-400');
                    el.textContent = el.textContent.replace('✓', '•');
                }
            });
        }
    }

    function checkPasswordMatchEdit() {
        const password = document.getElementById('edit-user-password').value;
        const confirmPassword = document.getElementById('confirm_password_edit').value;
        const indicator = document.getElementById('password-match-indicator-edit');

        if (!confirmPassword) {
            if (indicator) indicator.classList.add('hidden');
            return false;
        }

        if (indicator) {
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
        return false;
    }

    function resetPasswordMatchEdit() {
        const indicator = document.getElementById('password-match-indicator-edit');
        if (indicator) indicator.classList.add('hidden');
    }

    // Email validation
    function validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    // Toggle password visibility (same function from manage-users)
    function togglePasswordVisibility(inputId, toggleButtonId) {
        const passwordInput = document.getElementById(inputId);
        const toggleButton = document.getElementById(toggleButtonId);
        
        if (!passwordInput || !toggleButton) {
            return;
        }
        
        const isPassword = toggleButtonId.includes('edit-password') && !toggleButtonId.includes('confirm');
        const eyeIcon = document.getElementById(isPassword ? 'eye-icon-edit-password' : 'eye-icon-confirm-edit');
        const eyeOffIcon = document.getElementById(isPassword ? 'eye-off-icon-edit-password' : 'eye-off-icon-confirm-edit');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            if (eyeIcon) eyeIcon.classList.add('hidden');
            if (eyeOffIcon) eyeOffIcon.classList.remove('hidden');
        } else {
            passwordInput.type = 'password';
            if (eyeIcon) eyeIcon.classList.remove('hidden');
            if (eyeOffIcon) eyeOffIcon.classList.add('hidden');
        }
    }
    
    window.togglePasswordVisibility = togglePasswordVisibility;

    // Attach event listeners
    document.addEventListener('DOMContentLoaded', function() {
        const passwordInput = document.getElementById('edit-user-password');
        const confirmPasswordInput = document.getElementById('confirm_password_edit');
        const emailInput = document.getElementById('edit-user-email');

        if (passwordInput) {
            passwordInput.addEventListener('input', updatePasswordStrengthEdit);
        }

        if (confirmPasswordInput) {
            confirmPasswordInput.addEventListener('input', checkPasswordMatchEdit);
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
    });

    // Form validation
    const editUserForm = document.getElementById('edit-user-form');
    if (editUserForm) {
        editUserForm.addEventListener('submit', function(e) {
            const errorContainer = document.getElementById('form-errors');
            errorContainer.innerHTML = '';

            const password = document.getElementById('edit-user-password').value;
            const confirmPassword = document.getElementById('confirm_password_edit').value;
            const errors = [];

            // Only validate password if it's being changed
            if (password || confirmPassword) {
                if (password.length > 0 && password.length < 8) {
                    errors.push('Password must be at least 8 characters long.');
                }

                const strength = checkPasswordStrength(password);
                if (password.length > 0 && strength.strength < 50) {
                    errors.push('Password is too weak. Please use a stronger password.');
                }

                if (password !== confirmPassword) {
                    errors.push('Passwords do not match.');
                }
            }

            if (errors.length > 0) {
                e.preventDefault();
                errorContainer.innerHTML = '<div class="space-y-1">' + errors.map(err => '<p>• ' + err + '</p>').join('') + '</div>';
            }
        });
    }
</script>
@endpush
