@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold mb-6">Profile</h1>

    {{-- Profile Card --}}
    <div class="bg-white rounded-lg shadow p-6 mb-6 flex items-center space-x-6">
        <div class="text-4xl bg-green-100 w-20 h-20 rounded-full flex items-center justify-center">
            👤
        </div>
        <div>
            <h2 class="text-xl font-semibold text-gray-800">{{ Auth::user()->name }}</h2>
            <p class="text-gray-600 capitalize">{{ Auth::user()->role }}</p>
            <p class="text-gray-500 text-sm">{{ Auth::user()->email }}</p>
        </div>
    </div>

    {{-- Personal Information Card --}}
    <div class="bg-white rounded-lg shadow p-6 relative">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Personal Information</h3>
            <button class="text-sm text-white bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded">
                Update
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="text-gray-500 text-sm">Name</label>
                <p class="text-gray-800">{{ Auth::user()->name }}</p>
            </div>
            <div>
                <label class="text-gray-500 text-sm">Role</label>
                <p class="text-gray-800 capitalize">{{ Auth::user()->role }}</p>
            </div>
            <div>
                <label class="text-gray-500 text-sm">Email</label>
                <p class="text-gray-800">{{ Auth::user()->email }}</p>
            </div>
        </div>

        <div class="mt-4">
            <button class="flex items-center text-sm text-green-600 hover:underline">
                🔒 <span class="ml-1">Change Password</span>
            </button>
        </div>
    </div>
</div>
@endsection
