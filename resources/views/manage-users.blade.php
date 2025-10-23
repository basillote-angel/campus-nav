@extends('layouts.app')

@section('content')
<div class="min-h-full">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-blue-800">Manage Users</h1>
        <div class="flex items-center space-x-3">
            <div class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium flex items-center">
                <svg class="w-4 h-4 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                {{ Auth::user()->name }}
            </div>
        </div>
    </div>
    
    <button 
        id="add-user-btn"
        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-6 focus:outline-none focus:ring-2 focus:ring-gblue-500 focus:ring-opacity-50 transition">
        + Add New User
    </button>

    <!-- Search -->
    <div class="flex space-x-2 mb-4">
        <input 
            type="text" 
            id="search-input"
            value="{{ request()->search }}" 
            placeholder="Search user by name or email..." 
            class="flex-grow px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        />
    </div>
    
    <!-- Table -->
    <div id="users-table">
        @include('components.user-table', ['users' => $users])
    </div>

    <!-- Create Modal -->
    <div id="add-user-modal" class="fixed top-0 left-0 w-full h-screen overflow-y-auto bg-black/50 hidden items-center justify-center z-50">
        <div class="bg-white p-6 w-full max-w-md rounded-lg shadow-lg my-8">
            <h2 class="text-2xl font-bold mb-4">Add New User</h2>

            <form id="add-user-form">
                @csrf
                <div class="mb-4">
                    <input 
                        type="text" 
                        name="name"
                        placeholder="Name" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                        required
                    />
                </div>

                <div class="mb-4">
                    <input 
                        type="email" 
                        name="email"
                        placeholder="example@gmail.com" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                    />
                </div>

                <input type="hidden" name="role" value="admin" />

                <div class="mb-4">
                    <input 
                        id="password"
                        type="password" 
                        name="password"
                        placeholder="Password" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                    />
                </div>

                <div class="mb-4">
                    <input 
                        id="confirm_password"
                        type="password" 
                        placeholder="Confirm Password" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gblue-500"
                        required
                    />
                </div>

                <div id="form-errors" class="mb-4 text-red-600 text-sm space-y-1"></div>

                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded w-full">
                    Submit
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
    <script>
        window.__toastShownAt = window.__toastShownAt || 0;
        function showOnceToast(opts){
            const now = Date.now();
            if (now - window.__toastShownAt < 1000) return;
            window.__toastShownAt = now;
            Swal.fire(Object.assign({
                icon: 'success',
                position: 'center',
                showConfirmButton: false,
                timer: 1600,
                timerProgressBar: true,
            }, opts));
        }
        document.addEventListener('DOMContentLoaded', function () {
            showOnceToast({ title: @json(session('success')) });
        });
    </script>
    @endif

    <script>
        // Search, Filters, and table
        const searchInput = document.getElementById('search-input');
        const usersTable = document.getElementById('users-table');

        // For modal
        const addUserModal = document.getElementById('add-user-modal');
        const addUserButton = document.getElementById('add-user-btn');
        const addUserForm = document.getElementById('add-user-form');

        // Debounce for optimized AJAX requests
        let debounceTimeout;
       
        const debounce = (func, delay = 500) => {
            clearTimeout(debounceTimeout);
            debounceTimeout = setTimeout(() => func(), delay);
        };

        // Fetch Data
        const fetchUsers = () => {
            const search = searchInput.value;

            // Build the query string
            const query = new URLSearchParams({ search }).toString();

            // Send AJAX request
            fetch(`{{ route('users') }}?${query}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(data => {
                usersTable.innerHTML = data;
            })
            .catch(error => console.error('Error fetching users:', error));
        };

        // Event Listeners
        searchInput.addEventListener('input', () => debounce(fetchUsers));
        

        addUserButton.addEventListener('click', () => {
            addUserModal.classList.toggle('hidden');
            addUserModal.classList.add('flex');
        });

        addUserModal.addEventListener('click', (e) => {
            if (e.target !== addUserModal) return;
            addUserModal.classList.add('hidden');
        });

        // Handle form submission
        addUserForm.addEventListener('submit', (e) => {
            e.preventDefault();

            const errorContainer = document.getElementById('form-errors');
            errorContainer.innerHTML = ''; // Clear previous errors

            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (password !== confirmPassword) {
                errorContainer.innerHTML = '<p>Passwords do not match.</p>';
                return;
            }

            const formData = new FormData(addUserForm);

            fetch("{{ route('users.store') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Close modal
                    addUserModal.classList.add('hidden');
                    // Clear form
                    addUserForm.reset();
                    // Refresh items list
                    fetchUsers();
                    if (typeof showOnceToast === 'function') {
                        showOnceToast({ title: 'User added successfully.' });
                    } else {
                        Swal.fire({ icon: 'success', title: 'User added successfully.', position: 'center', showConfirmButton: false, timer: 1600, timerProgressBar: true });
                    }
                } else {
                    errorContainer.innerHTML = '<p>Failed to create user.</p>';
                }
            })
            .catch(error => console.error('Error:', error));
        });
    </script>
@endsection
