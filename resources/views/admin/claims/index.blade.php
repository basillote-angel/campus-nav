@extends('layouts.app')

@section('content')
{{-- 
    Claims Management Page - Modernized UI
    Displays pending, approved, and rejected claims with filtering and action capabilities
--}}

<div class="min-h-full bg-gradient-to-br from-blue-50 via-white to-indigo-50">
    {{-- Page Header --}}
    <x-ui.page-header 
        title="Claims Management"
        description="Review and manage item claim requests from users"
    />

    {{-- Main Content Area --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-8">
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

        {{-- Collection Status Alert --}}
        @if(isset($collectionStats) && $collectionStats['pending_collection'] > 0)
            <x-ui.alert type="info" class="mb-6">
                <div class="flex items-center gap-4 flex-wrap">
                    <span><strong>Collection Status:</strong></span>
                    <span>{{ $collectionStats['pending_collection'] }} pending collection</span>
                    @if($collectionStats['deadline_passed'] > 0)
                        <span class="text-amber-700">💡 {{ $collectionStats['deadline_passed'] }} past suggested date</span>
                    @endif
                    <span class="text-green-700">✓ {{ $collectionStats['collected'] }} collected</span>
                </div>
            </x-ui.alert>
        @endif

        {{-- Search and Filter Section --}}
        <x-ui.card class="mb-6">
            <div class="space-y-4">
                {{-- Search Input --}}
                <div>
                    <form method="get" action="{{ route('admin.claims.index') }}" class="flex gap-3">
                        <input type="hidden" name="tab" value="{{ $tab }}">
                        <div class="flex-1 relative">
                            <input 
                                type="text" 
                                name="search" 
                                value="{{ request()->query('search') }}"
                                placeholder="Search items, claimants, descriptions..."
                                class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#123A7D]/50 focus:border-[#123A7D] text-sm"
                            >
                            <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <x-ui.button-primary type="submit" size="md">
                            Search
                        </x-ui.button-primary>
                        @if(request()->query('search'))
                            <a href="{{ route('admin.claims.index', ['tab' => $tab]) }}" class="px-4 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm font-medium">
                                Clear
                            </a>
                        @endif
                    </form>
                </div>

                {{-- Advanced Filters (Collapsible) --}}
                <div class="border-t border-gray-200 pt-4">
                    <button 
                        type="button" 
                        onclick="toggleFilters()"
                        class="w-full flex items-center justify-between text-left font-semibold text-gray-900 hover:text-[#123A7D] transition-colors py-2"
                    >
                        <span class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            Advanced Filters
                        </span>
                        <svg id="filter-arrow" class="w-5 h-5 transform transition-transform duration-300 {{ request()->hasAny(['category', 'claimant', 'date_from', 'date_to']) ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    
                    <div id="filterCollapse" class="{{ request()->hasAny(['category', 'claimant', 'date_from', 'date_to']) ? '' : 'hidden' }} mt-4 space-y-4">
                        <form method="get" action="{{ route('admin.claims.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                            <input type="hidden" name="tab" value="{{ $tab }}">
                            <input type="hidden" name="search" value="{{ request()->query('search') }}">
                            
                            {{-- Category Filter --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                                <select name="category" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#123A7D]/50 focus:border-[#123A7D] text-sm">
                                    <option value="">All Categories</option>
                                    @foreach($categories ?? [] as $category)
                                        <option value="{{ $category->id }}" {{ ($categoryId ?? '') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Claimant Filter --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Claimant</label>
                                <select name="claimant" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#123A7D]/50 focus:border-[#123A7D] text-sm">
                                    <option value="">All Claimants</option>
                                    @foreach($claimants ?? [] as $claimant)
                                        <option value="{{ $claimant->id }}" {{ ($claimantId ?? '') == $claimant->id ? 'selected' : '' }}>
                                            {{ $claimant->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Date From Filter --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                                <input 
                                    type="date" 
                                    name="date_from" 
                                    value="{{ $dateFrom ?? '' }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#123A7D]/50 focus:border-[#123A7D] text-sm"
                                >
                            </div>

                            {{-- Date To Filter --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                                <input 
                                    type="date" 
                                    name="date_to" 
                                    value="{{ $dateTo ?? '' }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#123A7D]/50 focus:border-[#123A7D] text-sm"
                                >
                            </div>

                            {{-- Filter Actions --}}
                            <div class="flex items-end gap-2">
                                <x-ui.button-primary type="submit" size="sm" class="flex-1">
                                    Apply Filters
                                </x-ui.button-primary>
                                <a 
                                    href="{{ route('admin.claims.index', ['tab' => $tab]) }}" 
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500/50 transition-colors"
                                >
                                    Clear
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </x-ui.card>

        {{-- Tab Navigation --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
            <div class="border-b border-gray-200">
                <nav class="flex -mb-px" aria-label="Tabs">
                    {{-- Pending Tab --}}
                    <a 
                        href="?tab=pending" 
                        class="{{ $tab === 'pending' ? 'border-[#123A7D] text-[#123A7D] bg-blue-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} flex-1 whitespace-nowrap py-4 px-4 text-center border-b-2 font-medium text-sm transition-colors"
                    >
                        Pending
                        @if($pending->count() > 0)
                            <x-ui.badge variant="warning" size="sm" class="ml-2">
                                {{ $pending->count() }}
                            </x-ui.badge>
                        @endif
                    </a>
                    
                    {{-- Approved Tab --}}
                    <a 
                        href="?tab=approved" 
                        class="{{ $tab === 'approved' ? 'border-green-600 text-green-600 bg-green-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} flex-1 whitespace-nowrap py-4 px-4 text-center border-b-2 font-medium text-sm transition-colors"
                    >
                        Approved
                        @if($approved->count() > 0)
                            <x-ui.badge variant="success" size="sm" class="ml-2">
                                {{ $approved->count() }}
                            </x-ui.badge>
                        @endif
                    </a>
                    
                    {{-- Rejected Tab --}}
                    <a 
                        href="?tab=rejected" 
                        class="{{ $tab === 'rejected' ? 'border-red-600 text-red-600 bg-red-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} flex-1 whitespace-nowrap py-4 px-4 text-center border-b-2 font-medium text-sm transition-colors"
                    >
                        Rejected
                    </a>
                </nav>
            </div>
        </div>

        {{-- Claims List --}}
        @php
            // Determine which list to display based on active tab
            $list = $tab === 'approved' ? $approved : ($tab === 'rejected' ? $rejected : $pending);
        @endphp

        @if($list->isEmpty())
            {{-- Empty State --}}
            <x-ui.card>
                <div class="text-center py-12">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">No records found</h3>
                    <p class="text-gray-600">There are no {{ $tab }} claims at this time.</p>
                </div>
            </x-ui.card>
        @else
            {{-- Bulk Actions Toolbar (Only for Pending Tab) --}}
            @if($tab === 'pending')
                <div id="bulkActionsToolbar" class="hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-lg z-40 p-4">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <span class="text-sm font-medium text-gray-700">
                                <span id="selectedCount">0</span> item(s) selected
                            </span>
                            <button 
                                onclick="clearSelection()"
                                class="text-sm text-gray-600 hover:text-gray-900 underline"
                            >
                                Clear selection
                            </button>
                        </div>
                        <div class="flex items-center gap-2">
                            <button 
                                onclick="bulkApprove()"
                                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm font-medium flex items-center gap-2"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Bulk Approve
                            </button>
                            <button 
                                onclick="bulkReject()"
                                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm font-medium flex items-center gap-2"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Bulk Reject
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Claims Table --}}
            <x-ui.card class="overflow-hidden p-0 {{ $tab === 'pending' ? 'mb-20' : '' }}">
                {{-- Mobile: Show scroll hint, Desktop: Auto scroll --}}
                <div class="w-full overflow-x-auto table-scroll-hint" style="-webkit-overflow-scrolling: touch;">
                    <table class="w-full divide-y divide-gray-200">
                        <thead class="bg-gradient-to-r from-[#123A7D] to-[#10316A]">
                            <tr>
                                @if($tab === 'pending')
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider w-12">
                                        <input 
                                            type="checkbox" 
                                            id="selectAll" 
                                            class="w-4 h-4 text-[#123A7D] bg-white border-gray-300 rounded focus:ring-[#123A7D] focus:ring-2 cursor-pointer"
                                            onclick="toggleSelectAll()"
                                        />
                                    </th>
                                @endif
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Item</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Claimant</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Message</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Dates</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider bg-gradient-to-r from-[#123A7D] to-[#10316A] sticky right-0 z-10 shadow-lg">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($list as $item)
                                <tr 
                                    class="hover:bg-blue-50 cursor-pointer transition-colors duration-200 border-l-4 border-transparent hover:border-[#123A7D]"
                                    onclick="showClaimModal({{ json_encode([
                                        'id' => $item->id,
                                        'title' => $item->title,
                                        'description' => $item->description,
                                        'category' => $item->category ? $item->category->name : 'N/A',
                                        'location' => $item->location,
                                        'status' => $item->status,
                                        'claim_message' => $item->claim_message,
                                        'claimant_name' => $item->claimedBy ? $item->claimedBy->name : 'Unknown',
                                        'claimant_email' => $item->claimedBy ? $item->claimedBy->email : null,
                                        'claimed_at' => $item->claimed_at ? $item->claimed_at->toDateTimeString() : null,
                                        'approved_at' => $item->approved_at ? $item->approved_at->toDateTimeString() : null,
                                        'rejected_at' => $item->rejected_at ? $item->rejected_at->toDateTimeString() : null,
                                        'rejection_reason' => $item->rejection_reason,
                                        'collection_deadline' => $item->collection_deadline ? $item->collection_deadline->toDateString() : null,
                                        'collected_at' => $item->collected_at ? $item->collected_at->toDateTimeString() : null,
                                        'created_at' => $item->created_at ? $item->created_at->toDateTimeString() : null,
                                        'updated_at' => $item->updated_at ? $item->updated_at->toDateTimeString() : null,
                                        'tab' => $tab,
                                        'multiple_claims' => isset($itemsWithMultipleClaims[$item->id]) && $itemsWithMultipleClaims[$item->id]->count() > 1,
                                        'claims_count' => isset($itemsWithMultipleClaims[$item->id]) ? $itemsWithMultipleClaims[$item->id]->count() : 1,
                                    ]) }})"
                                >
                                    @if($tab === 'pending')
                                        <td class="px-4 py-3 whitespace-nowrap" onclick="event.stopPropagation()">
                                            <input 
                                                type="checkbox" 
                                                class="claim-checkbox w-4 h-4 text-[#123A7D] bg-white border-gray-300 rounded focus:ring-[#123A7D] focus:ring-2 cursor-pointer"
                                                data-item-id="{{ $item->id }}"
                                                onchange="updateBulkActions()"
                                            />
                                        </td>
                                    @endif
                                    
                                    {{-- Item Column --}}
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-sm font-semibold text-gray-900 truncate max-w-xs" title="{{ $item->title }}">
                                            {{ $item->title }}
                                        </div>
                                        <div class="text-xs text-gray-500 truncate max-w-xs" title="{{ ($item->category ? $item->category->name : 'N/A') . ' • ' . $item->location }}">
                                            {{ $item->category ? $item->category->name : 'N/A' }} • {{ $item->location }}
                                        </div>
                                        @if(isset($itemsWithMultipleClaims[$item->id]) && $itemsWithMultipleClaims[$item->id]->count() > 1)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 mt-1">
                                                {{ $itemsWithMultipleClaims[$item->id]->count() }} Claims
                                            </span>
                                        @endif
                                    </td>
                                    
                                    {{-- Claimant Column --}}
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        @if(isset($itemsWithMultipleClaims[$item->id]) && $itemsWithMultipleClaims[$item->id]->count() > 1)
                                            <div class="text-sm font-medium text-gray-900 truncate max-w-xs" title="{{ $itemsWithMultipleClaims[$item->id]->count() }} claimants">
                                                {{ $itemsWithMultipleClaims[$item->id]->count() }} Claimants
                                            </div>
                                        @elseif($item->claimedBy)
                                            <div class="text-sm font-medium text-gray-900 truncate max-w-xs" title="{{ $item->claimedBy->name }}">
                                                {{ $item->claimedBy->name }}
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-400">—</span>
                                        @endif
                                    </td>
                                    
                                    {{-- Message Column --}}
                                    <td class="px-4 py-3 max-w-xs">
                                        <div class="text-sm text-gray-600 truncate" title="{{ $item->claim_message ?: '—' }}">
                                            {{ $item->claim_message ? Str::limit($item->claim_message, 60) : '—' }}
                                        </div>
                                    </td>
                                    
                                    {{-- Dates Column --}}
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-sm text-gray-700">
                                            Claimed: {{ $item->claimed_at ? $item->claimed_at->diffForHumans() : '—' }}
                                        </div>
                                        @if($tab === 'approved' && $item->approved_at)
                                            <div class="text-xs text-gray-600 truncate max-w-xs" title="{{ $item->approved_at->format('M d, Y g:i A') }}">
                                                Approved: {{ $item->approved_at->diffForHumans() }}
                                            </div>
                                        @elseif($tab === 'rejected' && $item->rejected_at)
                                            <div class="text-xs text-red-600 truncate max-w-xs" title="{{ $item->rejected_at->format('M d, Y g:i A') }}">
                                                Rejected: {{ $item->rejected_at->diffForHumans() }}
                                            </div>
                                        @endif
                                    </td>
                                    
                                    {{-- Actions Column --}}
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium bg-white" onclick="event.stopPropagation()">
                                        <div class="flex items-center gap-2">
                                            @if($tab === 'pending')
                                                <form method="post" action="{{ route('admin.claims.approve', $item->id) }}" class="inline" onsubmit="event.stopPropagation(); return confirm('Approve this claim?');">
                                                    @csrf
                                                    <button type="submit" class="px-3 py-1.5 text-xs font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors">
                                                        Approve
                                                    </button>
                                                </form>
                                                <button 
                                                    type="button" 
                                                    onclick="event.stopPropagation(); openRejectModal({{ $item->id }}, '{{ addslashes($item->title) }}')"
                                                    class="px-3 py-1.5 text-xs font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors"
                                                >
                                                    Reject
                                                </button>
                                            @elseif($tab === 'approved')
                                                @if(!$item->collected_at)
                                                    <form method="post" action="{{ route('admin.claims.markCollected', $item->id) }}" class="inline" onsubmit="event.stopPropagation(); return confirm('Mark this item as collected?');">
                                                        @csrf
                                                        <button type="submit" class="px-3 py-1.5 text-xs font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors">
                                                            Mark Collected
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        Collected
                                                    </span>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-ui.card>
        @endif
    </div>
</div>

{{-- Reject Modal (Reusable) --}}
<div 
    id="rejectModal"
    class="fixed inset-0 bg-black/50 hidden items-center justify-center z-[10000]"
    onclick="if(event.target === this) hideModal('rejectModal')"
>
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4 transform transition-all duration-300 scale-95 opacity-0" id="rejectModalContent">
        {{-- Modal Header --}}
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Reject Claim</h3>
            <button 
                type="button"
                onclick="hideModal('rejectModal')"
                class="text-gray-400 hover:text-gray-600 transition-colors"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        
        {{-- Modal Body --}}
        <form method="post" id="rejectForm" class="px-6 py-4 space-y-4">
            @csrf
            <div>
                <label for="reject-reason" class="block text-sm font-medium text-gray-700 mb-2">
                    Rejection Reason <span class="text-red-500">*</span>
                </label>
                <textarea 
                    id="reject-reason" 
                    name="reason" 
                    rows="4" 
                    placeholder="Please provide a reason for rejecting this claim. This reason will be sent to the claimant."
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500/50 focus:border-red-500 resize-none"
                    required
                ></textarea>
                <p class="mt-1 text-xs text-gray-500">Maximum 1000 characters</p>
            </div>
        </form>
        
        {{-- Modal Footer --}}
        <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-3">
            <button 
                type="button"
                onclick="hideModal('rejectModal')"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500/50 focus:ring-offset-2 transition-colors"
            >
                Cancel
            </button>
            <button 
                type="submit" 
                form="rejectForm"
                class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500/50 focus:ring-offset-2 transition-colors"
            >
                Reject Claim
            </button>
        </div>
    </div>
</div>

{{-- JavaScript for Filter Toggle and Modal Management --}}
@push('scripts')
<script>
    // Toggle filter collapse
    function toggleFilters() {
        const filterCollapse = document.getElementById('filterCollapse');
        const filterArrow = document.getElementById('filter-arrow');
        
        if (filterCollapse.classList.contains('hidden')) {
            filterCollapse.classList.remove('hidden');
            filterArrow.classList.add('rotate-180');
        } else {
            filterCollapse.classList.add('hidden');
            filterArrow.classList.remove('rotate-180');
        }
    }
    
    // Open reject modal with item-specific data
    function openRejectModal(itemId, itemTitle) {
        const modal = document.getElementById('rejectModal');
        const form = document.getElementById('rejectForm');
        const actionUrl = '{{ route("admin.claims.reject", ":id") }}'.replace(':id', itemId);
        
        // Update form action
        if (form) {
            form.action = actionUrl;
        }
        
        // Show modal using the reusable modal function
        if (typeof showModal === 'function') {
            showModal('rejectModal');
        } else {
            // Fallback if showModal is not defined
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            const content = document.getElementById('rejectModalContent');
            if (content) {
                setTimeout(() => {
                    content.classList.remove('scale-95', 'opacity-0');
                    content.classList.add('scale-100', 'opacity-100');
                }, 10);
            }
        }
        
        // Focus textarea
        setTimeout(() => {
            const textarea = document.getElementById('reject-reason');
            if (textarea) {
                textarea.focus();
            }
        }, 300);
    }
    
    // Bulk Actions Functions
    function toggleSelectAll() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.claim-checkbox');
        
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAll.checked;
        });
        
        updateBulkActions();
    }
    
    function updateBulkActions() {
        const checked = document.querySelectorAll('.claim-checkbox:checked');
        const toolbar = document.getElementById('bulkActionsToolbar');
        const countSpan = document.getElementById('selectedCount');
        const selectAll = document.getElementById('selectAll');
        
        if (checked.length > 0 && toolbar && countSpan) {
            toolbar.classList.remove('hidden');
            countSpan.textContent = checked.length;
            
            // Update select all checkbox state
            if (selectAll) {
                selectAll.indeterminate = checked.length < document.querySelectorAll('.claim-checkbox').length;
                selectAll.checked = checked.length === document.querySelectorAll('.claim-checkbox').length;
            }
        } else if (toolbar) {
            toolbar.classList.add('hidden');
            if (selectAll) {
                selectAll.checked = false;
                selectAll.indeterminate = false;
            }
        }
    }
    
    function clearSelection() {
        const checkboxes = document.querySelectorAll('.claim-checkbox');
        const selectAll = document.getElementById('selectAll');
        
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        
        if (selectAll) {
            selectAll.checked = false;
            selectAll.indeterminate = false;
        }
        
        updateBulkActions();
    }
    
    async function bulkApprove() {
        const checked = Array.from(document.querySelectorAll('.claim-checkbox:checked'))
            .map(cb => parseInt(cb.dataset.itemId));
        
        if (checked.length === 0) return;
        
        if (typeof Swal !== 'undefined') {
            const result = await Swal.fire({
                title: `Approve ${checked.length} claim(s)?`,
                text: 'This action will approve all selected claims. This cannot be undone.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#6b7280',
                confirmButtonText: `Yes, approve ${checked.length}`,
                cancelButtonText: 'Cancel'
            });
            
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
                        const response = await fetch(`/admin/claims/${itemId}/approve`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Content-Type': 'application/json',
                            },
                        });
                        
                        if (response.ok) {
                            successCount++;
                        } else {
                            failCount++;
                        }
                    } catch (error) {
                        failCount++;
                    }
                }
                
                // Show results and reload
                Swal.fire({
                    icon: failCount === 0 ? 'success' : 'warning',
                    title: failCount === 0 ? 'Success!' : 'Completed with errors',
                    text: `${successCount} approved successfully${failCount > 0 ? `, ${failCount} failed` : ''}`,
                    confirmButtonText: 'OK'
                }).then(() => {
                    location.reload();
                });
            }
        } else {
            // Fallback without SweetAlert
            if (confirm(`Approve ${checked.length} claim(s)?`)) {
                checked.forEach(itemId => {
                    fetch(`/admin/claims/${itemId}/approve`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                    });
                });
                setTimeout(() => location.reload(), 1000);
            }
        }
    }
    
    async function bulkReject() {
        const checked = Array.from(document.querySelectorAll('.claim-checkbox:checked'))
            .map(cb => parseInt(cb.dataset.itemId));
        
        if (checked.length === 0) return;
        
        if (typeof Swal !== 'undefined') {
            const { value: reason } = await Swal.fire({
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
            });
            
            if (reason) {
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
                        const response = await fetch(`/admin/claims/${itemId}/reject`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({ reason: reason })
                        });
                        
                        if (response.ok) {
                            successCount++;
                        } else {
                            failCount++;
                        }
                    } catch (error) {
                        failCount++;
                    }
                }
                
                // Show results and reload
                Swal.fire({
                    icon: failCount === 0 ? 'success' : 'warning',
                    title: failCount === 0 ? 'Success!' : 'Completed with errors',
                    text: `${successCount} rejected successfully${failCount > 0 ? `, ${failCount} failed` : ''}`,
                    confirmButtonText: 'OK'
                }).then(() => {
                    location.reload();
                });
            }
        } else {
            // Fallback without SweetAlert
            const reason = prompt(`Reject ${checked.length} claim(s). Enter rejection reason:`);
            if (reason && reason.trim()) {
                checked.forEach(itemId => {
                    fetch(`/admin/claims/${itemId}/reject`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ reason: reason })
                    });
                });
                setTimeout(() => location.reload(), 1000);
            }
        }
    }
    
    // Show Claim Details Modal
    function showClaimModal(claim) {
        // Format dates
        const formatDate = (dateString) => {
            if (!dateString) return 'N/A';
            const date = new Date(dateString);
            return date.toLocaleString('en-US', { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        };

        const formatDateShort = (dateString) => {
            if (!dateString) return 'N/A';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric'
            });
        };

        // Status colors
        const statusColors = {
            'matched': 'bg-purple-100 text-purple-800',
            'returned': 'bg-green-100 text-green-800',
            'unclaimed': 'bg-blue-100 text-blue-800',
        };
        const statusColor = statusColors[claim.status] || 'bg-gray-100 text-gray-800';
        const statusLabel = (claim.status || '').charAt(0).toUpperCase() + (claim.status || '').slice(1);

        // Escape HTML to prevent XSS
        const escapeHtml = (text) => {
            if (!text) return 'N/A';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        };

        // Create modal HTML
        const modalHTML = `
            <div id="claimDetailModal" class="fixed inset-0 bg-black/50 z-[10000] flex items-center justify-center p-4" onclick="closeClaimModal(event)">
                <div class="bg-white rounded-xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
                    <div class="bg-gradient-to-r from-[#123A7D] to-[#10316A] text-white px-6 py-4 rounded-t-xl flex items-center justify-between sticky top-0 z-10">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-white/20 rounded-lg">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold">Claim Details</h2>
                                <p class="text-sm text-white/80">Complete information about this claim</p>
                            </div>
                        </div>
                        <button onclick="closeClaimModal()" class="text-white hover:text-gray-200 transition-colors p-2 hover:bg-white/10 rounded-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="p-6">
                        <div class="space-y-6">
                            {{-- Item Header Section --}}
                            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6 border border-blue-100">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex-1">
                                        <h3 class="text-2xl font-bold text-gray-900 mb-2">${escapeHtml(claim.title || 'Unnamed Item')}</h3>
                                        <div class="flex items-center gap-3 flex-wrap">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold ${statusColor}">
                                                ${statusLabel}
                                            </span>
                                            ${claim.category ? `
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800">
                                                    ${escapeHtml(claim.category)}
                                                </span>
                                            ` : ''}
                                            ${claim.multiple_claims ? `
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-orange-100 text-orange-800">
                                                    ⚠️ ${claim.claims_count} Claims
                                                </span>
                                            ` : ''}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Claim Information --}}
                            <div class="bg-white border border-gray-200 rounded-xl p-6">
                                <div class="flex items-center gap-2 mb-4">
                                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <h3 class="text-lg font-semibold text-gray-900">Claim Information</h3>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 uppercase mb-1.5">Claimant</label>
                                        <p class="text-sm text-gray-900 font-medium">${escapeHtml(claim.claimant_name)}</p>
                                        ${claim.claimant_email ? `<p class="text-xs text-gray-500 mt-1">${escapeHtml(claim.claimant_email)}</p>` : ''}
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 uppercase mb-1.5">Claimed At</label>
                                        <p class="text-sm text-gray-900 font-medium">${formatDate(claim.claimed_at)}</p>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-xs font-medium text-gray-500 uppercase mb-1.5">Claim Message</label>
                                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                            <p class="text-sm text-gray-700 whitespace-pre-wrap">${escapeHtml(claim.claim_message || 'No message provided.')}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Item Details --}}
                            <div class="bg-white border border-gray-200 rounded-xl p-6">
                                <div class="flex items-center gap-2 mb-4">
                                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                    </svg>
                                    <h3 class="text-lg font-semibold text-gray-900">Item Details</h3>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 uppercase mb-1.5">Location</label>
                                        <p class="text-sm text-gray-900 font-medium">${escapeHtml(claim.location || 'Not specified')}</p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 uppercase mb-1.5">Category</label>
                                        <p class="text-sm text-gray-900 font-medium">${escapeHtml(claim.category)}</p>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-xs font-medium text-gray-500 uppercase mb-1.5">Description</label>
                                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                            <p class="text-sm text-gray-700 whitespace-pre-wrap">${escapeHtml(claim.description || 'No description provided.')}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            ${claim.tab === 'approved' ? `
                                {{-- Approval & Collection Information --}}
                                <div class="bg-white border border-gray-200 rounded-xl p-6">
                                    <div class="flex items-center gap-2 mb-4">
                                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <h3 class="text-lg font-semibold text-gray-900">Approval & Collection</h3>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 uppercase mb-1.5">Approved At</label>
                                            <p class="text-sm text-gray-900 font-medium">${formatDate(claim.approved_at)}</p>
                                        </div>
                                        ${claim.collection_deadline ? `
                                            <div>
                                                <label class="block text-xs font-medium text-gray-500 uppercase mb-1.5">Collection Deadline</label>
                                                <p class="text-sm text-gray-900 font-medium">${formatDateShort(claim.collection_deadline)}</p>
                                            </div>
                                        ` : ''}
                                        ${claim.collected_at ? `
                                            <div class="md:col-span-2">
                                                <label class="block text-xs font-medium text-gray-500 uppercase mb-1.5">Collected At</label>
                                                <p class="text-sm text-green-700 font-semibold">✓ ${formatDate(claim.collected_at)}</p>
                                            </div>
                                        ` : ''}
                                    </div>
                                </div>
                            ` : ''}

                            ${claim.tab === 'rejected' ? `
                                {{-- Rejection Information --}}
                                <div class="bg-white border border-red-200 rounded-xl p-6 bg-red-50">
                                    <div class="flex items-center gap-2 mb-4">
                                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                        <h3 class="text-lg font-semibold text-red-900">Rejection Information</h3>
                                    </div>
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-xs font-medium text-red-700 uppercase mb-1.5">Rejected At</label>
                                            <p class="text-sm text-red-900 font-medium">${formatDate(claim.rejected_at)}</p>
                                        </div>
                                        ${claim.rejection_reason ? `
                                            <div>
                                                <label class="block text-xs font-medium text-red-700 uppercase mb-1.5">Rejection Reason</label>
                                                <div class="bg-white rounded-lg p-4 border border-red-200">
                                                    <p class="text-sm text-red-800 whitespace-pre-wrap">${escapeHtml(claim.rejection_reason)}</p>
                                                </div>
                                            </div>
                                        ` : ''}
                                    </div>
                                </div>
                            ` : ''}

                            {{-- Timeline Section --}}
                            <div class="bg-white border border-gray-200 rounded-xl p-6">
                                <div class="flex items-center gap-2 mb-4">
                                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <h3 class="text-lg font-semibold text-gray-900">Timeline</h3>
                                </div>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 uppercase mb-1.5">Created At</label>
                                        <p class="text-sm text-gray-900 font-medium">${formatDate(claim.created_at)}</p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 uppercase mb-1.5">Last Updated</label>
                                        <p class="text-sm text-gray-900 font-medium">${formatDate(claim.updated_at)}</p>
                                    </div>
                                </div>
                            </div>

                            ${claim.tab === 'pending' ? `
                                {{-- Quick Actions --}}
                                <div class="bg-gray-50 border border-gray-200 rounded-xl p-6">
                                    <div class="flex flex-wrap gap-3">
                                        <form method="post" action="/admin/claims/${claim.id}/approve" class="flex-1 min-w-[140px]" onsubmit="event.preventDefault(); if(confirm('Approve this claim?')) { const form = this; fetch(form.action, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=\\'csrf-token\\']').getAttribute('content') }, body: new FormData(form) }).then(() => { location.reload(); }); }">
                                            <button type="submit" class="w-full px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm font-semibold flex items-center justify-center gap-2 shadow-sm hover:shadow-md">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                Approve Claim
                                            </button>
                                        </form>
                                        <button onclick="closeClaimModal(); openRejectModal(${claim.id}, '${claim.title.replace(/'/g, "\\'")}');" class="flex-1 min-w-[140px] px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm font-semibold flex items-center justify-center gap-2 shadow-sm hover:shadow-md">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                            Reject Claim
                                        </button>
                                    </div>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing modal if any
        const existingModal = document.getElementById('claimDetailModal');
        if (existingModal) {
            existingModal.remove();
        }
        
        // Insert modal into body
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        document.body.style.overflow = 'hidden';
    }

    // Close Claim Modal
    function closeClaimModal(event) {
        if (event && event.target !== event.currentTarget) {
            return;
        }
        const modal = document.getElementById('claimDetailModal');
        if (modal) {
            modal.remove();
            document.body.style.overflow = '';
        }
    }
    
    // Ensure hideModal is available globally
    if (typeof hideModal === 'undefined') {
        window.hideModal = function(modalId) {
            const modal = document.getElementById(modalId);
            const content = document.getElementById(modalId + 'Content');
            if (modal && content) {
                content.classList.remove('scale-100', 'opacity-100');
                content.classList.add('scale-95', 'opacity-0');
                setTimeout(() => {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }, 300);
            }
        };
    }
    
    // Ensure showModal is available globally
    if (typeof showModal === 'undefined') {
        window.showModal = function(modalId) {
            const modal = document.getElementById(modalId);
            const content = document.getElementById(modalId + 'Content');
            if (modal && content) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                setTimeout(() => {
                    content.classList.remove('scale-95', 'opacity-0');
                    content.classList.add('scale-100', 'opacity-100');
                }, 10);
            }
        };
    }
</script>
@endpush

@endsection
