@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto bg-white p-6 rounded-md mt-10">
	<div class="flex items-center  mb-4">
		<a href="{{ route('users') }}" class="text-gray-600 hover:text-green-600 rounded text-xs mr-2">
			<x-heroicon-o-arrow-small-left class="h-8 w-8"/>
		</a>
		<h2 class="text-2xl font-bold">Edit User</h2>
	</div>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-2 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <form id="add-user-form" action="{{ route('users.update', $user->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <input 
                type="text" 
                name="name"
                value="{{ $user->name }}"
                placeholder="Name" 
                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500" 
                required
            />
        </div>

        <div class="mb-4">
            <input
                name="email"
                placeholder="Email"
                value="{{ $user->email }}"
                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                required
            />
        </div>

        <div class="mb-4">
            <select 
                name="role" 
                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                value="{{ $user->role }}"
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
            />
        </div>

        <div class="mb-4">
            <input
                id="confirm_password"
                type="password" 
                placeholder="Confirm Password" 
                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
            />
        </div>

        <div id="form-errors" class="mb-4 text-red-600 text-sm space-y-1"></div>

        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded w-full">
            Save Changes
        </button>
    </form>
</div>

<script>
    const addUserForm = document.getElementById('add-user-form');

	addUserForm.addEventListener('submit', function(e) {
        const password = document.getElementById('password').value.trim();
        const confirmPassword = document.getElementById('confirm_password').value.trim();
        const errorContainer = document.getElementById('form-errors');
        errorContainer.innerHTML = '';

        if (password && password !== confirmPassword) {
            e.preventDefault();
            errorContainer.innerHTML = '<p>Passwords do not match.</p>';
        }
    });
</script>
@endsection
