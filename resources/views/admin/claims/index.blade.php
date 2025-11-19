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
                    
                    {{-- Collected Tab --}}
                    <a 
                        href="?tab=collected" 
                        class="{{ $tab === 'collected' ? 'border-emerald-600 text-emerald-600 bg-emerald-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} flex-1 whitespace-nowrap py-4 px-4 text-center border-b-2 font-medium text-sm transition-colors"
                    >
                        Collected
                        @if($collected->count() > 0)
                            <x-ui.badge variant="success" size="sm" class="ml-2">
                                {{ $collected->count() }}
                            </x-ui.badge>
                        @endif
                    </a>
                    
                </nav>
            </div>
        </div>

        {{-- Claims List --}}
        @php
            // Determine which list to display based on active tab
            $list = $tab === 'approved'
                ? $approved
                : ($tab === 'collected' ? $collected : $pending);
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
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($list as $item)
                                @php
                                    $claimPayload = [
                                        'id' => $item->id,
                                        'title' => $item->title,
                                        'description' => $item->description,
                                        'category' => optional($item->category)->name,
                                        'location' => $item->location,
                                        'status' => $item->status,
                                        'claimMessage' => $item->claim_message,
                                        'claimantName' => optional($item->claimedBy)->name ?? 'Unknown',
                                        'claimantEmail' => optional($item->claimedBy)->email,
                                        'claimantContactName' => $item->claimant_contact_name,
                                        'claimantContactInfo' => $item->claimant_contact_info,
                                        'claimedAt' => optional($item->claimed_at)->toDateTimeString(),
                                        'approvedAt' => optional($item->approved_at)->toDateTimeString(),
                                        'rejectedAt' => optional($item->rejected_at)->toDateTimeString(),
                                        'rejectionReason' => $item->rejection_reason,
                                        'collectionDeadline' => optional($item->collection_deadline)->toDateTimeString(),
                                        'collectedAt' => optional($item->collected_at)->toDateTimeString(),
                                        'lastCollectionReminderAt' => optional($item->last_collection_reminder_at)->toDateTimeString(),
                                        'collectionReminderStage' => $item->collection_reminder_stage,
                                        'overdueNotifiedAt' => optional($item->overdue_notified_at)->toDateTimeString(),
										'collectedByName' => optional($item->collectedBy)->name,
										'collectionNotes' => $item->collection_notes,
                                        'createdAt' => optional($item->created_at)->toDateTimeString(),
                                        'updatedAt' => optional($item->updated_at)->toDateTimeString(),
                                        'tab' => $tab,
                                        'multipleClaims' => isset($itemsWithMultipleClaims[$item->id]) && $itemsWithMultipleClaims[$item->id]->count() > 1,
                                        'claimsCount' => isset($itemsWithMultipleClaims[$item->id]) ? $itemsWithMultipleClaims[$item->id]->count() : 1,
                                        'isOverdue' => $item->isCollectionDeadlinePassed(),
                                        'approveUrl' => route('admin.claims.approve', $item->id),
                                        'rejectUrl' => route('admin.claims.reject', $item->id),
                                        'markCollectedUrl' => route('admin.claims.markCollected', $item->id),
                                        'cancelUrl' => route('admin.claims.cancel', $item->id),
                                        'sendReminderUrl' => route('admin.claims.sendReminder', $item->id),
                                    ];

                                    if (isset($pendingClaimsByItem[$item->id])) {
                                        $claimPayload['claims'] = $pendingClaimsByItem[$item->id]->values();
                                    }

                                    if ($tab === 'approved') {
                                        $claimPayload['otherClaims'] = $item->claims
                                            ?->where('status', 'rejected')
                                            ->map(function ($claim) {
                                                return [
                                                    'claimantName' => optional($claim->claimant)->name ?? 'Unknown',
                                                    'claimantEmail' => optional($claim->claimant)->email,
                                                    'createdAt' => optional($claim->created_at)->toDateTimeString(),
                                                    'rejectionReason' => $claim->rejection_reason,
                                                    'notifiedAt' => optional($claim->rejected_at)->toDateTimeString(),
                                                ];
                                            })
                                            ->values();
                                    }
                                @endphp
                                <tr 
                                    class="hover:bg-blue-50 cursor-pointer transition-colors duration-200 border-l-4 border-transparent hover:border-[#123A7D]"
                                    onclick='showClaimModal(@json($claimPayload))'
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
                                        @if($tab === 'approved' && $item->collection_deadline)
                                            <div class="mt-1 flex items-center gap-2 flex-wrap text-xs text-gray-600">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-blue-100 text-blue-800">
                                                    Deadline: {{ $item->collection_deadline->format('M d, Y') }}
                                                </span>
                                                @if($item->isCollectionDeadlinePassed())
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-red-100 text-red-700">
                                                        Overdue
                                                    </span>
                                                @endif
                                            </div>
                                        @elseif($tab === 'collected' && $item->collected_at)
                                            <div class="mt-1 flex items-center gap-2 flex-wrap text-xs text-emerald-700">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700">
                                                    Collected {{ $item->collected_at->format('M d, Y') }}
                                                </span>
                                                @if(optional($item->collectedBy)->name)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-gray-100 text-gray-700">
                                                        By {{ optional($item->collectedBy)->name }}
                                                    </span>
                                                @endif
                                            </div>
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
                                            @if($item->collection_deadline)
                                                <div class="text-xs text-gray-600 truncate max-w-xs" title="{{ $item->collection_deadline->format('M d, Y g:i A') }}">
                                                    Deadline: {{ $item->collection_deadline->format('M d, Y') }}
                                                </div>
                                            @endif
                                            @if($item->collected_at)
                                                <div class="text-xs text-green-600 truncate max-w-xs" title="{{ $item->collected_at->format('M d, Y g:i A') }}">
                                                    Collected: {{ $item->collected_at->diffForHumans() }}
                                                </div>
                                            @endif
                                            @if($item->last_collection_reminder_at)
                                                <div class="text-xs text-gray-500 truncate max-w-xs" title="{{ $item->last_collection_reminder_at->format('M d, Y g:i A') }}">
                                                    Last Reminder: {{ $item->last_collection_reminder_at->diffForHumans() }}
                                                </div>
                                            @endif
                                        @elseif($tab === 'collected')
                                            @if($item->collected_at)
                                                <div class="text-xs text-green-600 truncate max-w-xs" title="{{ $item->collected_at->format('M d, Y g:i A') }}">
                                                    Collected: {{ $item->collected_at->format('M d, Y g:i A') }}
                                                </div>
                                            @endif
                                            @if(optional($item->collectedBy)->name)
                                                <div class="text-xs text-gray-600 truncate max-w-xs" title="Verified by {{ optional($item->collectedBy)->name }}">
                                                    Verified: {{ optional($item->collectedBy)->name }}
                                                </div>
                                            @endif
                                            @if($item->collection_notes)
                                                <div class="text-xs text-gray-500 truncate max-w-xs" title="{{ Str::limit($item->collection_notes, 80) }}">
                                                    Notes: {{ Str::limit($item->collection_notes, 40) }}
                                                </div>
                                            @endif
                                        @endif
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

{{-- Reject Confirmation Modal --}}
<div 
    id="rejectModal"
    class="fixed inset-0 bg-black/50 hidden items-center justify-center z-[10000]"
    onclick="if(event.target === this) hideModal('rejectModal')"
>
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4 transform transition-all duration-300 scale-95 opacity-0" id="rejectModalContent">
        {{-- Modal Header --}}
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Confirm Rejection</h3>
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
        <div class="px-6 py-4">
            <p class="text-gray-700 mb-4">Are you sure you want to reject this claim? This action cannot be undone.</p>
            <form method="post" id="rejectForm">
                @csrf
                <input type="hidden" name="claim_id" id="reject-claim-id">
                <input type="hidden" name="reason" value="">
            </form>
        </div>
        
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
                Confirm Reject
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
    function openRejectModal(itemId, claimId = null) {
        const modal = document.getElementById('rejectModal');
        const form = document.getElementById('rejectForm');
        const actionUrl = '{{ route("admin.claims.reject", ":id") }}'.replace(':id', itemId);
        const claimInput = document.getElementById('reject-claim-id');
        
        // Update form action
        if (form) {
            form.action = actionUrl;
        }

        if (claimInput) {
            claimInput.value = claimId ?? '';
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
            const { isConfirmed } = await Swal.fire({
                title: 'Confirm Rejection',
                html: `
                    <p class="mb-3">Are you sure you want to reject <strong>${checked.length}</strong> claim(s)?</p>
                    <p class="text-sm text-gray-600">This action cannot be undone.</p>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: `Reject ${checked.length}`,
                cancelButtonText: 'Cancel',
            });
            
            if (isConfirmed) {
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
                            body: JSON.stringify({ reason: '' })
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
            if (confirm(`Are you sure you want to reject ${checked.length} claim(s)?`)) {
                checked.forEach(itemId => {
                    fetch(`/admin/claims/${itemId}/reject`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ reason: '' })
                    });
                });
                setTimeout(() => location.reload(), 1000);
            }
        }
    }
    
    // Show Claim Details Modal
    function showClaimModal(claim) {
        const formatDateTime = (value) => {
            if (!value) return 'N/A';
            const parsed = new Date(value);
            if (Number.isNaN(parsed.getTime())) {
                return 'N/A';
            }
            return parsed.toLocaleString('en-US', {
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        };

        const formatDateShort = (value) => {
            if (!value) return 'N/A';
            const parsed = new Date(value);
            if (Number.isNaN(parsed.getTime())) {
                return 'N/A';
            }
            return parsed.toLocaleDateString('en-US', {
                year: 'numeric', 
                month: 'short', 
                day: 'numeric'
            });
        };

        const statusColors = {
            LOST_REPORTED: 'bg-yellow-100 text-yellow-800',
            RESOLVED: 'bg-gray-100 text-gray-800',
            FOUND_UNCLAIMED: 'bg-blue-100 text-blue-800',
            CLAIM_PENDING: 'bg-purple-100 text-purple-800',
            CLAIM_APPROVED: 'bg-amber-100 text-amber-800',
            COLLECTED: 'bg-green-100 text-green-800',
        };

        const escapeHtml = (value) => {
            if (!value) return 'N/A';
            const div = document.createElement('div');
            div.textContent = value;
            return div.innerHTML;
        };

        const isPending = claim.tab === 'pending';

        const formattedStatusLabel = (claim.status || '')
            .split('_')
            .map(part => part.charAt(0).toUpperCase() + part.slice(1).toLowerCase())
            .join(' ');
        const statusLabel = formattedStatusLabel || 'N/A';
        const statusColor = statusColors[claim.status] || 'bg-gray-100 text-gray-800';

        const claims = Array.isArray(claim.claims) ? claim.claims : [];
        const hasPendingClaims = claims.length > 0;

        const formatReminderStage = (stage) => {
            if (!stage) {
                return 'N/A';
            }
            const mapping = {
                three_day: '3-Day Reminder',
                one_day: '1-Day Reminder',
                manual: 'Manual Reminder',
            };
            return mapping[stage] || stage;
        };

        const claimsHtml = hasPendingClaims
            ? `
                <div class="bg-white border border-gray-200 rounded-xl p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <h3 class="text-lg font-semibold text-gray-900">Pending Claims (${claims.length})</h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        ${claims.map((entry, index) => `
                            <div class="border border-gray-200 rounded-xl p-4 ${index === 0 ? 'bg-blue-50/40' : 'bg-gray-50'} flex flex-col h-full">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between gap-4">
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <span class="text-sm font-semibold text-gray-900">${escapeHtml(entry.claimantName)}</span>
                                                    ${entry.claimantEmail ? `<span class="text-xs text-gray-500">${escapeHtml(entry.claimantEmail)}</span>` : ''}
                                                </div>
                                                <p class="text-xs text-gray-500 mt-1">Submitted: ${formatDateTime(entry.createdAt)}</p>
                                            </div>
                                            ${entry.similarity ? `
                                                <div class="text-xs font-semibold text-indigo-600 bg-indigo-50 border border-indigo-200 rounded-full px-3 py-1">
                                                    Match ${entry.similarity}%
                                                </div>
                                            ` : ''}
                                        </div>
                                        ${entry.contactName || entry.contactInfo ? `
                                            <div class="mt-3 text-xs text-gray-600 grid grid-cols-1 sm:grid-cols-2 gap-2">
                                                ${entry.contactName ? `<p><span class="font-medium text-gray-700">Contact Name:</span> ${escapeHtml(entry.contactName)}</p>` : ''}
                                                ${entry.contactInfo ? `<p><span class="font-medium text-gray-700">Contact Details:</span> ${escapeHtml(entry.contactInfo)}</p>` : ''}
                                            </div>
                                        ` : ''}
                                        <div class="mt-3 bg-white border border-gray-200 rounded-lg p-3">
                                            <p class="text-sm text-gray-700 whitespace-pre-wrap">${escapeHtml(entry.message)}</p>
                                        </div>
                                    </div>
                                    <div class="flex flex-col items-end gap-2">
                                        <form method="post" action="${claim.approveUrl}" onsubmit="return confirm('Approve this claim?');" class="w-full">
                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                            <input type="hidden" name="claim_id" value="${entry.id}">
                                            <button type="submit" class="w-full px-3 py-1.5 text-xs font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors">
                                                Approve
                                            </button>
                                        </form>
                                        <button 
                                            type="button"
                                            onclick="closeClaimModal(); setTimeout(() => openRejectModal(${claim.id}, ${entry.id}), 300);"
                                            class="w-full px-3 py-1.5 text-xs font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors"
                                        >
                                            Reject
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `
            : '';

        const itemDetailsHtml = `
            <div class="bg-white border border-gray-200 rounded-xl p-6">
                <div class="mb-6">
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
                        ${claim.multipleClaims ? `
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-orange-100 text-orange-800">
                                ⚠️ ${claim.claimsCount} Claims
                            </span>
                        ` : ''}
                        ${claim.isOverdue ? `
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                                Overdue
                            </span>
                        ` : ''}
                    </div>
                </div>
                <div class="border-t border-gray-200 pt-6">
                    <div class="flex items-center gap-2 mb-4">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        <h4 class="text-lg font-semibold text-gray-900">Item Details</h4>
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
            </div>
        `;

        // Determine current step based on status and tab
        const getCurrentStep = () => {
            if (claim.collectedAt || claim.status === 'COLLECTED') return 4;
            if (claim.tab === 'approved' || claim.status === 'CLAIM_APPROVED' || claim.approvedAt) return 3;
            if (claim.tab === 'pending' || claim.status === 'CLAIM_PENDING' || claim.claimedAt) return 2;
            return 1; // FOUND_UNCLAIMED
        };

        const currentStep = getCurrentStep();

        const timelineHtml = `
            <div class="bg-white border border-gray-200 rounded-xl p-6">
                <div class="flex items-center gap-2 mb-6">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h3 class="text-lg font-semibold text-gray-900">Claim Status Timeline</h3>
                </div>
                
                <div class="relative py-4">
                    <!-- Steps Container -->
                    <div class="relative flex justify-between items-start">
                        <!-- Progress Line Background -->
                        <div class="absolute top-6 left-0 right-0 h-0.5 bg-gray-200" style="margin-left: 24px; margin-right: 24px;"></div>
                        <!-- Progress Line Fill -->
                        <div class="absolute top-6 h-0.5 transition-all duration-300 ${
                            currentStep >= 2 ? 'bg-green-500' : 'bg-gray-200'
                        }" style="left: 24px; width: ${currentStep >= 2 ? (currentStep >= 3 ? (currentStep >= 4 ? 'calc(100% - 48px)' : 'calc(66.66% - 16px)') : 'calc(33.33% - 8px)') : '0'};"></div>
                        
                        <!-- Step 1: Found/Unclaimed -->
                        <div class="flex flex-col items-center flex-1 relative z-0">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center ${
                                currentStep >= 1 ? 'bg-green-500 border-2 border-green-500' : 'bg-white border-2 border-gray-300'
                            }">
                                ${currentStep > 1 ? `
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                ` : currentStep === 1 ? `
                                    <div class="w-3 h-3 rounded-full bg-white"></div>
                                ` : ''}
                            </div>
                            <div class="mt-3 text-center w-full px-1">
                                <p class="text-xs font-medium text-gray-900">Found</p>
                                <p class="text-xs text-gray-500 mt-1">Unclaimed</p>
                                ${claim.createdAt ? `<p class="text-xs text-gray-400 mt-0.5">${formatDateShort(claim.createdAt)}</p>` : ''}
                            </div>
                        </div>
                        
                        <!-- Step 2: Pending -->
                        <div class="flex flex-col items-center flex-1 relative z-0">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center ${
                                currentStep >= 2 ? (currentStep > 2 ? 'bg-green-500 border-2 border-green-500' : 'bg-blue-500 border-2 border-blue-500') : 'bg-white border-2 border-gray-300'
                            }">
                                ${currentStep > 2 ? `
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                ` : currentStep === 2 ? `
                                    <div class="w-3 h-3 rounded-full bg-white"></div>
                                ` : ''}
                            </div>
                            <div class="mt-3 text-center w-full px-1">
                                <p class="text-xs font-medium text-gray-900">Pending</p>
                                <p class="text-xs text-gray-500 mt-1">Claim Submitted</p>
                                ${claim.claimedAt ? `<p class="text-xs text-gray-400 mt-0.5">${formatDateShort(claim.claimedAt)}</p>` : ''}
                            </div>
                        </div>
                        
                        <!-- Step 3: Approved -->
                        <div class="flex flex-col items-center flex-1 relative z-0">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center ${
                                currentStep >= 3 ? (currentStep > 3 ? 'bg-green-500 border-2 border-green-500' : 'bg-blue-500 border-2 border-blue-500') : 'bg-white border-2 border-gray-300'
                            }">
                                ${currentStep > 3 ? `
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                ` : currentStep === 3 ? `
                                    <div class="w-3 h-3 rounded-full bg-white"></div>
                                ` : ''}
                            </div>
                            <div class="mt-3 text-center w-full px-1">
                                <p class="text-xs font-medium text-gray-900">Approved</p>
                                <p class="text-xs text-gray-500 mt-1">Claim Approved</p>
                                ${claim.approvedAt ? `<p class="text-xs text-gray-400 mt-0.5">${formatDateShort(claim.approvedAt)}</p>` : ''}
                            </div>
                        </div>
                        
                        <!-- Step 4: Collected -->
                        <div class="flex flex-col items-center flex-1 relative z-0">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center ${
                                currentStep >= 4 ? 'bg-green-500 border-2 border-green-500' : 'bg-white border-2 border-gray-300'
                            }">
                                ${currentStep >= 4 ? `
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                ` : ''}
                            </div>
                            <div class="mt-3 text-center w-full px-1">
                                <p class="text-xs font-medium text-gray-900">Collected</p>
                                <p class="text-xs text-gray-500 mt-1">Item Collected</p>
                                ${claim.collectedAt ? `<p class="text-xs text-gray-400 mt-0.5">${formatDateShort(claim.collectedAt)}</p>` : ''}
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Additional Timeline Details -->
                <div class="mt-6 pt-6 border-t border-gray-200 space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-medium text-gray-500 uppercase">Created At</span>
                        <span class="text-xs text-gray-900">${formatDateTime(claim.createdAt)}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-medium text-gray-500 uppercase">Last Updated</span>
                        <span class="text-xs text-gray-900">${formatDateTime(claim.updatedAt)}</span>
                    </div>
                    ${claim.lastCollectionReminderAt ? `
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-medium text-gray-500 uppercase">Last Reminder Sent</span>
                            <span class="text-xs text-gray-900">${formatDateTime(claim.lastCollectionReminderAt)}</span>
                        </div>
                    ` : ''}
                    ${claim.collectionReminderStage ? `
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-medium text-gray-500 uppercase">Reminder Stage</span>
                            <span class="text-xs text-gray-900">${formatReminderStage(claim.collectionReminderStage)}</span>
                        </div>
                    ` : ''}
                    ${claim.overdueNotifiedAt ? `
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-medium text-red-600 uppercase">Overdue Notified</span>
                            <span class="text-xs text-red-700">${formatDateTime(claim.overdueNotifiedAt)}</span>
                        </div>
                    ` : ''}
                </div>
            </div>
        `;

        const claimantCardHtml = claim.claimantName ? `
            <div class="bg-white border border-gray-200 rounded-xl p-6">
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Claimant Information</h3>
                    ${claim.claimantEmail ? `
                        <p class="text-xs text-gray-500">${escapeHtml(claim.claimantEmail)}</p>
                    ` : ''}
                </div>
                <div class="space-y-4">
                    ${(claim.claimantContactName || claim.claimantContactInfo) ? `
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            ${claim.claimantContactName ? `
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase mb-1.5">Contact Name</label>
                                    <p class="text-sm text-gray-900 font-medium">${escapeHtml(claim.claimantContactName)}</p>
                                </div>
                            ` : ''}
                            ${claim.claimantContactInfo ? `
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase mb-1.5">Contact Information</label>
                                    <p class="text-sm text-gray-900 font-medium">${escapeHtml(claim.claimantContactInfo)}</p>
                                </div>
                            ` : ''}
                        </div>
                    ` : ''}
                    ${claim.claimMessage ? `
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase mb-1.5">Description</label>
                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                <p class="text-sm text-gray-700 whitespace-pre-wrap">${escapeHtml(claim.claimMessage)}</p>
                            </div>
                        </div>
                    ` : ''}
                </div>
            </div>
        ` : '';

        let actionButtonsHtml = '';
        if (claim.tab === 'approved' && !claim.collectedAt) {
            actionButtonsHtml = `
                <div class="bg-white border border-gray-200 rounded-xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Manage Approval</h3>
                        <span class="text-xs text-gray-500 uppercase tracking-wide">Actions</span>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <form method="post" action="${claim.sendReminderUrl}" onsubmit="return confirm('Send a reminder to the claimant?');" class="inline-flex">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 transition-colors">
                                <span>Send Reminder</span>
                            </button>
                        </form>
                        <form method="post" action="${claim.cancelUrl}" onsubmit="return confirm('Cancel this approval and reopen the item?');" class="inline-flex">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-amber-600 hover:bg-amber-700 transition-colors">
                                <span>Cancel Approval</span>
                            </button>
                        </form>
                        <form method="post" action="${claim.markCollectedUrl}" class="inline-flex mark-collected-form">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="hidden" name="note" value="">
                            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-green-600 hover:bg-green-700 transition-colors">
                                <span>Mark Collected</span>
                            </button>
                        </form>
                    </div>
                </div>
            `;
        } else if (claim.tab === 'approved' && claim.collectedAt) {
            actionButtonsHtml = `
                <div class="bg-white border border-green-200 rounded-xl p-6">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <p class="text-sm font-semibold text-green-700">Item already marked as collected.</p>
                    </div>
                </div>
            `;
        } else if (claim.tab === 'collected') {
            actionButtonsHtml = `
                <div class="bg-white border border-gray-200 rounded-xl p-6">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <p class="text-sm font-semibold text-emerald-700">This item has been collected. No further actions are required.</p>
                    </div>
                </div>
            `;
        }

        const modalHTML = `
            <div id="claimDetailModal" class="fixed inset-0 bg-black/50 z-[10000] flex items-center justify-center p-4" onclick="closeClaimModal(event)">
                <div class="bg-white rounded-xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
                    <div class="bg-gradient-to-r from-[#123A7D] to-[#10316A] text-white px-6 py-4 rounded-t-xl flex items-center justify-between sticky top-0 z-50 shadow-lg">
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
                            ${itemDetailsHtml}

                            ${claimantCardHtml}

                            ${timelineHtml}

                            ${actionButtonsHtml}

                            ${claim.tab === 'approved' ? `
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
                                            <p class="text-sm text-gray-900 font-medium">${formatDateTime(claim.approvedAt)}</p>
                                    </div>
                                        ${claim.collectionDeadline ? `
                                    <div>
                                                <label class="block text-xs font-medium text-gray-500 uppercase mb-1.5">Collection Deadline</label>
                                                <p class="text-sm text-gray-900 font-medium">${formatDateShort(claim.collectionDeadline)}</p>
                                    </div>
                                        ` : ''}
                                        ${claim.collectedAt ? `
                                    <div>
                                                <label class="block text-xs font-medium text-gray-500 uppercase mb-1.5">Collected At</label>
                                                <p class="text-sm text-gray-900 font-medium">${formatDateTime(claim.collectedAt)}</p>
                                    </div>
                                    <div>
                                                <label class="block text-xs font-medium text-gray-500 uppercase mb-1.5">Verified By</label>
                                                <p class="text-sm text-gray-900 font-medium">${escapeHtml(claim.collectedByName || 'N/A')}</p>
                                    </div>
                                            ${claim.collectionNotes ? `
                                    <div class="md:col-span-2">
                                                    <label class="block text-xs font-medium text-gray-500 uppercase mb-1.5">Collection Notes</label>
                                                    <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                                                        <p class="text-sm text-gray-700 whitespace-pre-wrap">${escapeHtml(claim.collectionNotes)}</p>
                                        </div>
                                    </div>
                                        ` : ''}
                                        ` : `
                                            ${claim.lastCollectionReminderAt ? `
                                        <div>
                                                    <label class="block text-xs font-medium text-gray-500 uppercase mb-1.5">Last Reminder Sent</label>
                                                    <p class="text-sm text-gray-900 font-medium">${formatDateTime(claim.lastCollectionReminderAt)}</p>
                                        </div>
                                            ` : ''}
                                            ${claim.collectionReminderStage ? `
                                            <div>
                                                    <label class="block text-xs font-medium text-gray-500 uppercase mb-1.5">Reminder Stage</label>
                                                    <p class="text-sm text-gray-900 font-medium">${formatReminderStage(claim.collectionReminderStage)}</p>
                                            </div>
                                        ` : ''}
                                            ${claim.overdueNotifiedAt ? `
                                                <div>
                                                    <label class="block text-xs font-medium text-red-600 uppercase mb-1.5">Overdue Notified</label>
                                                    <p class="text-sm text-red-700 font-medium">${formatDateTime(claim.overdueNotifiedAt)}</p>
                                            </div>
                                        ` : ''}
                                        `}
                                    </div>
                                </div>
                            ` : ''}

                            ${claimsHtml}

							${claim.tab === 'approved' && Array.isArray(claim.otherClaims) && claim.otherClaims.length ? `
                            <div class="bg-white border border-gray-200 rounded-xl p-6">
                                <div class="flex items-center gap-2 mb-4">
										<svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
											<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 8a9 9 0 11-8.485 5.276" />
                                    </svg>
										<h3 class="text-lg font-semibold text-gray-900">Other Claimants Notified</h3>
                                </div>
									<div class="space-y-3">
										${claim.otherClaims.map(outcome => `
											<div class="border border-gray-200 rounded-lg p-3 bg-gray-50">
												<div class="flex justify-between items-start gap-4">
                                    <div>
														<p class="text-sm font-semibold text-gray-900">${escapeHtml(outcome.claimantName)}</p>
														${outcome.claimantEmail ? `<p class="text-xs text-gray-500">${escapeHtml(outcome.claimantEmail)}</p>` : ''}
                                    </div>
													${outcome.notifiedAt ? `<span class="text-xs text-gray-500">Notified ${formatDateTime(outcome.notifiedAt)}</span>` : ''}
                                    </div>
												${outcome.rejectionReason ? `<p class="text-xs text-gray-700 mt-2">${escapeHtml(outcome.rejectionReason)}</p>` : ''}
                                </div>
										`).join('')}
                                    </div>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        const existingModal = document.getElementById('claimDetailModal');
        if (existingModal) {
            existingModal.remove();
        }
        
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        document.body.style.overflow = 'hidden';
        attachMarkCollectedHandlers();
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
    
    // Attach collection note prompt
    function attachMarkCollectedHandlers() {
        document.querySelectorAll('.mark-collected-form').forEach(form => {
            if (form.dataset.bound === 'true') {
                return;
            }

            form.dataset.bound = 'true';
            form.addEventListener('submit', function handleCollectionSubmit(event) {
                const noteField = form.querySelector('input[name="note"]');
                if (!noteField) {
                    return;
                }

                const existingValue = noteField.value || '';
                const note = window.prompt('Add collection verification notes (optional):', existingValue);
                if (note === null) {
                    event.preventDefault();
                    return false;
                }

                if (note.length > 1000) {
                    window.alert('Notes must be 1000 characters or fewer.');
                    event.preventDefault();
                    return false;
                }

                noteField.value = note.trim();
                return true;
            });
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        attachMarkCollectedHandlers();
    });
    
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
