@extends('layouts.app')

@php
use Illuminate\Support\Facades\Storage;
@endphp

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
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-all duration-300 transform hover:-translate-y-1">
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

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-all duration-300 transform hover:-translate-y-1">
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

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-all duration-300 transform hover:-translate-y-1">
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

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-all duration-300 transform hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Claims</p>
                        <p class="text-3xl font-bold text-[#123A7D] mt-2">{{ $totalClaims }}</p>
                    </div>
                    <div class="bg-[rgba(59,130,246,0.08)] p-3 rounded-lg">
                        <svg class="w-8 h-8 text-[rgba(59,130,246,0.8)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Analytics Dashboard --}}
        @if(isset($analytics))
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-[#123A7D] flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    Analytics & Insights
                </h3>
                <button onclick="toggleAnalytics()" class="text-sm text-gray-600 hover:text-[#123A7D] flex items-center gap-1">
                    <span id="analyticsToggleText">Show</span>
                    <svg id="analyticsToggleIcon" class="w-4 h-4 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
            </div>
            
            <div id="analyticsPanel" class="hidden">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    {{-- Average Response Time --}}
                    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg p-4 border border-blue-200">
                        <p class="text-xs font-medium text-gray-600 mb-1">Average Response Time</p>
                        <p class="text-2xl font-bold text-[#123A7D]">{{ $analytics['avg_response_time_hours'] ?? 0 }}h</p>
                        <p class="text-xs text-gray-500 mt-1">From claim to decision</p>
                    </div>
                    
                    {{-- Top Category --}}
                    @if(isset($analytics['top_categories']) && $analytics['top_categories']->count() > 0)
                    <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-4 border border-green-200">
                        <p class="text-xs font-medium text-gray-600 mb-1">Top Category</p>
                        <p class="text-lg font-bold text-green-700">{{ $analytics['top_categories'][0]->name }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $analytics['top_categories'][0]->count }} claims</p>
                    </div>
                    @endif
                    
                    {{-- Weekly Trend --}}
                    @php
                        $weekTotal = collect($analytics['trend_last_7_days'] ?? [])->sum('approved') + collect($analytics['trend_last_7_days'] ?? [])->sum('rejected');
                    @endphp
                    <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-lg p-4 border border-purple-200">
                        <p class="text-xs font-medium text-gray-600 mb-1">7-Day Activity</p>
                        <p class="text-2xl font-bold text-purple-700">{{ $weekTotal }}</p>
                        <p class="text-xs text-gray-500 mt-1">Approvals + Rejections</p>
                    </div>
                </div>
                
                {{-- Trend Chart (Simple Bar Chart) --}}
                @if(isset($analytics['trend_last_7_days']) && count($analytics['trend_last_7_days']) > 0)
                <div class="border-t border-gray-200 pt-4">
                    <h4 class="text-sm font-semibold text-gray-700 mb-4">Last 7 Days Trend</h4>
                    <div class="flex items-end justify-between gap-2 h-32">
                        @foreach($analytics['trend_last_7_days'] as $day)
                            @php
                                $maxValue = max(collect($analytics['trend_last_7_days'])->map(fn($d) => max($d['approved'], $d['rejected']))->max(), 1);
                                $approvedHeight = ($day['approved'] / $maxValue) * 100;
                                $rejectedHeight = ($day['rejected'] / $maxValue) * 100;
                            @endphp
                            <div class="flex-1 flex flex-col items-center gap-1">
                                <div class="w-full flex flex-col items-end justify-end gap-1 h-full">
                                    <div class="w-full bg-green-500 rounded-t" style="height: {{ $approvedHeight }}%" title="Approved: {{ $day['approved'] }}"></div>
                                    <div class="w-full bg-red-500 rounded-t" style="height: {{ $rejectedHeight }}%" title="Rejected: {{ $day['rejected'] }}"></div>
                                </div>
                                <p class="text-xs text-gray-600 mt-1">{{ $day['date'] }}</p>
                            </div>
                        @endforeach
                    </div>
                    <div class="flex items-center gap-4 mt-4 justify-center">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 bg-green-500 rounded"></div>
                            <span class="text-xs text-gray-600">Approved</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 bg-red-500 rounded"></div>
                            <span class="text-xs text-gray-600">Rejected</span>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- Collection Status Dashboard --}}
        @php
            // Check if collection stats are available (migration might not be run yet)
            $showCollectionDashboard = isset($collectionStats) && (
                $collectionStats['pending_collection'] > 0 || 
                $collectionStats['collected_today'] > 0 ||
                $collectionStats['deadline_passed'] > 0
            );
        @endphp
        
        @if($showCollectionDashboard)
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border border-blue-200 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-[#123A7D] flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                    Collection Management
                </h3>
                @if($collectionStats['pending_collection'] > 0 || $collectionStats['deadline_passed'] > 0)
                    <x-ui.badge variant="warning">Requires Attention</x-ui.badge>
                @endif
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                {{-- Pending Collection Card --}}
                <x-ui.card hover>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-600">Pending Collection</p>
                            <p class="text-2xl font-bold text-amber-600 mt-1">{{ $collectionStats['pending_collection'] ?? 0 }}</p>
                            <p class="text-xs text-gray-500 mt-1">Items ready for pickup</p>
                        </div>
                        <div class="bg-amber-100 p-3 rounded-lg">
                            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </x-ui.card>
                
                {{-- Overdue Collection Card --}}
                <x-ui.card hover>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-600">Overdue</p>
                            <p class="text-2xl font-bold text-red-600 mt-1">{{ $collectionStats['deadline_passed'] ?? 0 }}</p>
                            <p class="text-xs text-gray-500 mt-1">Past collection date</p>
                        </div>
                        <div class="bg-red-100 p-3 rounded-lg">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </x-ui.card>
                
                {{-- Collected Today Card --}}
                <x-ui.card hover>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-600">Collected Today</p>
                            <p class="text-2xl font-bold text-green-600 mt-1">{{ $collectionStats['collected_today'] ?? 0 }}</p>
                            <p class="text-xs text-gray-500 mt-1">Successfully collected</p>
                        </div>
                        <div class="bg-green-100 p-3 rounded-lg">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                    </div>
                </x-ui.card>
                
                {{-- Collection Rate Card --}}
                <x-ui.card hover>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-600">Collection Rate</p>
                            <p class="text-2xl font-bold text-[#123A7D] mt-1">{{ $collectionStats['collection_rate'] ?? 0 }}%</p>
                            <p class="text-xs text-gray-500 mt-1">Overall success rate</p>
                        </div>
                        <div class="bg-[rgba(59,130,246,0.08)] p-3 rounded-lg">
                            <svg class="w-6 h-6 text-[rgba(59,130,246,0.8)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                    </div>
                </x-ui.card>
            </div>
        </div>
        @endif

        {{-- Bulk Actions Toolbar (Hidden by default) --}}
        <div id="bulkActionsToolbar" class="hidden bg-[#123A7D] rounded-xl shadow-lg border border-[#0E2F5E] p-4 mb-6 text-white">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <span class="font-semibold">
                        <span id="selectedCount">0</span> item(s) selected
                    </span>
                    <button 
                        onclick="selectAllPending()" 
                        class="text-sm underline hover:no-underline opacity-80 hover:opacity-100"
                    >
                        Select All Pending
                    </button>
                </div>
                <div class="flex items-center gap-3">
                    <button 
                        onclick="bulkApprove()"
                        class="px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500/50 focus:ring-offset-2 focus:ring-offset-[#123A7D] transition-colors"
                    >
                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Bulk Approve
                    </button>
                    <button 
                        onclick="bulkReject()"
                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500/50 focus:ring-offset-2 focus:ring-offset-[#123A7D] transition-colors"
                    >
                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Bulk Reject
                    </button>
                    <button 
                        onclick="clearSelection()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                    >
                        Clear Selection
                    </button>
                </div>
            </div>
        </div>


        <!-- Filters and Search -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-4">
                <div class="flex-1 max-w-md">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search Claims</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input 
                            type="text" 
                            id="search" 
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Search by item name, claimant name, email, description, location..." 
                            class="block w-full pl-10 pr-10 py-2 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-2 focus:ring-[#123A7D]/50 focus:border-[#123A7D] transition-colors duration-200 sm:text-sm"
                            onkeyup="debounceSearch(this)"
                        >
                        @if(request('search'))
                            <button 
                                type="button"
                                onclick="clearSearch()"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        @endif
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <label for="status-filter" class="text-sm font-medium text-gray-700 mr-2">Filter:</label>
                    <select id="status-filter" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#123A7D]/50 focus:border-[#123A7D] transition-colors duration-200">
                        <option value="">All Statuses</option>
                        <option value="matched">Pending Approval</option>
                        <option value="returned">Approved</option>
                        <option value="unclaimed">Rejected</option>
                    </select>
                    
                    {{-- Real-Time Update Indicator --}}
                    <div class="flex items-center gap-2">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input 
                                type="checkbox" 
                                id="autoRefreshToggle"
                                class="sr-only peer"
                                onchange="toggleAutoRefresh()"
                            >
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-[#123A7D]/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#123A7D]"></div>
                            <span class="ml-2 text-xs text-gray-600">Auto-refresh</span>
                        </label>
                        <div id="lastUpdated" class="text-xs text-gray-500">
                            Updated: <span id="lastUpdatedTime">Just now</span>
                        </div>
                    </div>
                    
                    <button id="refresh-btn" class="inline-flex items-center px-4 py-2 bg-[#123A7D] border border-transparent rounded-lg font-medium text-sm text-white hover:bg-[#10316A] focus:outline-none focus:ring-2 focus:ring-[#123A7D]/50 focus:ring-offset-2 transition-all duration-300 transform hover:-translate-y-0.5 hover:shadow-lg">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Refresh
                    </button>
                </div>
            </div>
            
            
            {{-- Advanced Filters (Collapsible) --}}
            <button 
                onclick="toggleAdvancedFilters()"
                class="w-full flex items-center justify-between text-left font-semibold text-gray-900 hover:text-[#123A7D] transition-colors p-3 rounded-lg hover:bg-gray-50"
            >
                <span class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Advanced Filters
                    @php
                        $activeFilters = array_filter(request()->only(['category', 'claim_age', 'date_from', 'date_to']));
                        $activeCount = count($activeFilters);
                    @endphp
                    @if($activeCount > 0)
                        <x-ui.badge variant="primary" size="sm">{{ $activeCount }} active</x-ui.badge>
                    @endif
                </span>
                <svg id="filterArrow" class="w-5 h-5 transform transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            
            <div id="advancedFiltersPanel" class="hidden mt-4 pt-4 border-t border-gray-200">
                <form method="get" action="{{ route('notifications') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    {{-- Category Filter --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                        <select name="category" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#123A7D]/50 focus:border-[#123A7D] text-sm">
                            <option value="">All Categories</option>
                            @foreach($categories ?? [] as $category)
                                <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    {{-- Claim Age Filter --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Claim Age</label>
                        <select name="claim_age" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#123A7D]/50 focus:border-[#123A7D] text-sm">
                            <option value="">All Ages</option>
                            <option value="today" {{ request('claim_age') == 'today' ? 'selected' : '' }}>Today</option>
                            <option value="24h" {{ request('claim_age') == '24h' ? 'selected' : '' }}>Last 24 Hours</option>
                            <option value="48h" {{ request('claim_age') == '48h' ? 'selected' : '' }}>Last 48 Hours</option>
                            <option value="week" {{ request('claim_age') == 'week' ? 'selected' : '' }}>Last Week</option>
                            <option value="overdue" {{ request('claim_age') == 'overdue' ? 'selected' : '' }}>Overdue (>24h)</option>
                        </select>
                    </div>
                    
                    {{-- Date Range --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                        <input 
                            type="date" 
                            name="date_from" 
                            value="{{ request('date_from') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#123A7D]/50 focus:border-[#123A7D] text-sm"
                        >
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                        <input 
                            type="date" 
                            name="date_to" 
                            value="{{ request('date_to') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#123A7D]/50 focus:border-[#123A7D] text-sm"
                        >
                    </div>
                    
                    {{-- Filter Actions --}}
                    <div class="flex items-end gap-2 md:col-span-2 lg:col-span-4">
                        <x-ui.button-primary type="submit" size="sm" class="flex-1">
                            Apply Filters
                        </x-ui.button-primary>
                        <a 
                            href="{{ route('notifications') }}" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            Clear All
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Notifications List -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-medium text-[#123A7D] flex items-center">
                    <svg class="w-5 h-5 mr-2 text-[rgba(59,130,246,0.8)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                    Claim Requests
                </h3>
                <p class="mt-1 text-sm text-gray-600">Review and manage item claim requests from users</p>
            </div>

            {{-- Sort and View Controls --}}
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-3 flex-wrap">
                    <label for="sortSelect" class="text-sm font-medium text-gray-700">Sort by:</label>
                    <select 
                        id="sortSelect"
                        onchange="handleSort()"
                        class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#123A7D]/50 focus:border-[#123A7D] text-sm bg-white"
                    >
                        <option value="newest" {{ ($sortBy ?? 'newest') === 'newest' ? 'selected' : '' }}>Newest First</option>
                        <option value="oldest" {{ ($sortBy ?? '') === 'oldest' ? 'selected' : '' }}>Oldest First</option>
                        <option value="claim_date" {{ ($sortBy ?? '') === 'claim_date' ? 'selected' : '' }}>Claim Date (Oldest Pending)</option>
                        <option value="priority" {{ ($sortBy ?? '') === 'priority' ? 'selected' : '' }}>Priority (Urgent First)</option>
                    </select>
                    
                    {{-- Per Page Selector --}}
                    <label for="perPageSelect" class="text-sm font-medium text-gray-700 ml-4">Per page:</label>
                    <select 
                        id="perPageSelect"
                        onchange="handlePerPageChange()"
                        class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#123A7D]/50 focus:border-[#123A7D] text-sm bg-white"
                    >
                        <option value="10" {{ request()->query('perPage', 20) == 10 ? 'selected' : '' }}>10</option>
                        <option value="20" {{ request()->query('perPage', 20) == 20 ? 'selected' : '' }}>20</option>
                        <option value="50" {{ request()->query('perPage', 20) == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request()->query('perPage', 20) == 100 ? 'selected' : '' }}>100</option>
                    </select>
                    
                    {{-- Export Button --}}
                    <a 
                        href="{{ route('notifications.export', request()->query()) }}" 
                        class="ml-4 px-3 py-2 text-sm text-gray-600 hover:text-[#123A7D] border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                        title="Export current view to CSV"
                        onclick="showToast('Exporting data...', 'info', 2000);"
                    >
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                        </svg>
                        Export CSV
                    </a>
                </div>
                
                {{-- Pagination Info --}}
            @if($notifications->hasPages())
                <p class="text-sm text-gray-600">
                    Showing <span class="font-medium">{{ $notifications->firstItem() }}</span> to 
                    <span class="font-medium">{{ $notifications->lastItem() }}</span> of 
                    <span class="font-medium">{{ $notifications->total() }}</span> claims
                </p>
                                    @endif
                                </div>

            <div id="notifications-list" class="">
                @forelse($notifications as $notification)
                    <div 
                        class="notification-card mb-4 bg-white rounded-xl shadow-sm border border-gray-100 border-l-4 {{ $notification->status === 'matched' ? 'border-amber-500' : ($notification->status === 'returned' ? 'border-green-500' : 'border-red-500') }} hover:shadow-md transition-all duration-300 overflow-hidden relative" 
                        data-item-id="{{ $notification->id }}" 
                        data-status="{{ $notification->status }}"
                        data-claimant-name="{{ $notification->claimedBy ? $notification->claimedBy->name : 'Unknown' }}"
                        data-claim-message="{{ $notification->claim_message ? Str::limit($notification->claim_message, 100) : 'No message' }}"
                        tabindex="0"
                        role="article"
                        aria-label="Notification for {{ $notification->title }}"
                    >
                        {{-- Checkbox for Bulk Selection (Only for Pending Claims) --}}
                        @if($notification->status === 'matched')
                            <div class="absolute top-4 left-4 z-10">
                                <input 
                                    type="checkbox" 
                                    class="notification-checkbox w-5 h-5 text-[#123A7D] border-gray-300 rounded focus:ring-[#123A7D]/50 cursor-pointer"
                                    data-item-id="{{ $notification->id }}"
                                    onchange="updateBulkActions()"
                                    onclick="event.stopPropagation()"
                                >
                            </div>
                        @endif
                        
                        <!-- Compact View (Always Visible) -->
                        <div class="notification-compact p-4 cursor-pointer hover:bg-gray-50 transition-colors duration-200" onclick="toggleNotification({{ $notification->id }})">
                            <div class="flex items-center gap-4">
                                <!-- Item Image (Smaller) -->
                                <div class="flex-shrink-0 {{ $notification->status === 'matched' ? 'ml-6' : '' }}">
                                    @if($notification->image_path)
                                        <img src="{{ str_starts_with($notification->image_path, 'http') ? $notification->image_path : (Storage::exists('public/' . $notification->image_path) ? asset('storage/' . $notification->image_path) : asset('storage/' . $notification->image_path)) }}" 
                                             alt="{{ $notification->title }}"
                                             class="w-16 h-16 object-cover rounded-lg border border-gray-200"
                                             loading="lazy"
                                             onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%2716%27 height=%2716%27%3E%3Crect width=%2716%27 height=%2716%27 fill=%27%23e5e7eb%27/%3E%3Ctext x=%2750%25%27 y=%2750%25%27 text-anchor=%27middle%27 dy=%27.3em%27 font-family=%27Arial%27 font-size=%279%27 fill=%27%239ca3af%27%3ENo Image%3C/text%3E%3C/svg%3E';">
                                            @else
                                        <div class="w-16 h-16 bg-gray-100 rounded-lg border border-gray-200 flex items-center justify-center">
                                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                            @endif
                                </div>

                                <!-- Main Info -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1 flex-wrap">
                                        <h4 class="text-base font-semibold text-[#123A7D] truncate">
                                            {{ $notification->title }}
                                        </h4>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border
                                            {{ $notification->status === 'matched' ? 'bg-amber-100 text-amber-800 border-amber-200' : 
                                               ($notification->status === 'returned' ? 'bg-green-100 text-green-800 border-green-200' : 'bg-red-100 text-red-800 border-red-200') }}">
                                            @if($notification->status === 'matched')
                                                <span class="w-1.5 h-1.5 bg-amber-500 rounded-full mr-1.5 animate-pulse"></span>
                                                Pending
                                            @elseif($notification->status === 'returned')
                                                <svg class="w-2.5 h-2.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                </svg>
                                                Approved
                                            @else
                                                <svg class="w-2.5 h-2.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                                </svg>
                                                Rejected
                                            @endif
                                        </span>
                                        
                                        @if(isset($itemsWithMultipleClaims[$notification->id]) && $itemsWithMultipleClaims[$notification->id]->count() > 1)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 border border-orange-200">
                                                ⚠️ {{ $itemsWithMultipleClaims[$notification->id]->count() }} Claims
                                            </span>
                                        @endif

                                        @if($notification->status === 'matched' && $notification->claimed_at)
                                            @php
                                                $hoursPending = \Carbon\Carbon::parse($notification->claimed_at)->diffInHours(now());
                                                $isUrgent = $hoursPending > 24;
                                            @endphp
                                            @if($isUrgent)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 border border-red-200">
                                                    🔥 Urgent
                                                </span>
                                            @endif
                                        @endif

                                        @if($notification->status === 'returned')
                                            @if($notification->collected_at)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 border border-blue-200">
                                                    ✓ Collected
                                                </span>
                                            @elseif($notification->collection_deadline)
                                                @if(\Carbon\Carbon::parse($notification->collection_deadline)->isPast())
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 border border-yellow-200">
                                                        ⏰ Past Date
                                                    </span>
                                        @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 border border-indigo-200">
                                                        📅 Pending
                                                    </span>
                                        @endif
                                            @endif
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-3 text-sm text-gray-600">
                                        <span class="flex items-center gap-1">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                            <span class="font-medium">{{ $notification->claimedBy ? $notification->claimedBy->name : 'Unknown User' }}</span>
                                        </span>
                                        <span class="text-gray-300">•</span>
                                        <span class="flex items-center gap-1">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                            </svg>
                                            {{ $notification->category ? $notification->category->name : 'N/A' }}
                                        </span>
                                        @if($notification->claimed_at)
                                            <span class="text-gray-300">•</span>
                                            <span class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($notification->claimed_at)->diffForHumans() }}</span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Quick Action Menu & Expand Icon -->
                                <div class="flex-shrink-0 flex items-center gap-2">
                                    {{-- Quick Action Menu (3-dot dropdown) --}}
                                    <div class="relative inline-block" onclick="event.stopPropagation()">
                                        <button 
                                            onclick="toggleActionMenu({{ $notification->id }})"
                                            class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-colors"
                                            aria-label="Actions"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                            </svg>
                                        </button>
                                        
                                        {{-- Dropdown Menu (Hidden by default) --}}
                                        <div 
                                            id="actionMenu{{ $notification->id }}"
                                            class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50"
                                            onclick="event.stopPropagation()"
                                        >
                                            <div class="py-1">
                                                @if($notification->status === 'matched')
                                                    <button 
                                                        onclick="approveClaim({{ $notification->id }})"
                                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-green-50 hover:text-green-700 flex items-center gap-2"
                                                    >
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                        </svg>
                                                        Approve Claim
                                                    </button>
                                                    <button 
                                                        onclick="rejectClaim({{ $notification->id }})"
                                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-700 flex items-center gap-2"
                                                    >
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                        </svg>
                                                        Reject Claim
                                                    </button>
                                                @endif
                                                
                                                @if(isset($itemsWithMultipleClaims[$notification->id]) && $itemsWithMultipleClaims[$notification->id]->count() > 1)
                                                    <button 
                                                        onclick="compareClaims({{ $notification->id }})"
                                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 flex items-center gap-2"
                                                    >
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"></path>
                                                        </svg>
                                                        Compare Claims ({{ $itemsWithMultipleClaims[$notification->id]->count() }})
                                                    </button>
                                                @endif
                                                
                                                <button 
                                                    onclick="viewItemDetails({{ $notification->id }})"
                                                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                    View Full Details
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    {{-- Expand Icon --}}
                                    <svg id="expand-icon-{{ $notification->id }}" class="w-5 h-5 text-gray-400 transform transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Expanded View (Hidden by Default) -->
                        <div id="expanded-{{ $notification->id }}" class="notification-expanded hidden border-t border-gray-100 bg-gray-50 transition-all duration-300 overflow-hidden" style="max-height: 0;">
                            <div class="p-6">
                                <div class="grid md:grid-cols-2 gap-6">
                                    <!-- Left Column: Details -->
                                    <div class="space-y-4">
                                        <div>
                                            <h5 class="text-sm font-semibold text-gray-700 mb-2">Item Information</h5>
                                            <div class="space-y-2 text-sm text-gray-600">
                                                <p><strong>Item ID:</strong> #{{ $notification->id }}</p>
                                                <p><strong>Item Title:</strong> {{ $notification->title }}</p>
                                                <p><strong>Category:</strong> {{ $notification->category ? $notification->category->name : 'N/A' }}</p>
                                                @if($notification->claimed_at)
                                                    <p><strong>Claim Date:</strong> {{ \Carbon\Carbon::parse($notification->claimed_at)->format('M d, Y \a\t g:i A') }} 
                                                        <span class="text-gray-400">({{ \Carbon\Carbon::parse($notification->claimed_at)->diffForHumans() }})</span>
                                                    </p>
                                                @endif
                                            </div>
                                        </div>

                                        <div>
                                            <h5 class="text-sm font-semibold text-gray-700 mb-2">Claimant Information</h5>
                                            <div class="space-y-2 text-sm text-gray-600">
                                                <p><strong>Name:</strong> {{ $notification->claimedBy ? $notification->claimedBy->name : 'Unknown User' }}</p>
                                        @if($notification->claimedBy)
                                                    <p><strong>Email:</strong> <a href="mailto:{{ $notification->claimedBy->email }}" class="text-[#123A7D] hover:underline">{{ $notification->claimedBy->email }}</a></p>
                                        @endif
                                            </div>
                                        </div>

                                        @if(isset($itemsWithMultipleClaims[$notification->id]) && $itemsWithMultipleClaims[$notification->id]->count() > 1)
                                            <div class="p-3 bg-orange-50 border border-orange-200 rounded-lg">
                                                <h5 class="text-sm font-semibold text-orange-900 mb-2">⚠️ Multiple Claims ({{ $itemsWithMultipleClaims[$notification->id]->count() }})</h5>
                                                <div class="space-y-1">
                                                    @foreach($itemsWithMultipleClaims[$notification->id] as $claim)
                                                        <p class="text-xs text-orange-700">
                                                            • <strong>{{ $claim->claimant->name ?? 'Unknown' }}</strong>: {{ Str::limit($claim->message, 60) }}
                                                        </p>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        @if($notification->claim_message)
                                            <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                                <h5 class="text-sm font-semibold text-blue-900 mb-1">Claimant Message</h5>
                                                <p class="text-sm text-blue-700">{{ $notification->claim_message }}</p>
                                            </div>
                                        @endif

                                        {{-- Claim Timeline View --}}
                                            <div>
                                            <h5 class="text-sm font-semibold text-gray-700 mb-3">Claim Timeline</h5>
                                            <div class="space-y-4">
                                                {{-- Claim Submitted --}}
                                                @if($notification->claimed_at)
                                                    <div class="flex gap-4">
                                                        <div class="flex flex-col items-center">
                                                            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                                                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                                </svg>
                                                            </div>
                                                            @if($notification->status !== 'matched')
                                                                <div class="w-0.5 h-full bg-gray-300 mt-2"></div>
                                                            @endif
                                                        </div>
                                                        <div class="flex-1 pb-6">
                                                            <div class="flex items-center gap-2 mb-1">
                                                                <h6 class="font-semibold text-gray-900">Claim Submitted</h6>
                                                                <x-ui.badge variant="info" size="sm">Completed</x-ui.badge>
                                                            </div>
                                                            <p class="text-sm text-gray-600">
                                                                Claimed by <strong>{{ $notification->claimedBy ? $notification->claimedBy->name : 'Unknown User' }}</strong>
                                                            </p>
                                                            <p class="text-xs text-gray-500 mt-1">
                                                                {{ \Carbon\Carbon::parse($notification->claimed_at)->format('M d, Y \a\t g:i A') }}
                                                                ({{ \Carbon\Carbon::parse($notification->claimed_at)->diffForHumans() }})
                                                            </p>
                                                        </div>
                                                    </div>
                                                @endif
                                                
                                                {{-- Approved --}}
                                        @if($notification->approved_at)
                                                    <div class="flex gap-4">
                                                        <div class="flex flex-col items-center">
                                                            <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                                                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                                </svg>
                                                            </div>
                                                            @if(!$notification->collected_at || $notification->collection_deadline)
                                                                <div class="w-0.5 h-full bg-gray-300 mt-2"></div>
                                        @endif
                                                        </div>
                                                        <div class="flex-1 pb-6">
                                                            <div class="flex items-center gap-2 mb-1">
                                                                <h6 class="font-semibold text-gray-900">Claim Approved</h6>
                                                                <x-ui.badge variant="success" size="sm">Completed</x-ui.badge>
                                                            </div>
                                                            <p class="text-sm text-gray-600">
                                                                Approved by <strong>{{ $notification->approvedBy ? $notification->approvedBy->name : 'Admin' }}</strong>
                                                            </p>
                                                            <p class="text-xs text-gray-500 mt-1">
                                                                {{ \Carbon\Carbon::parse($notification->approved_at)->format('M d, Y \a\t g:i A') }}
                                                                ({{ \Carbon\Carbon::parse($notification->approved_at)->diffForHumans() }})
                                                            </p>
                                                    @if($notification->collection_deadline)
                                                                <div class="mt-2 p-2 bg-blue-50 border border-blue-200 rounded-lg">
                                                                    <p class="text-xs font-medium text-blue-900 mb-1">📍 Collection Required</p>
                                                                    <p class="text-xs text-blue-700">
                                                                        Suggested date: <strong>{{ \Carbon\Carbon::parse($notification->collection_deadline)->format('M d, Y') }}</strong>
                                                                    </p>
                                                                    @if($notification->isCollectionDeadlinePassed())
                                                                        <p class="text-xs text-red-700 mt-1">⚠️ Past suggested date</p>
                                                    @endif
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    
                                                    {{-- Collection Deadline (if not collected) --}}
                                                    @if($notification->collection_deadline && !$notification->collected_at)
                                                        <div class="flex gap-4">
                                                            <div class="flex flex-col items-center">
                                                                <div class="w-10 h-10 rounded-full {{ $notification->isCollectionDeadlinePassed() ? 'bg-red-100' : 'bg-amber-100' }} flex items-center justify-center flex-shrink-0">
                                                                    <svg class="w-5 h-5 {{ $notification->isCollectionDeadlinePassed() ? 'text-red-600' : 'text-amber-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                                    </svg>
                                                                </div>
                                                                <div class="w-0.5 h-full bg-gray-300 mt-2"></div>
                                                            </div>
                                                            <div class="flex-1 pb-6">
                                                                <div class="flex items-center gap-2 mb-1">
                                                                    <h6 class="font-semibold text-gray-900">Collection Deadline</h6>
                                                                    <x-ui.badge variant="{{ $notification->isCollectionDeadlinePassed() ? 'danger' : 'warning' }}" size="sm">
                                                                        {{ $notification->isCollectionDeadlinePassed() ? 'Overdue' : 'Pending' }}
                                                                    </x-ui.badge>
                                                                </div>
                                                                <p class="text-sm text-gray-600">
                                                                    Suggested collection date: <strong>{{ \Carbon\Carbon::parse($notification->collection_deadline)->format('M d, Y') }}</strong>
                                                                </p>
                                                                <p class="text-xs text-gray-500 mt-1">
                                                                    {{ \Carbon\Carbon::parse($notification->collection_deadline)->diffForHumans() }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    @endif
                                                    
                                                    {{-- Collected --}}
                                                    @if($notification->collected_at)
                                                        <div class="flex gap-4">
                                                            <div class="flex flex-col items-center">
                                                                <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                                                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                                    </svg>
                                                                </div>
                                                            </div>
                                                            <div class="flex-1">
                                                                <div class="flex items-center gap-2 mb-1">
                                                                    <h6 class="font-semibold text-gray-900">Item Collected</h6>
                                                                    <x-ui.badge variant="success" size="sm">Completed</x-ui.badge>
                                                                </div>
                                                                <p class="text-sm text-gray-600">
                                                                    Collected by <strong>{{ $notification->collectedBy ? $notification->collectedBy->name : 'User' }}</strong>
                                                                </p>
                                                                <p class="text-xs text-gray-500 mt-1">
                                                                    {{ \Carbon\Carbon::parse($notification->collected_at)->format('M d, Y \a\t g:i A') }}
                                                                    ({{ \Carbon\Carbon::parse($notification->collected_at)->diffForHumans() }})
                                                                </p>
                                                            </div>
                                                        </div>
                                                            @endif
                                                @endif
                                                
                                                {{-- Rejected --}}
                                        @if($notification->rejected_at)
                                                    <div class="flex gap-4">
                                                        <div class="flex flex-col items-center">
                                                            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                                                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                                </svg>
                                                            </div>
                                                        </div>
                                                        <div class="flex-1">
                                                            <div class="flex items-center gap-2 mb-1">
                                                                <h6 class="font-semibold text-gray-900">Claim Rejected</h6>
                                                                <x-ui.badge variant="danger" size="sm">Completed</x-ui.badge>
                                                            </div>
                                                            <p class="text-sm text-gray-600">
                                                                Rejected by <strong>{{ $notification->rejectedBy ? $notification->rejectedBy->name : 'Admin' }}</strong>
                                                            </p>
                                                            <p class="text-xs text-gray-500 mt-1">
                                                                {{ \Carbon\Carbon::parse($notification->rejected_at)->format('M d, Y \a\t g:i A') }}
                                                                ({{ \Carbon\Carbon::parse($notification->rejected_at)->diffForHumans() }})
                                                            </p>
                                                            @if($notification->rejection_reason)
                                                                <div class="mt-2 p-2 bg-red-50 border border-red-200 rounded-lg">
                                                                    <p class="text-xs font-medium text-red-900 mb-1">Rejection Reason:</p>
                                                                    <p class="text-xs text-red-700">{{ $notification->rejection_reason }}</p>
                                                                </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                            </div>
                                        </div>

                                        @if($notification->rejected_at)
                                            <div>
                                                <h5 class="text-sm font-semibold text-gray-700 mb-2">Rejection Details</h5>
                                                <div class="space-y-2 text-sm text-gray-600">
                                            <p><strong>Rejected By:</strong> {{ $notification->rejectedBy ? $notification->rejectedBy->name : 'Admin' }}</p>
                                            <p><strong>Rejection Date:</strong> {{ \Carbon\Carbon::parse($notification->rejected_at)->format('M d, Y \a\t g:i A') }}</p>
                                            @if($notification->rejection_reason)
                                                        <div class="p-3 bg-red-50 border border-red-200 rounded-lg mt-2">
                                                            <p class="font-medium text-red-900 mb-1">Rejection Reason:</p>
                                                            <p class="text-sm text-red-700">{{ $notification->rejection_reason }}</p>
                                                        </div>
                                        @endif
                                    </div>
                                        </div>
                                    @endif
                                </div>

                                    <!-- Right Column: Image & Actions -->
                                    <div class="space-y-4">
                                        <!-- Larger Image -->
                                        <div class="flex justify-center">
                                            @if($notification->image_path)
                                                <img src="{{ str_starts_with($notification->image_path, 'http') ? $notification->image_path : (Storage::exists('public/' . $notification->image_path) ? asset('storage/' . $notification->image_path) : asset('storage/' . $notification->image_path)) }}" 
                                                     alt="{{ $notification->title }}"
                                                     class="w-full max-w-xs h-auto object-cover rounded-lg border border-gray-200 shadow-sm"
                                                     loading="lazy"
                                                     onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%27300%27 height=%27300%27%3E%3Crect width=%27300%27 height=%27300%27 fill=%27%23e5e7eb%27/%3E%3Ctext x=%2750%25%27 y=%2750%25%27 text-anchor=%27middle%27 dy=%27.3em%27 font-family=%27Arial%27 font-size=%2714%27 fill=%27%239ca3af%27%3ENo Image%3C/text%3E%3C/svg%3E';">
                                            @else
                                                <div class="w-full max-w-xs h-48 bg-gray-100 rounded-lg border border-gray-200 flex items-center justify-center">
                                                    <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                    </svg>
                            </div>
                                            @endif
                                        </div>

                                        <!-- Action Buttons (Only for Pending Claims) -->
                                        @if($notification->status === 'matched')
                                            <div class="flex flex-col gap-2 pt-4 border-t border-gray-200">
                                                <button id="approve-btn-{{ $notification->id }}" onclick="approveClaim({{ $notification->id }})" class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-green-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500/50 focus:ring-offset-2 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed">
                                                    <svg id="approve-icon-{{ $notification->id }}" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                                    <span id="approve-text-{{ $notification->id }}">Approve Claim</span>
                                    </button>
                                                <button id="reject-btn-{{ $notification->id }}" onclick="rejectClaim({{ $notification->id }})" class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-red-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500/50 focus:ring-offset-2 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed">
                                                    <svg id="reject-icon-{{ $notification->id }}" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                                    <span id="reject-text-{{ $notification->id }}">Reject Claim</span>
                                    </button>
                                                <button onclick="viewItemDetails({{ $notification->id }})" class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-[#123A7D] border border-transparent rounded-lg font-medium text-sm text-white hover:bg-[#10316A] focus:outline-none focus:ring-2 focus:ring-[#123A7D]/50 focus:ring-offset-2 transition-all duration-300">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                                    View Full Details
                                </button>
                            </div>
                                        @else
                                            <div class="flex flex-col gap-2 pt-4 border-t border-gray-200">
                                                <button onclick="viewItemDetails({{ $notification->id }})" class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-[#123A7D] border border-transparent rounded-lg font-medium text-sm text-white hover:bg-[#10316A] focus:outline-none focus:ring-2 focus:ring-[#123A7D]/50 focus:ring-offset-2 transition-all duration-300">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                    View Full Details
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    {{-- Enhanced Empty State --}}
                    <div id="empty-state" class="p-12 text-center">
                        @php
                            // Determine empty state type based on filters
                            $hasFilters = request()->has(['category', 'claim_age', 'date_from', 'date_to']) 
                                || request()->has('status-filter') 
                                || request()->has('search');
                            $emptyStateType = $hasFilters ? 'filtered' : ($pendingCount > 0 ? 'all_caught_up' : 'no_notifications');
                        @endphp
                        
                        @if($emptyStateType === 'filtered')
                            {{-- Filtered Empty State --}}
                            <div class="w-32 h-32 bg-gradient-to-br from-amber-50 to-orange-50 rounded-full flex items-center justify-center mx-auto mb-6">
                                <svg class="w-16 h-16 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                            </svg>
                        </div>
                            <h3 class="text-xl font-semibold text-[#123A7D] mb-2">No Results Found</h3>
                            <p class="text-gray-600 mb-4">No notifications match your current filters. Try adjusting your search criteria.</p>
                            <a href="{{ route('notifications') }}" class="inline-flex items-center px-4 py-2 bg-[#123A7D] text-white rounded-lg hover:bg-[#10316A] transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Clear All Filters
                            </a>
                        @elseif($emptyStateType === 'all_caught_up')
                            {{-- All Caught Up Empty State --}}
                        <div class="w-32 h-32 bg-gradient-to-br from-[rgba(59,130,246,0.1)] to-[rgba(18,58,125,0.1)] rounded-full flex items-center justify-center mx-auto mb-6">
                            <svg class="w-16 h-16 text-[rgba(59,130,246,0.6)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-[#123A7D] mb-2">All Caught Up! 🎉</h3>
                        <p class="text-gray-600 mb-4">No pending notifications at the moment. You're doing great!</p>
                            <div class="flex justify-center space-x-4 text-sm text-gray-500 mb-4">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-1 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                </svg>
                                Pending: {{ $pendingCount }}
                            </div>
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-1 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Approved Today: {{ $approvedToday }}
                            </div>
                        </div>
                            <div class="mt-6 flex justify-center gap-3">
                                <button onclick="document.getElementById('refresh-btn').click()" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors text-sm">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                    Refresh Page
                                </button>
                            </div>
                        @else
                            {{-- No Notifications Empty State --}}
                            <div class="w-32 h-32 bg-gradient-to-br from-gray-50 to-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                                <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-[#123A7D] mb-2">No Notifications Yet</h3>
                            <p class="text-gray-600 mb-4">There are no claim notifications in the system at this time.</p>
                        @endif
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($notifications->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        Showing <span class="font-medium">{{ $notifications->firstItem() }}</span> to 
                        <span class="font-medium">{{ $notifications->lastItem() }}</span> of 
                        <span class="font-medium">{{ $notifications->total() }}</span> results
                    </div>
                    <div class="flex gap-2">
                        {{ $notifications->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Toast Notification Container -->
<div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2 max-w-md w-full" aria-live="polite" aria-atomic="true"></div>


<!-- Rejection Modal -->
<div id="reject-modal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-[10000]">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Reject Claim</h3>
                <button onclick="closeRejectModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <p class="text-sm text-gray-600 mb-4">Please provide a reason for rejecting this claim. This reason will be sent to the claimant.</p>
            
            {{-- Rejection Reason Templates --}}
            <div class="mb-4">
                <label class="text-xs font-medium text-gray-700 mb-2 block">Quick Templates (click to use):</label>
                <div class="flex flex-wrap gap-2">
                    <button 
                        type="button"
                        onclick="setRejectionReason('Insufficient evidence or details provided in the claim.')"
                        class="px-3 py-1.5 text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors"
                    >
                        Insufficient Evidence
                    </button>
                    <button 
                        type="button"
                        onclick="setRejectionReason('Item description does not match the found item details.')"
                        class="px-3 py-1.5 text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors"
                    >
                        Description Mismatch
                    </button>
                    <button 
                        type="button"
                        onclick="setRejectionReason('Location or date information does not match our records.')"
                        class="px-3 py-1.5 text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors"
                    >
                        Location/Date Mismatch
                    </button>
                    <button 
                        type="button"
                        onclick="setRejectionReason('Another claim has already been approved for this item.')"
                        class="px-3 py-1.5 text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors"
                    >
                        Already Approved
                    </button>
                    <button 
                        type="button"
                        onclick="setRejectionReason('Claim cannot be verified at this time. Please provide additional documentation.')"
                        class="px-3 py-1.5 text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors"
                    >
                        Needs Verification
                    </button>
                </div>
            </div>
            
            <textarea id="rejection-reason" rows="4" placeholder="Enter rejection reason (required)..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 resize-none" required></textarea>
            <div class="mt-2 text-sm text-gray-500">
                <span id="char-count">0</span> / 1000 characters
            </div>
            <div class="flex justify-end space-x-3 mt-6">
                <button onclick="closeRejectModal()" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-colors">
                    Cancel
                </button>
                <button onclick="confirmReject()" id="confirm-reject-btn" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                    Reject Claim
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// JavaScript for handling notifications functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize keyboard navigation and hover previews
    initKeyboardNavigation();
    initHoverPreviews();
    
    const searchInput = document.getElementById('search');
    const statusFilter = document.getElementById('status-filter');
    const refreshBtn = document.getElementById('refresh-btn');
    const notificationsList = document.getElementById('notifications-list');

    let selectedNotificationIndex = -1;
    let pendingNotifications = [];

    // Initialize: Get all pending notifications for keyboard navigation
    function updatePendingNotifications() {
        pendingNotifications = Array.from(notificationsList.querySelectorAll('[data-status="matched"]'));
        pendingNotifications.forEach((item, index) => {
            item.setAttribute('data-index', index);
        });
    }
    updatePendingNotifications();


    // Search functionality with debounce
    let searchTimeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
        filterNotifications();
            updatePendingNotifications();
        }, 300);
    });

    // Status filter functionality
    statusFilter.addEventListener('change', function() {
        filterNotifications();
        updatePendingNotifications();
        selectedNotificationIndex = -1;
        clearHighlight();
    });

    // Refresh functionality
    refreshBtn.addEventListener('click', function() {
        // Add loading state
        const originalHTML = refreshBtn.innerHTML;
        refreshBtn.disabled = true;
        refreshBtn.innerHTML = `
            <svg class="animate-spin w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
            Refreshing...
        `;
        
        // Reload the page
        setTimeout(() => {
            location.reload();
        }, 500);
    });

    function filterNotifications() {
        const searchTerm = searchInput.value.toLowerCase();
        const statusFilterValue = statusFilter.value;
        const notificationItems = notificationsList.querySelectorAll('.p-6[data-status]');

        let visibleCount = 0;

        notificationItems.forEach(item => {
            const text = item.textContent.toLowerCase();
            const status = item.dataset.status || '';
            
            // Map filter values to actual status values
            let matchesStatus = true;
            if (statusFilterValue) {
                if (statusFilterValue === 'matched') {
                    matchesStatus = status === 'matched';
                } else if (statusFilterValue === 'returned') {
                    matchesStatus = status === 'returned';
                } else if (statusFilterValue === 'unclaimed') {
                    matchesStatus = status === 'unclaimed';
                }
            }
            
            const matchesSearch = !searchTerm || text.includes(searchTerm);

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
            emptyState.style.display = 'block';
            emptyState.scrollIntoView({ behavior: 'smooth' });
        } else {
            emptyState.style.display = 'none';
        }

        // Show filter results message
        const filterMessage = document.getElementById('filter-results-message');
        if (!filterMessage && (searchTerm || statusFilterValue)) {
            const message = document.createElement('div');
            message.id = 'filter-results-message';
            message.className = 'px-6 py-3 bg-blue-50 border-b border-blue-100 text-sm text-[#123A7D]';
            message.innerHTML = `Showing ${visibleCount} of ${notificationItems.length} notifications`;
            notificationsList.parentElement.insertBefore(message, notificationsList);
        } else if (filterMessage) {
            filterMessage.textContent = `Showing ${visibleCount} of ${notificationItems.length} notifications`;
        }

        // Clear filter message if no filters applied
        if (!searchTerm && !statusFilterValue) {
            const filterMsg = document.getElementById('filter-results-message');
            if (filterMsg) filterMsg.remove();
        }

        // Reset keyboard navigation when filters change
        updatePendingNotifications();
        selectedNotificationIndex = -1;
        clearHighlight();
    }

    // Toggle notification expand/collapse
    window.toggleNotification = function(itemId) {
        const expandedDiv = document.getElementById('expanded-' + itemId);
        const expandIcon = document.getElementById('expand-icon-' + itemId);
        
        if (expandedDiv && expandIcon) {
            if (expandedDiv.classList.contains('hidden')) {
                expandedDiv.classList.remove('hidden');
                expandIcon.classList.add('rotate-180');
                // Use requestAnimationFrame for smooth animation
                requestAnimationFrame(() => {
                    expandedDiv.style.maxHeight = expandedDiv.scrollHeight + 'px';
                });
            } else {
                expandedDiv.style.maxHeight = '0px';
                expandIcon.classList.remove('rotate-180');
                setTimeout(() => {
                    expandedDiv.classList.add('hidden');
                }, 300);
            }
        }
    };

    // Prevent action buttons from triggering expand/collapse
    document.addEventListener('click', function(e) {
        if (e.target.closest('.notification-expanded button, .notification-expanded a')) {
            e.stopPropagation();
        }
    });
    
    // Close modal on backdrop click
    document.getElementById('reject-modal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeRejectModal();
        }
    });
    
    // Update last updated time
    updateLastUpdatedTime();
});

// Rejection modal state
let currentRejectItemId = null;

// Auto-refresh state
let autoRefreshInterval = null;
let lastPendingCount = {{ $pendingCount }};

// Toggle advanced filters
function toggleAdvancedFilters() {
    const panel = document.getElementById('advancedFiltersPanel');
    const arrow = document.getElementById('filterArrow');
    
    if (panel && arrow) {
        if (panel.classList.contains('hidden')) {
            panel.classList.remove('hidden');
            arrow.classList.add('rotate-180');
        } else {
            panel.classList.add('hidden');
            arrow.classList.remove('rotate-180');
        }
    }
}

// Update bulk actions toolbar visibility
function updateBulkActions() {
    const checked = document.querySelectorAll('.notification-checkbox:checked');
    const toolbar = document.getElementById('bulkActionsToolbar');
    const countSpan = document.getElementById('selectedCount');
    
    if (checked.length > 0 && toolbar && countSpan) {
        toolbar.classList.remove('hidden');
        countSpan.textContent = checked.length;
    } else if (toolbar) {
        toolbar.classList.add('hidden');
    }
}

// Select all pending notifications
function selectAllPending() {
    const checkboxes = document.querySelectorAll('.notification-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = true;
    });
    updateBulkActions();
}

// Clear selection
function clearSelection() {
    const checkboxes = document.querySelectorAll('.notification-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = false;
    });
    updateBulkActions();
}

// Bulk approve selected items
async function bulkApprove() {
    const checked = Array.from(document.querySelectorAll('.notification-checkbox:checked'))
        .map(cb => parseInt(cb.dataset.itemId));
    
    if (checked.length === 0) return;
    
    Swal.fire({
        title: `Approve ${checked.length} claim(s)?`,
        text: 'This action will approve all selected claims. This cannot be undone.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6b7280',
        confirmButtonText: `Yes, approve ${checked.length}`,
        cancelButtonText: 'Cancel'
    }).then(async (result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Processing...',
                text: `Approving ${checked.length} claim(s)...`,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Process each approval
            let successCount = 0;
            let failCount = 0;
            
            for (const itemId of checked) {
                try {
                    const response = await fetch(`/notifications/${itemId}/approve`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json',
                        },
                    });
                    const data = await response.json();
                    if (data.success) {
                        successCount++;
                    } else {
                        failCount++;
                    }
                } catch (error) {
                    failCount++;
                }
            }
            
            // Show results
            Swal.fire({
                icon: failCount === 0 ? 'success' : 'warning',
                title: failCount === 0 ? 'Success!' : 'Completed with errors',
                text: `${successCount} approved successfully${failCount > 0 ? `, ${failCount} failed` : ''}`,
                confirmButtonText: 'OK'
            }).then(() => {
                location.reload();
            });
        }
    });
}

// Bulk reject selected items
async function bulkReject() {
    const checked = Array.from(document.querySelectorAll('.notification-checkbox:checked'))
        .map(cb => parseInt(cb.dataset.itemId));
    
    if (checked.length === 0) return;
    
    Swal.fire({
        title: `Reject ${checked.length} claim(s)?`,
        html: `
            <div class="text-left">
                <p class="mb-3">Please provide a rejection reason for all selected claims:</p>
                <textarea id="bulkRejectReason" rows="4" placeholder="Enter rejection reason..." class="w-full px-3 py-2 border border-gray-300 rounded-lg"></textarea>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: `Reject ${checked.length}`,
        cancelButtonText: 'Cancel',
        preConfirm: () => {
            const reason = document.getElementById('bulkRejectReason').value.trim();
            if (!reason || reason.length === 0 || reason.length > 1000) {
                Swal.showValidationMessage('Please provide a rejection reason (max 1000 characters)');
                return false;
            }
            return reason;
        }
    }).then(async (result) => {
        if (result.isConfirmed && result.value) {
            const reason = result.value;
            
            // Show loading
            Swal.fire({
                title: 'Processing...',
                text: `Rejecting ${checked.length} claim(s)...`,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Process each rejection
            let successCount = 0;
            let failCount = 0;
            
            for (const itemId of checked) {
                try {
                    const response = await fetch(`/notifications/${itemId}/reject`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ reason: reason })
                    });
                    const data = await response.json();
                    if (data.success) {
                        successCount++;
                    } else {
                        failCount++;
                    }
                } catch (error) {
                    failCount++;
                }
            }
            
            // Show results
            Swal.fire({
                icon: failCount === 0 ? 'success' : 'warning',
                title: failCount === 0 ? 'Success!' : 'Completed with errors',
                text: `${successCount} rejected successfully${failCount > 0 ? `, ${failCount} failed` : ''}`,
                confirmButtonText: 'OK'
            }).then(() => {
                location.reload();
            });
        }
    });
}

// Toggle quick action menu
function toggleActionMenu(itemId) {
    const menu = document.getElementById('actionMenu' + itemId);
    if (!menu) return;
    
    // Close all other menus
    document.querySelectorAll('[id^="actionMenu"]').forEach(otherMenu => {
        if (otherMenu.id !== 'actionMenu' + itemId) {
            otherMenu.classList.add('hidden');
        }
    });
    
    // Toggle current menu
    menu.classList.toggle('hidden');
}

// Close action menus when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('[id^="actionMenu"]') && !e.target.closest('button[onclick*="toggleActionMenu"]')) {
        document.querySelectorAll('[id^="actionMenu"]').forEach(menu => {
            menu.classList.add('hidden');
        });
    }
});

// Auto-refresh functionality
function toggleAutoRefresh() {
    const toggle = document.getElementById('autoRefreshToggle');
    
    if (!toggle) return;
    
    if (toggle.checked) {
        autoRefreshInterval = setInterval(async () => {
            await checkForNewClaims();
            updateLastUpdatedTime();
        }, 30000); // Every 30 seconds
    } else {
        if (autoRefreshInterval) {
            clearInterval(autoRefreshInterval);
            autoRefreshInterval = null;
        }
    }
}

// Check for new claims
async function checkForNewClaims() {
    try {
        const response = await fetch('{{ route("notifications") }}?count_only=1');
        const text = await response.text();
        // Parse pending count from response (simplified - you may want to add an API endpoint)
        // For now, we'll just update the timestamp
        updateLastUpdatedTime();
    } catch (error) {
        console.error('Failed to check for new claims:', error);
    }
}

// Update last updated time
function updateLastUpdatedTime() {
    const timeSpan = document.getElementById('lastUpdatedTime');
    if (timeSpan) {
        const now = new Date();
        timeSpan.textContent = now.toLocaleTimeString();
    }
}

// Handle sort change
function handleSort() {
    const sortSelect = document.getElementById('sortSelect');
    const currentUrl = new URL(window.location.href);
    currentUrl.searchParams.set('sort', sortSelect.value);
    window.location.href = currentUrl.toString();
}

// Handle per-page change
function handlePerPageChange() {
    const perPageSelect = document.getElementById('perPageSelect');
    const currentUrl = new URL(window.location.href);
    currentUrl.searchParams.set('perPage', perPageSelect.value);
    currentUrl.searchParams.set('page', '1'); // Reset to first page
    window.location.href = currentUrl.toString();
}

// Toast Notification System
function showToast(message, type = 'success', duration = 3000) {
    const container = document.getElementById('toast-container');
    if (!container) return;
    
    const toast = document.createElement('div');
    const icons = {
        success: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>',
        error: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>',
        info: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
        warning: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>'
    };
    
    const colors = {
        success: 'bg-green-50 border-green-200 text-green-800',
        error: 'bg-red-50 border-red-200 text-red-800',
        info: 'bg-blue-50 border-blue-200 text-blue-800',
        warning: 'bg-amber-50 border-amber-200 text-amber-800'
    };
    
    toast.className = `${colors[type]} border rounded-lg shadow-lg p-4 flex items-start gap-3 animate-slide-in transform transition-all duration-300`;
    toast.innerHTML = `
        <div class="flex-shrink-0 mt-0.5">${icons[type] || icons.info}</div>
        <div class="flex-1">
            <p class="text-sm font-medium">${message}</p>
        </div>
        <button onclick="this.parentElement.remove()" class="flex-shrink-0 text-gray-400 hover:text-gray-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    `;
    
    container.appendChild(toast);
    
    // Auto-remove after duration
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => toast.remove(), 300);
    }, duration);
    
    return toast;
}

// Hover Tooltip/Preview (using Tippy.js-like simple implementation)
function initHoverPreviews() {
    const cards = document.querySelectorAll('.notification-card');
    cards.forEach(card => {
        let tooltip = null;
        
        card.addEventListener('mouseenter', function(e) {
            const cardRect = this.getBoundingClientRect();
            const claimantName = this.dataset.claimantName || 'Unknown';
            const claimMessage = this.dataset.claimMessage || 'No message';
            
            // Create tooltip
            tooltip = document.createElement('div');
            tooltip.className = 'absolute z-50 w-64 p-3 bg-gray-900 text-white text-xs rounded-lg shadow-xl pointer-events-none';
            tooltip.style.top = (cardRect.top - 10) + 'px';
            tooltip.style.left = (cardRect.right + 10) + 'px';
            tooltip.innerHTML = `
                <div class="font-semibold mb-2">${claimantName}</div>
                <div class="text-gray-300">${claimMessage}</div>
                <div class="mt-2 text-xs text-gray-400">Hover to preview • Click for details</div>
            `;
            
            document.body.appendChild(tooltip);
            
            // Adjust position if tooltip goes off screen
            setTimeout(() => {
                if (tooltip) {
                    const tooltipRect = tooltip.getBoundingClientRect();
                    if (tooltipRect.right > window.innerWidth) {
                        tooltip.style.left = (cardRect.left - tooltipRect.width - 10) + 'px';
                    }
                    if (tooltipRect.bottom > window.innerHeight) {
                        tooltip.style.top = (window.innerHeight - tooltipRect.height - 10) + 'px';
                    }
                }
            }, 0);
        });
        
        card.addEventListener('mouseleave', function() {
            if (tooltip) {
                tooltip.remove();
                tooltip = null;
            }
        });
    });
}

// Compare claims modal (placeholder - needs backend support)
// Compare claims modal - shows side-by-side comparison
async function compareClaims(itemId) {
    try {
        // Show loading state
        Swal.fire({
            title: 'Loading claims...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Fetch claims data
        const response = await fetch(`/notifications/${itemId}/claims`, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            }
        });
        
        const data = await response.json();
        
        if (!data.success || !data.claims || data.claims.length === 0) {
            Swal.fire({
                icon: 'info',
                title: 'No Claims Found',
                text: 'There are no claims to compare for this item.',
                confirmButtonColor: '#123A7D'
            });
            return;
        }
        
        // Build comparison HTML
        const itemInfo = data.item;
        let claimsHtml = '';
        
        data.claims.forEach((claim, index) => {
            const statusColor = {
                'pending': 'amber',
                'approved': 'green',
                'rejected': 'red',
                'withdrawn': 'gray'
            }[claim.status] || 'gray';
            
            claimsHtml += `
                <div class="border border-gray-200 rounded-lg p-4 mb-4 ${claim.status === 'pending' ? 'bg-amber-50' : ''}">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-900 mb-1">${claim.claimant_name}</h4>
                            <p class="text-xs text-gray-600">${claim.claimant_email}</p>
                        </div>
                        <div class="flex flex-col items-end gap-2">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-${statusColor}-100 text-${statusColor}-800">
                                ${claim.status.charAt(0).toUpperCase() + claim.status.slice(1)}
                            </span>
                            ${claim.status === 'pending' ? `
                                <div class="flex gap-2">
                                    <button onclick="approveClaimFromComparison(${itemId}, ${claim.id})" 
                                            class="px-3 py-1 text-xs bg-green-600 text-white rounded hover:bg-green-700">
                                        Approve
                                    </button>
                                    <button onclick="rejectClaimFromComparison(${itemId}, ${claim.id})" 
                                            class="px-3 py-1 text-xs bg-red-600 text-white rounded hover:bg-red-700">
                                        Reject
                                    </button>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <p class="text-xs font-medium text-gray-700 mb-1">Claim Message:</p>
                        <p class="text-sm text-gray-800 bg-white p-2 rounded border">${claim.message || 'No message provided'}</p>
                    </div>
                    
                    ${claim.matched_lost_item_title ? `
                        <div class="mb-3">
                            <p class="text-xs font-medium text-gray-700 mb-1">Matched Lost Item:</p>
                            <p class="text-sm text-gray-800">${claim.matched_lost_item_title}</p>
                        </div>
                    ` : ''}
                    
                    <div class="flex items-center gap-4 text-xs text-gray-500">
                        <span>Submitted: ${claim.created_at_human}</span>
                        <span>(${claim.created_at})</span>
                    </div>
                </div>
            `;
        });
        
        // Show comparison modal
        Swal.fire({
            title: `<div class="text-left"><h3 class="text-lg font-semibold text-[#123A7D] mb-2">Compare Claims: ${itemInfo.title}</h3><p class="text-sm text-gray-600">${data.total_claims} claim(s) found</p></div>`,
            html: `
                <div class="text-left max-h-96 overflow-y-auto">
                    <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                        <p class="text-xs font-medium text-blue-900 mb-2">Item Details:</p>
                        <div class="grid grid-cols-2 gap-2 text-xs text-blue-800">
                            <div><strong>Category:</strong> ${itemInfo.category}</div>
                            <div><strong>Location:</strong> ${itemInfo.location || 'N/A'}</div>
                            <div><strong>Date Found:</strong> ${itemInfo.date_found}</div>
                            <div><strong>Pending:</strong> ${data.claims.filter(c => c.status === 'pending').length}</div>
                        </div>
                    </div>
                    ${claimsHtml}
                </div>
            `,
            width: '800px',
            showConfirmButton: true,
            confirmButtonText: 'Close',
            confirmButtonColor: '#123A7D',
            showCancelButton: false,
        });
        
    } catch (error) {
        console.error('Error fetching claims:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to load claims for comparison. Please try again.',
            confirmButtonColor: '#123A7D'
        });
    }
}

// Approve claim from comparison modal
function approveClaimFromComparison(itemId, claimId) {
    Swal.fire({
        title: 'Approve this claim?',
        text: 'This will approve only this specific claim.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, approve',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `/notifications/${itemId}/approve?claim_id=${claimId}`;
        }
    });
}

// Reject claim from comparison modal
function rejectClaimFromComparison(itemId, claimId) {
    Swal.fire({
        title: 'Reject this claim?',
        html: `
            <div class="text-left">
                <p class="mb-3">Please provide a reason:</p>
                <textarea id="comparisonRejectReason" rows="4" placeholder="Enter rejection reason..." class="w-full px-3 py-2 border border-gray-300 rounded-lg"></textarea>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Reject',
        cancelButtonText: 'Cancel',
        preConfirm: () => {
            const reason = document.getElementById('comparisonRejectReason').value.trim();
            if (!reason || reason.length === 0 || reason.length > 1000) {
                Swal.showValidationMessage('Please provide a rejection reason (max 1000 characters)');
                return false;
            }
            return reason;
        }
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            fetch(`/notifications/${itemId}/reject`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ 
                    reason: result.value,
                    claim_id: claimId 
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Claim rejected successfully', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Failed to reject claim',
                        confirmButtonColor: '#123A7D'
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to reject claim. Please try again.',
                    confirmButtonColor: '#123A7D'
                });
            });
        }
    });
}

// Function to approve a claim (with toast notification)
function approveClaim(itemId) {
    Swal.fire({
        title: 'Approve Claim?',
        text: 'Are you sure you want to approve this claim?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, approve',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            const approveBtn = document.getElementById(`approve-btn-${itemId}`);
            const approveIcon = document.getElementById(`approve-icon-${itemId}`);
            const approveText = document.getElementById(`approve-text-${itemId}`);
            
            // Show loading state
            approveBtn.disabled = true;
            approveIcon.innerHTML = '<svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
            approveText.textContent = 'Processing...';

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
                    showToast(data.message || 'Claim approved successfully!', 'success');
                    setTimeout(() => {
                    location.reload();
                    }, 1500);
            } else {
                    showToast(data.message || 'Failed to approve claim', 'error');
                    // Reset button state
                    approveBtn.disabled = false;
                    approveIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>';
                    approveText.textContent = 'Approve';
                    
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: data.message
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
                // Reset button state
                approveBtn.disabled = false;
                approveIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>';
                approveText.textContent = 'Approve';
                
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'An error occurred while processing your request.'
            });
        });
    }
    });
}

// Function to open rejection modal
function rejectClaim(itemId) {
    currentRejectItemId = itemId;
    const modal = document.getElementById('reject-modal');
    const textarea = document.getElementById('rejection-reason');
    const charCount = document.getElementById('char-count');
    const confirmBtn = document.getElementById('confirm-reject-btn');
    
    // Reset modal
    textarea.value = '';
    charCount.textContent = '0';
    confirmBtn.disabled = true;
    
    // Show modal
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    
    // Focus textarea
    textarea.focus();
    
    // Character count update
    textarea.addEventListener('input', function() {
        const count = this.value.length;
        charCount.textContent = count;
        confirmBtn.disabled = count === 0 || count > 1000;
    });
}

// Function to close rejection modal
function closeRejectModal() {
    const modal = document.getElementById('reject-modal');
    const textarea = document.getElementById('rejection-reason');
    
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    textarea.value = '';
    currentRejectItemId = null;
    
    // Reset character count
    if (document.getElementById('char-count')) {
        document.getElementById('char-count').textContent = '0';
    }
    if (document.getElementById('confirm-reject-btn')) {
        document.getElementById('confirm-reject-btn').disabled = true;
    }
}

// Set rejection reason from template
function setRejectionReason(reason) {
    const textarea = document.getElementById('rejection-reason');
    if (textarea) {
        textarea.value = reason;
        updateCharCount();
        textarea.focus();
        // Scroll to textarea
        textarea.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        showToast('Template applied', 'info', 1500);
    }
}

// Update character count for rejection reason
function updateCharCount() {
    const textarea = document.getElementById('rejection-reason');
    const charCount = document.getElementById('char-count');
    const confirmBtn = document.getElementById('confirm-reject-btn');
    
    if (textarea && charCount) {
        const length = textarea.value.length;
        charCount.textContent = length;
        
        // Enable/disable button based on character count
        if (confirmBtn) {
            if (length > 0 && length <= 1000) {
                confirmBtn.disabled = false;
            } else {
                confirmBtn.disabled = true;
            }
        }
        
        // Change color based on character count
        if (length > 1000) {
            charCount.classList.add('text-red-600');
            charCount.classList.remove('text-gray-500');
        } else {
            charCount.classList.remove('text-red-600');
            charCount.classList.add('text-gray-500');
        }
    }
}

// Initialize character count listener
document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.getElementById('rejection-reason');
    if (textarea) {
        textarea.addEventListener('input', updateCharCount);
    }
});

// Advanced Search - Debounce functionality
let searchTimeout = null;
function debounceSearch(input) {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        const searchTerm = input.value.trim();
        if (searchTerm.length >= 2 || searchTerm.length === 0) {
            // Submit form or trigger search
            const form = input.closest('form');
            if (form) {
                form.submit();
            } else {
                // If no form, update URL
                const url = new URL(window.location.href);
                if (searchTerm.length >= 2) {
                    url.searchParams.set('search', searchTerm);
                } else {
                    url.searchParams.delete('search');
                }
                url.searchParams.set('page', '1'); // Reset to first page
                window.location.href = url.toString();
            }
        }
    }, 500); // Wait 500ms after user stops typing
}

// Clear search
function clearSearch() {
    const searchInput = document.getElementById('search');
    if (searchInput) {
        searchInput.value = '';
        const url = new URL(window.location.href);
        url.searchParams.delete('search');
        url.searchParams.set('page', '1');
        window.location.href = url.toString();
    }
}

// Toggle analytics panel
function toggleAnalytics() {
    const panel = document.getElementById('analyticsPanel');
    const icon = document.getElementById('analyticsToggleIcon');
    const text = document.getElementById('analyticsToggleText');
    
    if (panel && icon && text) {
        panel.classList.toggle('hidden');
        icon.classList.toggle('rotate-180');
        text.textContent = panel.classList.contains('hidden') ? 'Show' : 'Hide';
    }
}

// Mobile Swipe Gestures
let touchStartX = 0;
let touchEndX = 0;
let touchStartY = 0;
let touchEndY = 0;

function handleSwipe() {
    const swipeThreshold = 50; // Minimum swipe distance
    const horizontalSwipe = Math.abs(touchEndX - touchStartX);
    const verticalSwipe = Math.abs(touchEndY - touchStartY);
    
    // Only process if horizontal swipe is dominant
    if (horizontalSwipe > verticalSwipe && horizontalSwipe > swipeThreshold) {
        const card = this.closest('.notification-card');
        if (!card) return;
        
        const itemId = card.dataset.itemId;
        const status = card.dataset.status;
        
        if (!itemId || status !== 'matched') return;
        
        // Swipe right = Approve
        if (touchEndX > touchStartX) {
            Swal.fire({
                title: 'Approve this claim?',
                text: 'Swipe right detected - approving claim',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, approve',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    approveClaim(itemId);
                }
            });
        }
        // Swipe left = Reject
        else if (touchEndX < touchStartX) {
            Swal.fire({
                title: 'Reject this claim?',
                text: 'Swipe left detected - rejecting claim',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, reject',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    openRejectModal(itemId);
                }
            });
        }
        
        // Reset card position
        card.style.transform = '';
        card.style.transition = 'transform 0.3s ease-out';
    }
}

// Initialize swipe gestures on mobile
function initSwipeGestures() {
    if ('ontouchstart' in window) {
        const cards = document.querySelectorAll('.notification-card');
        cards.forEach(card => {
            card.addEventListener('touchstart', function(e) {
                touchStartX = e.changedTouches[0].screenX;
                touchStartY = e.changedTouches[0].screenY;
                this.style.transition = '';
            });
            
            card.addEventListener('touchmove', function(e) {
                const currentX = e.changedTouches[0].screenX;
                const diff = currentX - touchStartX;
                
                // Visual feedback during swipe
                if (Math.abs(diff) > 10) {
                    this.style.transform = `translateX(${diff * 0.3}px)`;
                    if (diff > 0) {
                        this.style.backgroundColor = 'rgba(16, 185, 129, 0.1)'; // Green tint for right swipe
                    } else {
                        this.style.backgroundColor = 'rgba(239, 68, 68, 0.1)'; // Red tint for left swipe
                    }
                }
            });
            
            card.addEventListener('touchend', function(e) {
                touchEndX = e.changedTouches[0].screenX;
                touchEndY = e.changedTouches[0].screenY;
                
                // Reset visual state
                this.style.backgroundColor = '';
                
                // Handle swipe
                handleSwipe.call(this, e);
            });
        });
    }
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    initSwipeGestures();
});

// Function to confirm rejection
function confirmReject() {
    const textarea = document.getElementById('rejection-reason');
    const reason = textarea.value.trim();
    const confirmBtn = document.getElementById('confirm-reject-btn');
    const rejectBtn = document.getElementById(`reject-btn-${currentRejectItemId}`);
    const rejectIcon = document.getElementById(`reject-icon-${currentRejectItemId}`);
    const rejectText = document.getElementById(`reject-text-${currentRejectItemId}`);
    
    if (!reason || reason.length === 0 || reason.length > 1000) {
        Swal.fire({
            icon: 'warning',
            title: 'Invalid Reason',
            text: 'Please provide a rejection reason (max 1000 characters).'
        });
        return;
    }
    
    // Show loading state
    confirmBtn.disabled = true;
    confirmBtn.textContent = 'Processing...';
    if (rejectBtn) {
        rejectBtn.disabled = true;
        rejectIcon.innerHTML = '<svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
        rejectText.textContent = 'Processing...';
    }
    
    fetch(`/notifications/${currentRejectItemId}/reject`, {
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
            closeRejectModal();
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
            // Reset button state
            confirmBtn.disabled = false;
            confirmBtn.textContent = 'Reject Claim';
            if (rejectBtn) {
                rejectBtn.disabled = false;
                rejectIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>';
                rejectText.textContent = 'Reject';
            }
            
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: data.message
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
        // Reset button state
        confirmBtn.disabled = false;
        confirmBtn.textContent = 'Reject Claim';
        if (rejectBtn) {
            rejectBtn.disabled = false;
            rejectIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>';
            rejectText.textContent = 'Reject';
        }
        
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'An error occurred while processing your request.'
            });
        });
}

// Function to view item details
function viewItemDetails(itemId) {
    // Redirect to item edit page with type=found
    window.location.href = `/items/${itemId}/edit?type=found`;
}

</script>

<style>
/* Toast Animation */
@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.animate-slide-in {
    animation: slideIn 0.3s ease-out;
}

/* Focus indicator for keyboard navigation */
.notification-card:focus {
    outline: 2px solid rgba(59, 130, 246, 0.5);
    outline-offset: 2px;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Skeleton Loader */
.skeleton {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: loading 1.5s ease-in-out infinite;
}

@keyframes loading {
    0% {
        background-position: 200% 0;
    }
    100% {
        background-position: -200% 0;
    }
}

/* Mobile Responsive Improvements */
@media (max-width: 640px) {
    .notification-card {
        border-radius: 0.75rem;
        margin-bottom: 1rem;
    }
    
    .stats-card {
        margin-bottom: 1rem;
    }
    
    /* Stack filters vertically on mobile */
    .flex-wrap {
        flex-direction: column;
        align-items: stretch;
    }
    
    /* Full width buttons on mobile */
    .mobile-full-width {
        width: 100%;
    }
    
    /* Reduce padding on mobile */
    .mobile-padding {
        padding: 1rem;
    }
}

/* Loading State */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.9);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}
</style>

@endsection 