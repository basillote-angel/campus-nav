@extends('layouts.app')

@section('content')
<div class="bg-gradient-to-br from-blue-50 via-white to-indigo-50 min-h-full">
    {{-- Page Header with Export Button --}}
    <div class="bg-white shadow-sm border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-[#123A7D]">Dashboard</h1>
                    <p class="mt-1 text-sm text-gray-600">Welcome! Here's what's happening with your Lost & Found + Campus Navigation system.</p>
                </div>
                <div class="flex items-center space-x-3">
                    {{-- Auto-refresh Indicator --}}
                    <div class="flex items-center gap-2 text-sm text-gray-600" id="auto-refresh-indicator" title="Auto-refreshing every 45 seconds">
                        <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse" id="refresh-pulse"></div>
                    </div>
                    
                    {{-- Export Dropdown --}}
                    <x-ui.export-dropdown 
                        id="dashboard-export-dropdown"
                        :routes="[
                            'csv' => route('dashboard.export', ['format' => 'csv']),
                            'csv-activity' => route('dashboard.export', ['format' => 'csv', 'type' => 'activity']),
                            'pdf' => route('dashboard.export', ['format' => 'pdf'])
                        ]"
                        :labels="[
                            'csv' => 'Export Analytics (CSV)',
                            'csv-activity' => 'Export Activity Log (CSV)',
                            'pdf' => 'Export Dashboard (PDF)'
                        ]"
                    />
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Success/Error Messages --}}
        @if(session('success'))
            <x-ui.alert type="success" dismissible="true" class="mb-6">
                {{ session('success') }}
            </x-ui.alert>
        @elseif(session('error'))
            <x-ui.alert type="error" dismissible="true" class="mb-6">
                {{ session('error') }}
            </x-ui.alert>
        @elseif(session('info'))
            <x-ui.alert type="info" dismissible="true" class="mb-6">
                {{ session('info') }}
            </x-ui.alert>
        @endif
        {{-- Enhanced Stat Cards (8 Cards) --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            {{-- Total Users --}}
            <a href="{{ route('users') }}" class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-sm border border-blue-200 p-6 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 cursor-pointer group" data-stat-type="users">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-blue-100">Registered Users</p>
                        <p class="text-3xl font-bold text-white mt-2" data-stat-value>{{ $totalUsers }}</p>
                        <p class="text-xs text-blue-100 mt-1 flex items-center gap-1" data-stat-growth>
                            @if($usersGrowthPercent >= 0)
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                                +{{ abs($usersGrowthPercent) }}%
                            @else
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                {{ $usersGrowthPercent }}%
                            @endif
                            from last month
                        </p>
                    </div>
                    <div class="bg-white/20 p-3 rounded-lg group-hover:bg-white/30 transition-colors">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                </div>
            </a>

            {{-- Lost Items --}}
            <a href="{{ route('item', ['type' => 'lost']) }}" class="bg-gradient-to-br from-red-500 to-red-600 rounded-xl shadow-sm border border-red-200 p-6 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 cursor-pointer group" data-stat-type="lost">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-red-100">Lost Items</p>
                        <p class="text-3xl font-bold text-white mt-2" data-stat-value>{{ $lostItems }}</p>
                        <p class="text-xs text-red-100 mt-1 flex items-center gap-1" data-stat-growth>
                            @if($lostGrowthPercent >= 0)
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                                +{{ abs($lostGrowthPercent) }}%
                            @else
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                {{ $lostGrowthPercent }}%
                            @endif
                            from last week
                        </p>
                    </div>
                    <div class="bg-white/20 p-3 rounded-lg group-hover:bg-white/30 transition-colors">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                </div>
            </div>
            </a>

            {{-- Found Items --}}
            <a href="{{ route('item', ['type' => 'found']) }}" class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-sm border border-green-200 p-6 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 cursor-pointer group" data-stat-type="found">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-green-100">Found Items</p>
                        <p class="text-3xl font-bold text-white mt-2" data-stat-value>{{ $foundItems }}</p>
                        <p class="text-xs text-green-100 mt-1 flex items-center gap-1" data-stat-growth>
                            @if($foundGrowthPercent >= 0)
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                                +{{ abs($foundGrowthPercent) }}%
                            @else
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                {{ $foundGrowthPercent }}%
                            @endif
                            this month
                        </p>
                    </div>
                    <div class="bg-white/20 p-3 rounded-lg group-hover:bg-white/30 transition-colors">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>
            </a>

            {{-- Pending Claims --}}
            <a href="{{ route('admin.claims.index', ['tab' => 'pending']) }}" class="bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl shadow-sm border border-amber-200 p-6 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 cursor-pointer group relative" data-stat-type="claims">
                @if($urgentClaims > 0)
                    <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full w-6 h-6 flex items-center justify-center animate-pulse">{{ $urgentClaims }}</span>
                @endif
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-amber-100">Pending Claims</p>
                        <p class="text-3xl font-bold text-white mt-2" data-stat-value>{{ $pendingClaims }}</p>
                        <p class="text-xs text-amber-100 mt-1">
                            @if($urgentClaims > 0)
                                {{ $urgentClaims }} urgent
                            @else
                                Needs review
                            @endif
                        </p>
                    </div>
                    <div class="bg-white/20 p-3 rounded-lg group-hover:bg-white/30 transition-colors">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                </div>
            </div>
            </a>

            {{-- Successful Matches --}}
            <a href="{{ route('admin.matches.index') }}" class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-sm border border-purple-200 p-6 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 cursor-pointer group" data-stat-type="matches">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-purple-100">Successful Matches</p>
                        <p class="text-3xl font-bold text-white mt-2" data-stat-value>{{ $successfulMatches }}</p>
                        <p class="text-xs text-purple-100 mt-1">{{ $matchSuccessRate }}% success rate</p>
                    </div>
                    <div class="bg-white/20 p-3 rounded-lg group-hover:bg-white/30 transition-colors">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                </div>
            </a>

            {{-- Unclaimed Items --}}
            <a href="{{ route('item', ['type' => 'found', 'status' => 'unclaimed']) }}" class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl shadow-sm border border-yellow-200 p-6 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 cursor-pointer group" data-stat-type="unclaimed">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-yellow-100">Unclaimed Items</p>
                        <p class="text-3xl font-bold text-white mt-2" data-stat-value>{{ $unclaimedItems }}</p>
                        <p class="text-xs text-yellow-100 mt-1">
                            @if($oldestUnclaimedDays > 0)
                                Oldest: {{ $oldestUnclaimedDays }} days
                            @else
                                Needs attention
                            @endif
                        </p>
                    </div>
                    <div class="bg-white/20 p-3 rounded-lg group-hover:bg-white/30 transition-colors">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                </div>
            </a>

            {{-- Items Collected This Month --}}
            <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl shadow-sm border border-emerald-200 p-6 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1" data-stat-type="collected">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-emerald-100">Collected This Month</p>
                        <p class="text-3xl font-bold text-white mt-2" data-stat-value>{{ $collectedThisMonth }}</p>
                        <p class="text-xs text-emerald-100 mt-1 flex items-center gap-1" data-stat-growth>
                            @if($collectedGrowthPercent >= 0)
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                                +{{ abs($collectedGrowthPercent) }}%
                            @else
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                {{ $collectedGrowthPercent }}%
                            @endif
                            from last month
                        </p>
                    </div>
                    <div class="bg-white/20 p-3 rounded-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- AI Match Success Rate --}}
            <div class="bg-gradient-to-br from-cyan-500 to-cyan-600 rounded-xl shadow-sm border border-cyan-200 p-6 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1" data-stat-type="match-success">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-cyan-100">AI Match Success</p>
                        <p class="text-3xl font-bold text-white mt-2" data-stat-value>{{ $matchSuccessRatePercent }}</p>
                        <p class="text-xs text-cyan-100 mt-1 flex items-center gap-1" data-stat-growth>
                            @if($matchSuccessGrowthPercent >= 0)
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                                +{{ abs($matchSuccessGrowthPercent) }}%
                            @else
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                {{ $matchSuccessGrowthPercent }}%
                            @endif
                            from last week
                        </p>
                    </div>
                    <div class="bg-white/20 p-3 rounded-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Charts Section --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            {{-- Posts Over Time Chart --}}
            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-[#123A7D]">Posts Over Time</h2>
                    <div class="flex items-center gap-2">
                        <button id="timeRange7d" class="time-range-btn active px-3 py-1 text-sm font-medium text-[#123A7D] bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors cursor-pointer">7 Days</button>
                        <button id="timeRange30d" class="time-range-btn px-3 py-1 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-lg transition-colors cursor-pointer">30 Days</button>
                        <button id="timeRange90d" class="time-range-btn px-3 py-1 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-lg transition-colors cursor-pointer">90 Days</button>
                    </div>
                </div>
                <div class="relative" style="height: 350px;">
                    <canvas id="postsOverTimeChart"></canvas>
                </div>
            </div>

            <div class="flex flex-col gap-6">
                {{-- Item Status Chart --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-xl font-bold text-[#123A7D] mb-4">Item Status</h2>
                    <div class="relative" style="height: 200px; max-height: 200px;">
                        <canvas id="itemStatusChart"></canvas>
                    </div>
                </div>
                {{-- AI Match Success Gauge --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6" data-stat-type="match-success">
                    <h2 class="text-xl font-bold text-[#123A7D] mb-4">AI Match Success</h2>
                    <div class="text-center my-4">
                        <div class="text-5xl font-bold {{ $matchSuccessRatePercent >= 80 ? 'text-green-600' : ($matchSuccessRatePercent >= 60 ? 'text-yellow-600' : 'text-red-600') }}" data-stat-value>
                            {{ $matchSuccessRatePercent }}
                        </div>
                        <p class="text-sm text-gray-600 mt-2">Claimed / Found Items</p>
                        <p class="text-xs {{ $matchSuccessGrowthPercent >= 0 ? 'text-green-500' : 'text-red-500' }} mt-1 flex items-center justify-center gap-1" data-stat-growth>
                            @if($matchSuccessGrowthPercent >= 0)
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                                +{{ abs($matchSuccessGrowthPercent) }}%
                            @else
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                {{ $matchSuccessGrowthPercent }}%
                            @endif
                            from last week
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Categories and Activity Section --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            {{-- Top Categories Chart --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-xl font-bold text-[#123A7D] mb-4">Top Categories</h2>
                <div class="relative" style="height: 400px;">
                    <canvas id="topCategoriesChart"></canvas>
                </div>
            </div>

            {{-- Pending Actions Section --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-xl font-bold text-[#123A7D] mb-4">Pending Actions</h2>
                <div class="space-y-4 overflow-y-auto" style="max-height: 360px;">
                    {{-- Pending Claims --}}
                    <div id="pending-claims-container">
                    @if($pendingClaimsList->count() > 0)
                        <div class="mb-4">
                            <p class="text-sm font-semibold text-gray-700 mb-2">Pending Claims ({{ $pendingClaimsList->count() }})</p>
                            @foreach($pendingClaimsList as $claim)
                                <div class="flex items-center justify-between p-3 bg-amber-50 rounded-lg mb-2 hover:bg-amber-100 transition-colors">
                                    <div class="flex-1 min-w-0">
                                        <p class="font-semibold text-sm text-gray-800 truncate">{{ $claim->title }}</p>
                                        <p class="text-xs text-gray-500">
                                            Claimed by: {{ $claim->claimedBy ? $claim->claimedBy->name : 'Unknown' }}
                                            @if($claim->claimed_at)
                                                • {{ $claim->claimed_at->diffForHumans() }}
                                            @endif
                                        </p>
                        </div>
                                    <a href="{{ route('admin.claims.index', ['tab' => 'pending']) }}" class="ml-2 text-xs bg-amber-600 text-white px-3 py-1 rounded-full hover:bg-amber-700 whitespace-nowrap">Review</a>
                        </div>
                            @endforeach
                        </div>
                    @endif
                    </div>

                    {{-- Items Near Collection Deadline --}}
                    <div id="items-deadline-container">
                    @if($itemsNearDeadline->count() > 0)
                        <div>
                            <p class="text-sm font-semibold text-gray-700 mb-2">Near Collection Deadline ({{ $itemsNearDeadline->count() }})</p>
                            @foreach($itemsNearDeadline as $item)
                                <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg mb-2 hover:bg-yellow-100 transition-colors">
                                    <div class="flex-1 min-w-0">
                                        <p class="font-semibold text-sm text-gray-800 truncate">{{ $item->title }}</p>
                                        <p class="text-xs text-gray-500">
                                            @if($item->collection_deadline)
                                                Deadline: {{ $item->collection_deadline->format('M d, Y') }}
                                                ({{ $item->collection_deadline->diffForHumans() }})
                                            @endif
                                        </p>
                        </div>
                                    <a href="{{ route('admin.claims.index', ['tab' => 'approved']) }}" class="ml-2 text-xs bg-yellow-600 text-white px-3 py-1 rounded-full hover:bg-yellow-700 whitespace-nowrap">View</a>
                        </div>
                            @endforeach
                        </div>
                    @endif
                    </div>

                    @if($pendingClaimsList->count() == 0 && $itemsNearDeadline->count() == 0)
                        <div class="text-center py-8 text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-sm">No pending actions</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Recent Activity Log --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-xl font-bold text-[#123A7D] mb-4">Recent Activity</h2>
                <ul id="recent-activities-container" class="space-y-4 overflow-y-auto" style="max-height: 360px;">
                    @forelse($recentActivities as $activity)
                        @php
                            $activityColor = 'blue';
                            $activityIcon = 'M8 16l2.879-2.879a3 3 0 014.242 0L18 16m-6-8a3 3 0 100-6 3 3 0 000 6z';
                            if (str_contains(strtolower($activity->action), 'approve') || str_contains(strtolower($activity->action), 'claim approved')) {
                                $activityColor = 'green';
                                $activityIcon = 'M5 13l4 4L19 7';
                            } elseif (str_contains(strtolower($activity->action), 'reject') || str_contains(strtolower($activity->action), 'claim rejected')) {
                                $activityColor = 'red';
                                $activityIcon = 'M6 18L18 6M6 6l12 12';
                            } elseif (str_contains(strtolower($activity->action), 'match') || str_contains(strtolower($activity->action), 'ai')) {
                                $activityColor = 'purple';
                                $activityIcon = 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m12.728 0l-.707.707M12 21v-1m-6.657-3.343l.707-.707m12.728 0l.707.707';
                            } elseif (str_contains(strtolower($activity->action), 'user') || str_contains(strtolower($activity->action), 'register')) {
                                $activityColor = 'yellow';
                                $activityIcon = 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z';
                            }
                        @endphp
                        <li class="flex items-start space-x-3">
                            <div class="p-2 bg-{{ $activityColor }}-100 rounded-full flex-shrink-0">
                                <svg class="w-5 h-5 text-{{ $activityColor }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $activityIcon }}"></path>
                                </svg>
                        </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-600">
                                    @if($activity->user)
                                        <b>{{ $activity->user->name }}</b>
                                    @else
                                        <b>System</b>
                                    @endif
                                    {{ $activity->action }}
                                    @if($activity->details)
                                        <span class="text-gray-500">{{ Str::limit($activity->details, 50) }}</span>
                                    @endif
                                </p>
                                <p class="text-xs text-gray-400 mt-1">{{ $activity->created_at->diffForHumans() }}</p>
                        </div>
                    </li>
                    @empty
                        <li class="text-center py-8 text-gray-500">
                            <p class="text-sm">No recent activity</p>
                    </li>
                    @endforelse
                </ul>
            </div>
        </div>

        </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
    // Helper functions for charts
    const getChartTooltip = () => ({
        backgroundColor: 'rgba(0,0,0,0.8)',
        titleColor: 'white',
        bodyColor: 'white',
        borderColor: 'rgba(255,255,255,0.1)',
        borderWidth: 1,
        cornerRadius: 8,
        padding: 10,
    });

    const getChartTicksX = () => ({
        ticks: { color: '#6B7280', font: { size: 12, weight: '500' }, padding: 10 },
        grid: { display: false },
    });

    const getChartTicksY = () => ({
        beginAtZero: true,
        ticks: { color: '#6B7280', font: { size: 12, weight: '500' }, padding: 10 },
        grid: { color: '#F3F4F6', drawBorder: false }
    });

    // Posts Over Time Chart
    const postsCtx = document.getElementById('postsOverTimeChart').getContext('2d');
    const gradient = postsCtx.createLinearGradient(0, 0, 0, 350);
    gradient.addColorStop(0, 'rgba(59, 130, 246, 0.3)');
    gradient.addColorStop(1, 'rgba(59, 130, 246, 0)');

    let postsChart = new Chart(postsCtx, {
        type: 'line',
        data: {
            labels: @json($postsOverTimeData['days']),
            datasets: [
                {
                    label: 'Lost Items',
                    data: @json($postsOverTimeData['lost']),
                    borderColor: 'rgba(239, 68, 68, 1)',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    borderWidth: 2,
                    pointBackgroundColor: 'rgba(239, 68, 68, 1)',
                    pointRadius: 4,
                    tension: 0.4
                },
                {
                    label: 'Found Items',
                    data: @json($postsOverTimeData['found']),
                    borderColor: 'rgba(59, 130, 246, 1)',
                    backgroundColor: gradient,
                    fill: true,
                    borderWidth: 2,
                    pointBackgroundColor: 'rgba(59, 130, 246, 1)',
                    pointRadius: 4,
                    tension: 0.4
                }
        ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', align: 'start', labels: { usePointStyle: true, padding: 20 } },
                tooltip: getChartTooltip()
            },
            scales: {
                x: getChartTicksX(),
                y: getChartTicksY()
            }
        }
    });

    // Time Range Selector Functionality
    let currentTimeRange = '7d';
    
    function updateChartTimeRange(range) {
        currentTimeRange = range;
        
        // Update button styles
        document.querySelectorAll('.time-range-btn').forEach(btn => {
            btn.classList.remove('active', 'text-[#123A7D]', 'bg-blue-50');
            btn.classList.add('text-gray-600', 'hover:bg-gray-50');
        });
        
        const activeBtn = document.getElementById('timeRange' + range);
        if (activeBtn) {
            activeBtn.classList.add('active', 'text-[#123A7D]', 'bg-blue-50');
            activeBtn.classList.remove('text-gray-600', 'hover:bg-gray-50');
        }
        
        // Show loading state
        postsChart.data.datasets.forEach(dataset => {
            dataset.data = [];
        });
        postsChart.data.labels = [];
        postsChart.update('none');
        
        // Fetch new data
        fetch(`{{ route('dashboard.chartData') }}?range=${range}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            // Update chart data
            postsChart.data.labels = data.labels;
            postsChart.data.datasets[0].data = data.lost;
            postsChart.data.datasets[1].data = data.found;
            postsChart.update('active');
        })
        .catch(error => {
            console.error('Error fetching chart data:', error);
            
            // Show non-intrusive error notification
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Chart Update Failed',
                    text: 'Failed to load chart data. The chart will update on the next refresh.',
                    timer: 3000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end',
                });
            }
            
            // Log error for debugging
            console.error('Chart data fetch error details:', error);
        });
    }
    
        // Event listeners for time range buttons
    document.addEventListener('DOMContentLoaded', function() {
        const btn7d = document.getElementById('timeRange7d');
        const btn30d = document.getElementById('timeRange30d');
        const btn90d = document.getElementById('timeRange90d');
        
        if (btn7d) {
            btn7d.addEventListener('click', () => updateChartTimeRange('7d'));
        }
        if (btn30d) {
            btn30d.addEventListener('click', () => updateChartTimeRange('30d'));
        }
        if (btn90d) {
            btn90d.addEventListener('click', () => updateChartTimeRange('90d'));
        }
        
        // Auto-refresh functionality
        initAutoRefresh();
    });

    // Auto-refresh Dashboard Data
    let autoRefreshInterval = null;
    const AUTO_REFRESH_INTERVAL = 45000; // 45 seconds - optimal balance between real-time updates and server load
    
    function initAutoRefresh() {
        // Start auto-refresh
        autoRefreshInterval = setInterval(refreshDashboardData, AUTO_REFRESH_INTERVAL);
        
        // Also refresh on page visibility change (when user comes back to tab)
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                refreshDashboardData();
            }
        });
        
        // Initial refresh after interval
        setTimeout(refreshDashboardData, AUTO_REFRESH_INTERVAL);
    }
    
    function refreshDashboardData() {
        const refreshPulse = document.getElementById('refresh-pulse');
        
        // Show refreshing state
        if (refreshPulse) {
            refreshPulse.classList.remove('bg-green-500');
            refreshPulse.classList.add('bg-yellow-500', 'animate-pulse');
        }
        
        // Add subtle shimmer effect to stat cards during update
        const statCards = document.querySelectorAll('[data-stat-type]');
        statCards.forEach(card => {
            card.classList.add('opacity-90');
            card.style.transition = 'opacity 0.3s';
        });
        
        fetch('{{ route("dashboard.data") }}', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            updateDashboardStats(data.stats);
            updateActivityLog(data.recentActivities);
            updatePendingActions(data.pendingClaimsList, data.itemsNearDeadline);
            
            // Update last refresh time (text removed per user request - only show pulse indicator)
            if (refreshPulse) {
                refreshPulse.classList.remove('bg-yellow-500');
                refreshPulse.classList.add('bg-green-500');
            }
            
            // Remove shimmer effect from stat cards
            const statCards = document.querySelectorAll('[data-stat-type]');
            statCards.forEach(card => {
                card.classList.remove('opacity-90');
            });
        })
        .catch(error => {
            console.error('Error refreshing dashboard:', error);
            
            // Update visual indicator
            if (refreshPulse) {
                refreshPulse.classList.remove('bg-yellow-500', 'bg-green-500');
                refreshPulse.classList.add('bg-red-500');
            }
            
            // Show user-friendly error message (non-intrusive)
            // Only show if error persists (not for temporary network issues)
            if (typeof Swal !== 'undefined' && error.name !== 'TypeError') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Connection Error',
                    text: 'Failed to refresh dashboard data. Some information may be outdated.',
                    timer: 3000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end',
                });
            }
            
            // Auto-reset after delay (text removed per user request)
            setTimeout(() => {
                if (refreshPulse) {
                    refreshPulse.classList.remove('bg-red-500');
                    refreshPulse.classList.add('bg-green-500');
                }
            }, 5000);
        });
    }
    
    function updateDashboardStats(stats) {
        // Update Total Users
        updateStatCard('users', stats.totalUsers, stats.usersGrowthPercent, 'month');
        
        // Update Lost Items
        updateStatCard('lost', stats.lostItems, stats.lostGrowthPercent, 'week');
        
        // Update Found Items
        updateStatCard('found', stats.foundItems, stats.foundGrowthPercent, 'month');
        
        // Update Pending Claims
        updateStatCard('claims', stats.pendingClaims, null, null);
        
        // Update Successful Matches
        updateStatCard('matches', stats.successfulMatches, null, null);
        
        // Update Unclaimed Items
        updateStatCard('unclaimed', stats.unclaimedItems, null, null);
        
        // Update Collected This Month
        updateStatCard('collected', stats.collectedThisMonth, stats.collectedGrowthPercent, 'month');
        
        // Update AI Match Success Rate
        updateMatchSuccessRate(stats.matchSuccessRatePercent, stats.matchSuccessGrowthPercent);
    }
    
    function updateStatCard(type, value, growthPercent, period) {
        // Map type to element IDs
        const cardMap = {
            'users': { valueId: null, growthId: null }, // Will find by data attribute
            'lost': { valueId: null },
            'found': { valueId: null },
            'claims': { valueId: null },
            'matches': { valueId: null },
            'unclaimed': { valueId: null },
            'collected': { valueId: null },
        };
        
        // Find cards by data attributes and update
        const cards = document.querySelectorAll('[data-stat-type]');
        cards.forEach(card => {
            const statType = card.getAttribute('data-stat-type');
            if (statType === type) {
                const valueEl = card.querySelector('[data-stat-value]');
                const growthEl = card.querySelector('[data-stat-growth]');
                
                if (valueEl) {
                    // Animate number change
                    const oldValue = parseInt(valueEl.textContent.replace(/,/g, '')) || 0;
                    animateValue(valueEl, oldValue, value, 500);
                }
                
                if (growthEl && growthPercent !== null && growthPercent !== undefined) {
                    const isPositive = growthPercent >= 0;
                    const icon = growthEl.querySelector('svg');
                    const text = growthEl.querySelector('span:last-child') || growthEl;
                    
                    // Update growth percentage
                    if (text) {
                        const percentText = isPositive ? `+${Math.abs(growthPercent)}%` : `${growthPercent}%`;
                        text.textContent = percentText + (period ? ` from last ${period}` : '');
                    }
                    
                    // Update icon
                    if (icon) {
                        const iconPath = isPositive 
                            ? 'M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z'
                            : 'M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z';
                        const path = icon.querySelector('path');
                        if (path) {
                            path.setAttribute('d', iconPath);
                        }
                    }
                }
            }
        });
    }
    
    function animateValue(element, start, end, duration) {
        const startTime = performance.now();
        const difference = end - start;
        
        function update(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            // Easing function
            const easeOutQuad = 1 - Math.pow(1 - progress, 3);
            const current = Math.round(start + (difference * easeOutQuad));
            
            element.textContent = current.toLocaleString();
            
            if (progress < 1) {
                requestAnimationFrame(update);
            } else {
                element.textContent = end.toLocaleString();
            }
        }
        
        requestAnimationFrame(update);
    }
    
    function updateMatchSuccessRate(percent, growthPercent) {
        const gaugeEl = document.querySelector('[data-stat-type="match-success"]');
        if (gaugeEl) {
            const percentEl = gaugeEl.querySelector('[data-stat-value]');
            const growthEl = gaugeEl.querySelector('[data-stat-growth]');
            
            if (percentEl) {
                const oldPercent = parseInt(percentEl.textContent) || 0;
                animateValue(percentEl, oldPercent, percent, 500);
                
                // Update color based on percentage
                const colorClass = percent >= 80 ? 'text-green-600' : (percent >= 60 ? 'text-yellow-600' : 'text-red-600');
                percentEl.className = `text-5xl font-bold ${colorClass}`;
            }
            
            if (growthEl && growthPercent !== null) {
                const isPositive = growthPercent >= 0;
                const icon = growthEl.querySelector('svg');
                const text = growthEl.querySelector('span:last-child') || growthEl;
                
                if (text) {
                    const percentText = isPositive ? `+${Math.abs(growthPercent)}%` : `${growthPercent}%`;
                    text.textContent = percentText + ' from last week';
                }
            }
        }
    }
    
    function updateActivityLog(activities) {
        const activityContainer = document.getElementById('recent-activities-container');
        if (!activityContainer) return;
        
        // Clear and rebuild activity log
        activityContainer.innerHTML = '';
        
        activities.slice(0, 10).forEach(activity => {
            const iconMap = {
                'created': { path: 'M12 4v16m8-8H4', color: 'text-blue-500' },
                'updated': { path: 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z', color: 'text-yellow-500' },
                'deleted': { path: 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16', color: 'text-red-500' },
            };
            
            const icon = iconMap[activity.action.toLowerCase()] || { path: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z', color: 'text-gray-500' };
            
            const activityEl = document.createElement('div');
            activityEl.className = 'flex items-start gap-3 p-3 hover:bg-gray-50 rounded-lg transition-colors';
            activityEl.innerHTML = `
                <div class="flex-shrink-0 mt-1">
                    <svg class="w-5 h-5 ${icon.color}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${icon.path}"></path>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900">${escapeHtml(activity.action)}</p>
                    <p class="text-xs text-gray-500 mt-0.5">${escapeHtml(activity.user_name)} • ${activity.created_at_human}</p>
                    ${activity.details ? `<p class="text-xs text-gray-600 mt-1">${escapeHtml(activity.details)}</p>` : ''}
                </div>
            `;
            
            activityContainer.appendChild(activityEl);
        });
    }
    
    function updatePendingActions(pendingClaims, itemsNearDeadline) {
        // Update pending claims
        const claimsContainer = document.getElementById('pending-claims-container');
        if (claimsContainer) {
            claimsContainer.innerHTML = '';
            
            if (pendingClaims.length > 0) {
                pendingClaims.forEach(claim => {
                    const claimEl = document.createElement('div');
                    claimEl.className = 'flex items-center justify-between p-3 bg-amber-50 rounded-lg mb-2 hover:bg-amber-100 transition-colors';
                    claimEl.innerHTML = `
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-sm text-gray-800 truncate">${escapeHtml(claim.title)}</p>
                            <p class="text-xs text-gray-500">
                                Claimed by: ${escapeHtml(claim.claimed_by)}
                                ${claim.claimed_at_human ? ` • ${claim.claimed_at_human}` : ''}
                            </p>
                        </div>
                        <a href="/admin/claims?tab=pending" class="ml-2 text-xs bg-amber-600 text-white px-3 py-1 rounded-full hover:bg-amber-700 whitespace-nowrap">Review</a>
                    `;
                    claimsContainer.appendChild(claimEl);
                });
            } else {
                claimsContainer.innerHTML = '<p class="text-sm text-gray-500 text-center py-4">No pending claims</p>';
            }
        }
        
        // Update items near deadline
        const deadlineContainer = document.getElementById('items-deadline-container');
        if (deadlineContainer) {
            deadlineContainer.innerHTML = '';
            
            if (itemsNearDeadline.length > 0) {
                itemsNearDeadline.forEach(item => {
                    const itemEl = document.createElement('div');
                    itemEl.className = 'flex items-center justify-between p-3 bg-yellow-50 rounded-lg mb-2 hover:bg-yellow-100 transition-colors';
                    itemEl.innerHTML = `
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-sm text-gray-800 truncate">${escapeHtml(item.title)}</p>
                            <p class="text-xs text-gray-500">
                                Deadline: ${item.deadline || 'N/A'}
                                ${item.days_remaining !== null ? ` (${item.days_remaining} days left)` : ''}
                            </p>
                        </div>
                    `;
                    deadlineContainer.appendChild(itemEl);
                });
            } else {
                deadlineContainer.innerHTML = '<p class="text-sm text-gray-500 text-center py-4">No items near deadline</p>';
            }
        }
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Item Status Chart
    const statusCtx = document.getElementById('itemStatusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Active Lost', 'Active Found', 'Claimed'],
            datasets: [{
                data: [
                    {{ $itemStatusData['activeLost'] }},
                    {{ $itemStatusData['activeFound'] }},
                    {{ $itemStatusData['claimed'] }}
                ],
                backgroundColor: [
                    'rgba(239, 68, 68, 0.8)',
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(22, 163, 74, 0.8)'
                ],
                borderColor: '#FFFFFF',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true, padding: 15 } },
                tooltip: getChartTooltip()
            }
        }
    });

    // Top Categories Chart
    const categoriesCtx = document.getElementById('topCategoriesChart').getContext('2d');
    const topCategoriesData = @json($topCategories);
    const categoryLabels = topCategoriesData.map(cat => cat.name || 'Unknown');
    const categoryData = topCategoriesData.map(cat => cat.total_items || 0);

    new Chart(categoriesCtx, {
        type: 'bar',
        data: {
            labels: categoryLabels.length > 0 ? categoryLabels : ['No categories yet'],
            datasets: [{
                label: 'Number of Items',
                data: categoryData.length > 0 ? categoryData : [0],
                backgroundColor: 'rgba(18, 58, 125, 0.8)',
                borderColor: 'rgba(18, 58, 125, 1)',
                borderWidth: 1,
                borderRadius: 8,
                barThickness: 15,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: getChartTooltip()
            },
            scales: {
                x: getChartTicksY(),
                y: {
                    ticks: { color: '#6B7280', font: { size: 12, weight: '500' }, padding: 10 },
                    grid: { display: false }
                }
            }
        }
    });
</script>
@endsection
