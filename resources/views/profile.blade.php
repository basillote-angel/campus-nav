@extends('layouts.app')

@section('content')
<div class="min-h-full bg-gradient-to-br from-blue-50 via-white to-indigo-50">
    {{-- Page Header --}}
    <x-ui.page-header
        title="My Profile"
        description="Manage your account settings and personal information"
    />

    {{-- Main Content --}}
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Success/Error Messages --}}
        @if(session('success'))
            <x-ui.alert type="success" dismissible="true" class="mb-6">
                {{ session('success') }}
            </x-ui.alert>
        @elseif(session('error'))
            <x-ui.alert type="error" dismissible="true" class="mb-6">
                {{ session('error') }}
            </x-ui.alert>
        @endif

        {{-- Profile Overview Card --}}
        <div class="bg-gradient-to-br from-white to-blue-50/30 rounded-2xl shadow-lg border border-gray-200/50 p-8 md:p-10 mb-8 relative overflow-hidden">
            {{-- Decorative background elements --}}
            <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-br from-[#123A7D]/5 to-transparent rounded-full -mr-32 -mt-32"></div>
            <div class="absolute bottom-0 left-0 w-48 h-48 bg-gradient-to-tr from-indigo-50 to-transparent rounded-full -ml-24 -mb-24"></div>
            
            <div class="relative flex flex-col md:flex-row items-center md:items-start space-y-6 md:space-y-0 md:space-x-8">
                {{-- Profile Avatar --}}
                <div class="relative group">
                    <div class="w-32 h-32 bg-gradient-to-br from-[#123A7D] via-[#1a4fa3] to-[#10316A] rounded-2xl flex items-center justify-center text-white text-4xl font-bold shadow-xl ring-4 ring-white ring-offset-2 transition-transform duration-300 group-hover:scale-105">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div class="absolute -bottom-1 -right-1 bg-gradient-to-br from-green-400 to-green-600 w-10 h-10 rounded-full border-4 border-white flex items-center justify-center shadow-lg">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>

                {{-- Profile Info --}}
                <div class="flex-1 text-center md:text-left">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-3">{{ Auth::user()->name }}</h2>
                    <div class="flex items-center justify-center md:justify-start space-x-3 mb-5 flex-wrap gap-2">
                        <span class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-semibold bg-gradient-to-r from-blue-100 to-blue-50 text-blue-700 border border-blue-200 shadow-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            {{ ucfirst(Auth::user()->role) }}
                        </span>
                        <span class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-semibold bg-gradient-to-r from-green-100 to-emerald-50 text-green-700 border border-green-200 shadow-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Active
                        </span>
                    </div>
                    <div class="flex items-center justify-center md:justify-start space-x-2 text-gray-600 bg-white/60 backdrop-blur-sm rounded-xl px-4 py-3 border border-gray-200/50 shadow-sm w-fit mx-auto md:mx-0">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <span class="font-medium">{{ Auth::user()->email }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            {{-- Personal Information Card --}}
            <div class="bg-white rounded-2xl shadow-lg border border-gray-200/50 p-6 md:p-8 relative overflow-hidden">
                {{-- Decorative accent --}}
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-[#123A7D] via-blue-500 to-[#10316A]"></div>
                
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-100 to-blue-50 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            Personal Information
                        </h3>
                        <p class="text-sm text-gray-600 mt-2">Update your personal details</p>
                    </div>
                    <button 
                        onclick="showEditProfileModal()"
                        class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-[#123A7D] to-[#10316A] border border-transparent rounded-xl font-semibold text-sm text-white hover:from-[#10316A] hover:to-[#0d2757] focus:outline-none focus:ring-2 focus:ring-[#123A7D] focus:ring-offset-2 transition-all duration-200 cursor-pointer shadow-md hover:shadow-lg transform hover:scale-105"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit
                </button>
            </div>

                <div class="space-y-4">
                    <div class="bg-gradient-to-br from-gray-50 to-blue-50/30 rounded-xl p-5 border border-gray-200/50 hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Full Name</label>
                        </div>
                        <p class="text-gray-900 font-semibold text-lg ml-11">{{ Auth::user()->name }}</p>
                    </div>
                    
                    <div class="bg-gradient-to-br from-gray-50 to-blue-50/30 rounded-xl p-5 border border-gray-200/50 hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Email Address</label>
                        </div>
                        <p class="text-gray-900 font-semibold text-lg ml-11">{{ Auth::user()->email }}</p>
                    </div>

                    <div class="bg-gradient-to-br from-gray-50 to-blue-50/30 rounded-xl p-5 border border-gray-200/50 hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                            </div>
                            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Account Role</label>
                </div>
                        <div class="flex items-center mt-1 ml-11">
                            <span class="inline-flex items-center px-3 py-1.5 rounded-xl text-sm font-semibold bg-gradient-to-r from-blue-100 to-blue-50 text-blue-700 border border-blue-200">
                                {{ ucfirst(Auth::user()->role) }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-br from-gray-50 to-blue-50/30 rounded-xl p-5 border border-gray-200/50 hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Member Since</label>
                        </div>
                        <p class="text-gray-900 font-semibold text-lg ml-11">{{ Auth::user()->created_at->format('F d, Y') }}</p>
                    </div>
                </div>

                {{-- Security Section --}}
                <div class="mt-10 pt-8 border-t border-gray-200">
                    <h4 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                        <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
            </div>
                        Security Settings
                    </h4>
                <div class="space-y-3">
                        <button 
                            onclick="showChangePasswordModal()"
                            class="w-full inline-flex items-center justify-center px-5 py-3 bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-xl font-semibold text-sm text-amber-700 hover:from-amber-100 hover:to-orange-100 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition-all duration-200 cursor-pointer shadow-sm hover:shadow-md"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        Change Password
                    </button>
                    
                        <form method="POST" action="{{ route('logout') }}" class="inline-block w-full">
                            @csrf
                            <button 
                                type="submit"
                                class="w-full inline-flex items-center justify-center px-5 py-3 bg-gradient-to-r from-red-50 to-pink-50 border border-red-200 rounded-xl font-semibold text-sm text-red-700 hover:from-red-100 hover:to-pink-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-200 cursor-pointer shadow-sm hover:shadow-md"
                            >
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        Sign Out
                    </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Activity Summary Card --}}
            <div class="bg-white rounded-2xl shadow-lg border border-gray-200/50 p-6 md:p-8 relative overflow-hidden">
                {{-- Decorative accent --}}
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-purple-500 via-indigo-500 to-blue-500"></div>
                
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                            <div class="w-10 h-10 bg-gradient-to-br from-purple-100 to-indigo-50 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            Recent Activity
                        </h3>
                        <p class="text-sm text-gray-600 mt-2">Your recent actions and interactions</p>
                    </div>
                </div>

                <div class="space-y-3 max-h-[600px] overflow-y-auto custom-scrollbar pr-2">
                    @forelse($recentActivities ?? [] as $activity)
                        @php
                            $activityColorClass = 'bg-blue-100 text-blue-600';
                            $activityBgClass = 'from-blue-50 to-blue-100/50';
                            $activityIcon = 'M8 16l2.879-2.879a3 3 0 014.242 0L18 16m-6-8a3 3 0 100-6 3 3 0 000 6z';
                            if (str_contains(strtolower($activity->action), 'approve') || str_contains(strtolower($activity->action), 'claim approved')) {
                                $activityColorClass = 'bg-green-100 text-green-600';
                                $activityBgClass = 'from-green-50 to-emerald-100/50';
                                $activityIcon = 'M5 13l4 4L19 7';
                            } elseif (str_contains(strtolower($activity->action), 'reject') || str_contains(strtolower($activity->action), 'claim rejected')) {
                                $activityColorClass = 'bg-red-100 text-red-600';
                                $activityBgClass = 'from-red-50 to-rose-100/50';
                                $activityIcon = 'M6 18L18 6M6 6l12 12';
                            } elseif (str_contains(strtolower($activity->action), 'match') || str_contains(strtolower($activity->action), 'ai')) {
                                $activityColorClass = 'bg-purple-100 text-purple-600';
                                $activityBgClass = 'from-purple-50 to-indigo-100/50';
                                $activityIcon = 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m12.728 0l-.707.707M12 21v-1m-6.657-3.343l.707-.707m12.728 0l.707.707';
                            } elseif (str_contains(strtolower($activity->action), 'password')) {
                                $activityColorClass = 'bg-amber-100 text-amber-600';
                                $activityBgClass = 'from-amber-50 to-orange-100/50';
                                $activityIcon = 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z';
                            } elseif (str_contains(strtolower($activity->action), 'profile')) {
                                $activityColorClass = 'bg-blue-100 text-blue-600';
                                $activityBgClass = 'from-blue-50 to-blue-100/50';
                                $activityIcon = 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z';
                            }
                        @endphp
                        <div class="flex items-center space-x-4 p-4 bg-gradient-to-r {{ $activityBgClass }} rounded-xl hover:shadow-md transition-all duration-200 border border-gray-200/50">
                            <div class="w-12 h-12 {{ $activityColorClass }} rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $activityIcon }}"></path>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900">{{ $activity->action }}</p>
                                @if($activity->details)
                                    <p class="text-xs text-gray-600 mt-1 truncate">{{ Str::limit($activity->details, 60) }}</p>
                                @endif
                            </div>
                            <span class="text-xs font-medium text-gray-500 whitespace-nowrap bg-white/60 px-2 py-1 rounded-lg">{{ $activity->created_at->diffForHumans() }}</span>
                        </div>
                    @empty
                        <div class="text-center py-12 text-gray-500">
                            <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <p class="text-sm font-medium">No recent activity</p>
                            <p class="text-xs text-gray-400 mt-1">Your activity will appear here</p>
                        </div>
                    @endforelse
                </div>
            </div>
                </div>
            </div>
        </div>

{{-- Edit Profile Modal --}}
<div id="edit-profile-modal" class="fixed inset-0 bg-black/50 z-[10000] flex items-center justify-center p-0 md:p-4 hidden" onclick="if(event.target === this) hideModal('edit-profile-modal')">
    <div class="bg-white rounded-none md:rounded-xl shadow-2xl max-w-2xl w-full h-full md:h-auto md:max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
        <div class="bg-gradient-to-r from-[#123A7D] to-[#10316A] text-white px-4 py-4 md:px-6 md:py-4 rounded-none md:rounded-t-xl flex items-center justify-between sticky top-0 z-10">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-white/20 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl md:text-2xl font-bold">Edit Profile</h2>
                    <p class="text-xs md:text-sm text-white/80">Update your personal information</p>
                </div>
            </div>
            <button
                type="button"
                onclick="hideModal('edit-profile-modal')"
                class="text-white hover:text-gray-200 transition-colors p-2 hover:bg-white/10 rounded-lg cursor-pointer"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
            </button>
        </div>
        <div class="p-4 md:p-6">
            <form id="edit-profile-form" method="POST" action="{{ route('profile.update') }}">
                @csrf
                <div class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ Auth::user()->name }}"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#123A7D] focus:border-[#123A7D]"
                        >
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ Auth::user()->email }}"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#123A7D] focus:border-[#123A7D]"
                        >
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 md:p-6">
                        <div class="flex flex-col sm:flex-row gap-3">
                            <button
                                type="button"
                                onclick="hideModal('edit-profile-modal')"
                                class="flex-1 min-w-[140px] px-6 py-3 bg-white text-gray-700 rounded-lg hover:bg-gray-100 transition-colors text-sm font-semibold text-center flex items-center justify-center gap-2 shadow-sm hover:shadow-md border border-gray-300 cursor-pointer"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Cancel
                            </button>
                            <button
                                type="submit"
                                id="submit-profile-btn"
                                class="flex-1 min-w-[140px] px-6 py-3 bg-gradient-to-r from-[#123A7D] to-[#10316A] text-white rounded-lg hover:from-[#10316A] hover:to-[#0d2757] transition-colors text-sm font-semibold flex items-center justify-center gap-2 shadow-sm hover:shadow-md cursor-pointer"
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
        </div>
    </div>
                </div>

{{-- Change Password Modal --}}
<div id="change-password-modal" class="fixed inset-0 bg-black/50 z-[10000] flex items-center justify-center p-0 md:p-4 hidden" onclick="if(event.target === this) hideModal('change-password-modal')">
    <div class="bg-white rounded-none md:rounded-xl shadow-2xl max-w-2xl w-full h-full md:h-auto md:max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
        <div class="bg-gradient-to-r from-[#123A7D] to-[#10316A] text-white px-4 py-4 md:px-6 md:py-4 rounded-none md:rounded-t-xl flex items-center justify-between sticky top-0 z-10">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-white/20 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl md:text-2xl font-bold">Change Password</h2>
                    <p class="text-xs md:text-sm text-white/80">Update your account password</p>
                </div>
            </div>
            <button
                type="button"
                onclick="hideModal('change-password-modal')"
                class="text-white hover:text-gray-200 transition-colors p-2 hover:bg-white/10 rounded-lg cursor-pointer"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="p-4 md:p-6">
            <form id="change-password-form" method="POST" action="{{ route('profile.changePassword') }}">
                @csrf
                <div class="space-y-6">
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
                        <div class="relative">
                            <input
                                type="password"
                                id="current_password"
                                name="current_password"
                                required
                                class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#123A7D] focus:border-[#123A7D]"
                            >
                            <button
                                type="button"
                                onclick="togglePasswordVisibility('current_password', 'current_password_eye')"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors cursor-pointer"
                                id="current_password_eye"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                            </button>
                        </div>
                        @error('current_password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="new_password" class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                        <div class="relative">
                            <input
                                type="password"
                                id="new_password"
                                name="new_password"
                                required
                                minlength="8"
                                class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#123A7D] focus:border-[#123A7D]"
                            >
                            <button
                                type="button"
                                onclick="togglePasswordVisibility('new_password', 'new_password_eye')"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors cursor-pointer"
                                id="new_password_eye"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                    </div>
                        <p class="mt-1 text-xs text-gray-500">Must be at least 8 characters long</p>
                        @error('new_password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                </div>

                    <div>
                        <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                        <div class="relative">
                            <input
                                type="password"
                                id="new_password_confirmation"
                                name="new_password_confirmation"
                                required
                                minlength="8"
                                class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#123A7D] focus:border-[#123A7D]"
                            >
                            <button
                                type="button"
                                onclick="togglePasswordVisibility('new_password_confirmation', 'new_password_confirmation_eye')"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors cursor-pointer"
                                id="new_password_confirmation_eye"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                            </button>
                        </div>
                        @error('new_password_confirmation')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 md:p-6">
                        <div class="flex flex-col sm:flex-row gap-3">
                            <button
                                type="button"
                                onclick="hideModal('change-password-modal')"
                                class="flex-1 min-w-[140px] px-6 py-3 bg-white text-gray-700 rounded-lg hover:bg-gray-100 transition-colors text-sm font-semibold text-center flex items-center justify-center gap-2 shadow-sm hover:shadow-md border border-gray-300 cursor-pointer"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Cancel
                            </button>
                            <button
                                type="submit"
                                id="submit-password-btn"
                                class="flex-1 min-w-[140px] px-6 py-3 bg-gradient-to-r from-[#123A7D] to-[#10316A] text-white rounded-lg hover:from-[#10316A] hover:to-[#0d2757] transition-colors text-sm font-semibold flex items-center justify-center gap-2 shadow-sm hover:shadow-md cursor-pointer"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Change Password
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function showEditProfileModal() {
        document.getElementById('edit-profile-modal').classList.remove('hidden');
        document.getElementById('edit-profile-modal').classList.add('flex');
    }

    function showChangePasswordModal() {
        document.getElementById('change-password-modal').classList.remove('hidden');
        document.getElementById('change-password-modal').classList.add('flex');
    }

    function hideModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
        document.getElementById(modalId).classList.remove('flex');
    }

    // Form submission handlers
    document.getElementById('edit-profile-form')?.addEventListener('submit', function(e) {
        const submitBtn = document.getElementById('submit-profile-btn');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="animate-spin rounded-full h-4 w-4 border-b-2 border-white inline-block mr-2"></span> Saving...';
        }
    });

    document.getElementById('change-password-form')?.addEventListener('submit', function(e) {
        const submitBtn = document.getElementById('submit-password-btn');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="animate-spin rounded-full h-4 w-4 border-b-2 border-white inline-block mr-2"></span> Changing...';
        }
    });

    // Password visibility toggle function
    function togglePasswordVisibility(inputId, buttonId) {
        const input = document.getElementById(inputId);
        const button = document.getElementById(buttonId);
        
        if (input.type === 'password') {
            input.type = 'text';
            button.innerHTML = `
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                </svg>
            `;
        } else {
            input.type = 'password';
            button.innerHTML = `
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
            `;
        }
    }
</script>

<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: linear-gradient(to bottom, #123A7D, #10316A);
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(to bottom, #10316A, #0d2757);
    }
</style>
@endsection
