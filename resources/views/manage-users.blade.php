@extends('layouts.app')

@section('content')
    <h1 class="text-3xl font-bold text-green-800 mb-6">Manage Users</h1>
    
    <button 
        id="add-user-btn"
        class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded mb-6 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50 transition">
        + Add New User
    </button>

    <!-- Filters and Search -->
    <div class="flex space-x-2 mb-4">
        <input 
            type="text" 
            id="search-input"
            value="{{ request()->search }}" 
            placeholder="Search user by name or email..." 
            class="flex-grow px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
        />

        <select 
            id="role-select"
            class="w-full md:w-48 px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
        >
            <option value="">All Roles</option>
            <option value="student" {{ request()->role == 'student' ? 'selected' : '' }}>Student</option>
            <option value="staff" {{ request()->role == 'staff' ? 'selected' : '' }}>Staff</option>
            <option value="admin" {{ request()->role == 'admin' ? 'selected' : '' }}>Admin</option>
        </select>
    </div>
    
    <!-- Table -->
    <div id="users-table">
        @include('components.user-table', ['users' => $users])
    </div>

    <!-- Create Modal -->
    <div id="add-user-modal" class="absolute top-0 left-0 w-full h-screen overflow-y-auto bg-black/50 hidden items-center justify-center z-50">
        <div class="bg-white p-6 w-full max-w-md rounded-lg shadow-lg">
            <h2 class="text-2xl font-bold mb-4">Add New User</h2>

            <form id="add-user-form">
                @csrf
                <div class="mb-4">
                    <input 
                        type="text" 
                        name="name"
                        placeholder="Name" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500" 
                        required
                    />
                </div>

                <div class="mb-4">
                    <input 
                        type="email" 
                        name="email"
                        placeholder="example@gmail.com" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                        required
                    />
                </div>

                <div class="mb-4">
                    <select 
                        name="role" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                    >
                        <option value="student">Student</option>
                        <option value="staff">Staff</option>
                    </select>
                </div>

                <div class="mb-4">
                    <input 
                        id="password"
                        type="password" 
                        name="password"
                        placeholder="Password" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                        required
                    />
                </div>

                <div class="mb-4">
                    <input 
                        id="confirm_password"
                        type="password" 
                        placeholder="Confirm Password" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                        required
                    />
                </div>

                <div id="form-errors" class="mb-4 text-red-600 text-sm space-y-1"></div>

                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded w-full">
                    Submit
                </button>
            </form>
        </div>
    </div>

    <script>
        // Search, Filters, and table
        const searchInput = document.getElementById('search-input');
        const roleSelect = document.getElementById('role-select');
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
            const role = roleSelect.value;

            // Build the query string
            const query = new URLSearchParams({ search, role }).toString();

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
        roleSelect.addEventListener('change', fetchUsers);

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
                } else {
                    errorContainer.innerHTML = '<p>Failed to create user.</p>';
                }
            })
            .catch(error => console.error('Error:', error));
        });
    </script>
@endsection
