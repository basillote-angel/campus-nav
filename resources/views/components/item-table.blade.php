<x-ui.card class="overflow-hidden p-0">
    {{-- Mobile: Show scroll hint, Desktop: Auto scroll --}}
    <div class="w-full overflow-x-auto table-scroll-hint" style="-webkit-overflow-scrolling: touch;">
    <table class="w-full divide-y divide-gray-200">
            <thead class="bg-gradient-to-r from-[#123A7D] to-[#10316A]">
                <tr>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider w-12">
                        <input 
                            type="checkbox" 
                            id="select-all-checkbox"
                            class="w-4 h-4 text-[#123A7D] bg-white border-gray-300 rounded focus:ring-[#123A7D] focus:ring-2 cursor-pointer"
                            onclick="toggleSelectAll()"
                        />
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                        Item Name
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                        Description
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                        Category
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                        Type
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                        Status
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                        Location
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                        Date
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                        Posted By
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                        Created
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider bg-gradient-to-r from-[#123A7D] to-[#10316A] sticky right-0 z-10 shadow-lg">
                    Actions
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
                @forelse($items as $item)
                    <tr 
                        class="hover:bg-blue-50 cursor-pointer transition-colors duration-200 border-l-4 border-transparent hover:border-[#123A7D]" 
                        data-item-id="{{ data_get($item, 'id') }}" 
                        data-item-type="{{ data_get($item, 'type') }}"
                        onclick="showItemModal({{ json_encode([
                            'id' => data_get($item, 'id'),
                            'name' => data_get($item, 'name'),
                            'type' => data_get($item, 'type'),
                            'description' => data_get($item, 'description'),
                            'category' => data_get($item, 'category'),
                            'category_id' => data_get($item, 'category_id'),
                            'status' => data_get($item, 'status'),
                            'location' => data_get($item, 'location'),
                            'lost_found_date' => data_get($item, 'lost_found_date'),
                            'user_name' => data_get($item, 'user_name'),
                            'user_role' => data_get($item, 'user_role'),
                            'created_at' => data_get($item, 'created_at'),
                            'updated_at' => data_get($item, 'updated_at'),
                            'image_path' => data_get($item, 'image_path'),
                        ]) }})"
                    >
                        {{-- Checkbox --}}
                        <td class="px-4 py-3 whitespace-nowrap" onclick="event.stopPropagation()">
                            <input 
                                type="checkbox" 
                                class="item-checkbox w-4 h-4 text-[#123A7D] bg-white border-gray-300 rounded focus:ring-[#123A7D] focus:ring-2 cursor-pointer"
                                value="{{ data_get($item, 'id') }}"
                                data-item-type="{{ data_get($item, 'type') }}"
                                onchange="updateBulkActionsUI()"
                            />
                        </td>

                        {{-- Item Name --}}
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="text-sm font-semibold text-gray-900 truncate max-w-xs" title="{{ data_get($item, 'name') }}">
                                {{ data_get($item, 'name') }}
                            </div>
                            @if(data_get($item, 'category'))
                                <div class="text-xs text-gray-500 truncate max-w-xs" title="{{ data_get($item, 'category') }}">
                                    {{ data_get($item, 'category') }}
                                </div>
                            @endif
                    </td>

                        {{-- Description --}}
                        <td class="px-4 py-3 max-w-xs">
                            <div class="text-sm text-gray-600 truncate" title="{{ data_get($item, 'description') }}">
                                {{ Str::limit(data_get($item, 'description'), 60) }}
                            </div>
                    </td>

                        {{-- Category --}}
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if(data_get($item, 'category'))
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                    {{ data_get($item, 'category') }}
                                </span>
                            @else
                                <span class="text-xs text-gray-400">—</span>
                            @endif
                    </td>

                        {{-- Type Badge --}}
                        <td class="px-4 py-3 whitespace-nowrap">
                        @if(data_get($item, 'type') == 'lost')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Lost
                            </span>
                        @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Found
                            </span>
                        @endif
                    </td>

                        {{-- Status Badge --}}
                        <td class="px-4 py-3 whitespace-nowrap">
                            @php
                                $status = data_get($item, 'status');
                                $statusColors = [
                                    'open' => 'bg-yellow-100 text-yellow-800',
                                    'unclaimed' => 'bg-blue-100 text-blue-800',
                                    'matched' => 'bg-purple-100 text-purple-800',
                                    'returned' => 'bg-green-100 text-green-800',
                                    'closed' => 'bg-gray-100 text-gray-800',
                                ];
                                $statusColor = $statusColors[$status] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                {{ ucfirst($status) }}
                            </span>
                        </td>

                        {{-- Location --}}
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="text-sm text-gray-700 truncate max-w-xs" title="{{ data_get($item, 'location') ?: '—' }}">
                                {{ data_get($item, 'location') ?: '—' }}
                            </div>
                        </td>

                        {{-- Date Lost/Found --}}
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if(data_get($item, 'lost_found_date'))
                                <div class="text-sm text-gray-900">
                                    {{ \Carbon\Carbon::parse(data_get($item, 'lost_found_date'))->format('M d, Y') }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ \Carbon\Carbon::parse(data_get($item, 'lost_found_date'))->diffForHumans() }}
                                </div>
                        @else
                                <span class="text-sm text-gray-400">—</span>
                        @endif
                    </td>

                        {{-- Posted By --}}
                        <td class="px-4 py-3">
                            @if(data_get($item, 'user_name'))
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ data_get($item, 'user_name') }}</div>
                                    @if(data_get($item, 'user_role'))
                                        <div class="text-xs text-gray-500">
                                            @if(data_get($item, 'user_role') == 'admin')
                                                Admin
                                            @else
                                                Mobile User
                                            @endif
                                        </div>
                                    @else
                                        <div class="text-xs text-gray-500">Mobile User</div>
                                    @endif
                                </div>
                            @else
                                <span class="text-sm text-gray-400">System</span>
                            @endif
                    </td>

                        {{-- Created Date --}}
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if(data_get($item, 'created_at'))
                                <div class="text-sm text-gray-900">
                                    {{ \Carbon\Carbon::parse(data_get($item, 'created_at'))->format('M d, Y') }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ \Carbon\Carbon::parse(data_get($item, 'created_at'))->format('h:i A') }}
                                </div>
                            @else
                                <span class="text-sm text-gray-400">—</span>
                            @endif
                    </td>

                        {{-- Actions --}}
                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium bg-white sticky right-0 z-10 shadow-lg" onclick="event.stopPropagation()">
                            <div class="flex items-center gap-2">
                                <button 
                                    type="button"
                                    onclick="event.stopPropagation(); editItemFromModal({{ data_get($item, 'id') }}, '{{ data_get($item, 'type') }}');"
                                    class="p-2 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-colors cursor-pointer"
                                    title="Edit item"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                                <form id="delete-form-{{ data_get($item, 'id') }}" method="POST" action="{{ route('items.destroy', data_get($item, 'id')) }}" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="type" value="{{ data_get($item, 'type') }}">
                                <button
                                    type="button"
                                        onclick="event.stopPropagation(); confirmDelete({{ data_get($item, 'id') }})"
                                        class="p-2 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition-colors"
                                        title="Delete item"
                                >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                </button>
                            </form>
                                @if(data_get($item, 'type') == 'lost')
                                    <a 
                                        href="{{ route('admin.matches.index') }}?lost_id={{ data_get($item, 'id') }}" 
                                        class="p-2 text-purple-600 hover:text-purple-800 hover:bg-purple-50 rounded-lg transition-colors"
                                        title="View AI matches"
                                        onclick="event.stopPropagation()"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m12.728 0l-.707.707M12 21v-1m-6.657-3.343l.707-.707m12.728 0l.707.707"></path>
                                        </svg>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <svg class="w-16 h-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                </svg>
                                <h3 class="text-lg font-medium text-gray-900 mb-1">No items found</h3>
                                <p class="text-sm text-gray-500">Try adjusting your filters or add a new item.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
        </tbody>
    </table>
</div>

    {{-- Enhanced Pagination --}}
    @if($items->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                {{-- Pagination Info --}}
                <div class="text-sm text-gray-700">
                    Showing 
                    <span class="font-medium">{{ $items->firstItem() }}</span>
                    to
                    <span class="font-medium">{{ $items->lastItem() }}</span>
                    of
                    <span class="font-medium">{{ $items->total() }}</span>
                    results
                </div>
                
                {{-- Pagination Links --}}
                <div class="flex items-center gap-1">
                    {{-- Previous Button --}}
                    @if($items->onFirstPage())
                        <span class="px-3 py-2 text-sm font-medium text-gray-400 bg-white border border-gray-300 rounded-lg cursor-not-allowed">
                            <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </span>
                    @else
                        <a href="{{ $items->appends(request()->query())->previousPageUrl() }}" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-[#123A7D] transition-colors">
                            <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </a>
                    @endif
                    
                    {{-- Page Numbers --}}
                    @php
                        $currentPage = $items->currentPage();
                        $lastPage = $items->lastPage();
                        $startPage = max(1, $currentPage - 2);
                        $endPage = min($lastPage, $currentPage + 2);
                        
                        // Show first page if not in range
                        if ($startPage > 1) {
                            $startPage = 1;
                            $endPage = min(5, $lastPage);
                        }
                        
                        // Show last page if not in range
                        if ($endPage < $lastPage && $lastPage - $endPage > 2) {
                            $endPage = $lastPage;
                            $startPage = max(1, $lastPage - 4);
                        }
                    @endphp
                    
                    @if($startPage > 1)
                        <a href="{{ $items->appends(request()->query())->url(1) }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-[#123A7D] transition-colors">1</a>
                        @if($startPage > 2)
                            <span class="px-2 py-2 text-sm text-gray-500">...</span>
                        @endif
                    @endif
                    
                    @for($page = $startPage; $page <= $endPage; $page++)
                        @if($page == $currentPage)
                            <span class="px-4 py-2 text-sm font-semibold text-white bg-[#123A7D] border border-[#123A7D] rounded-lg">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $items->appends(request()->query())->url($page) }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-[#123A7D] transition-colors">
                                {{ $page }}
                            </a>
                        @endif
                    @endfor
                    
                    @if($endPage < $lastPage)
                        @if($endPage < $lastPage - 1)
                            <span class="px-2 py-2 text-sm text-gray-500">...</span>
                        @endif
                        <a href="{{ $items->appends(request()->query())->url($lastPage) }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-[#123A7D] transition-colors">{{ $lastPage }}</a>
                    @endif
                    
                    {{-- Next Button --}}
                    @if($items->hasMorePages())
                        <a href="{{ $items->appends(request()->query())->nextPageUrl() }}" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-[#123A7D] transition-colors">
                            <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    @else
                        <span class="px-3 py-2 text-sm font-medium text-gray-400 bg-white border border-gray-300 rounded-lg cursor-not-allowed">
                            <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </span>
                    @endif
                </div>
            </div>
        </div>
    @else
        {{-- No pagination needed, but show total count --}}
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            <div class="text-sm text-gray-700">
                Showing <span class="font-medium">{{ $items->total() }}</span> result{{ $items->total() !== 1 ? 's' : '' }}
            </div>
        </div>
    @endif
</x-ui.card>

{{-- Bulk Actions Floating Toolbar --}}
<div id="bulk-actions-toolbar" class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-lg z-50 transform translate-y-full transition-transform duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <span id="selected-count" class="text-sm font-medium text-gray-700">0 items selected</span>
                <button 
                    onclick="clearSelection()"
                    class="text-sm text-gray-500 hover:text-gray-700 underline"
                >
                    Clear selection
                </button>
            </div>
            <div class="flex items-center gap-3">
                <div class="relative" id="bulk-status-container">
                    <button 
                        onclick="toggleBulkStatusDropdown()"
                        class="bg-white border border-gray-300 rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#123A7D]/50 transition-all duration-200 flex items-center gap-2"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Change Status
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div 
                        id="bulk-status-dropdown"
                        class="absolute bottom-full right-0 mb-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-10 hidden"
                    >
                        <button onclick="bulkUpdateStatus('open')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Open</button>
                        <button onclick="bulkUpdateStatus('unclaimed')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Unclaimed</button>
                        <button onclick="bulkUpdateStatus('matched')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Matched</button>
                        <button onclick="bulkUpdateStatus('returned')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Returned</button>
                        <button onclick="bulkUpdateStatus('closed')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Closed</button>
                    </div>
                </div>
                
                <button 
                    onclick="bulkExportSelected()"
                    class="bg-white border border-gray-300 rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#123A7D]/50 transition-all duration-200 flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Export Selected
                </button>
                
                <button 
                    onclick="bulkDeleteSelected()"
                    class="bg-red-600 text-white rounded-lg px-4 py-2 text-sm font-medium hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 transition-all duration-200 flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Delete Selected
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Toggle Select All
    function toggleSelectAll() {
        const selectAll = document.getElementById('select-all-checkbox');
        const checkboxes = document.querySelectorAll('.item-checkbox');
        
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAll.checked;
        });
        
        updateBulkActionsUI();
    }

    // Update Bulk Actions UI
    function updateBulkActionsUI() {
        const checkboxes = document.querySelectorAll('.item-checkbox:checked');
        const count = checkboxes.length;
        const toolbar = document.getElementById('bulk-actions-toolbar');
        const selectedCount = document.getElementById('selected-count');
        const selectAll = document.getElementById('select-all-checkbox');
        
        // Update count
        if (selectedCount) {
            selectedCount.textContent = `${count} ${count === 1 ? 'item' : 'items'} selected`;
        }
        
        // Show/hide toolbar
        if (toolbar) {
            if (count > 0) {
                toolbar.classList.remove('translate-y-full');
                toolbar.classList.add('translate-y-0');
            } else {
                toolbar.classList.add('translate-y-full');
                toolbar.classList.remove('translate-y-0');
            }
        }
        
        // Update select all checkbox state
        if (selectAll) {
            const allChecked = checkboxes.length === document.querySelectorAll('.item-checkbox').length;
            selectAll.checked = allChecked && count > 0;
            selectAll.indeterminate = count > 0 && !allChecked;
        }
    }

    // Clear Selection
    function clearSelection() {
        const checkboxes = document.querySelectorAll('.item-checkbox');
        const selectAll = document.getElementById('select-all-checkbox');
        
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        
        if (selectAll) {
            selectAll.checked = false;
            selectAll.indeterminate = false;
        }
        
        updateBulkActionsUI();
    }

    // Toggle Bulk Status Dropdown
    function toggleBulkStatusDropdown() {
        const dropdown = document.getElementById('bulk-status-dropdown');
        if (dropdown) {
            dropdown.classList.toggle('hidden');
        }
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const statusContainer = document.getElementById('bulk-status-container');
        const statusDropdown = document.getElementById('bulk-status-dropdown');
        if (statusContainer && statusDropdown && !statusContainer.contains(event.target)) {
            statusDropdown.classList.add('hidden');
        }
    });

    // Bulk Update Status
    function bulkUpdateStatus(newStatus) {
        const checkboxes = document.querySelectorAll('.item-checkbox:checked');
        const selectedIds = Array.from(checkboxes).map(cb => {
            return {
                id: cb.value,
                type: cb.dataset.itemType
            };
        });
        
        if (selectedIds.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No items selected',
                text: 'Please select at least one item.',
            });
            return;
        }
        
        Swal.fire({
            title: `Change status to "${newStatus}"?`,
            text: `This will update ${selectedIds.length} item(s).`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#123A7D',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, update status',
            cancelButtonText: 'Cancel',
        }).then((result) => {
            if (result.isConfirmed) {
                // Perform bulk status update via AJAX
                fetch('{{ route("items.bulkUpdate") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        items: selectedIds,
                        action: 'status',
                        value: newStatus
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Status updated!',
                            text: `Successfully updated ${data.count} item(s).`,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        clearSelection();
                        setTimeout(() => window.location.reload(), 2000);
                    } else {
                        throw new Error(data.message || 'Update failed');
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message || 'Failed to update status. Please try again.',
                    });
                });
            }
        });
        
        // Close dropdown
        document.getElementById('bulk-status-dropdown').classList.add('hidden');
    }

    // Bulk Delete Selected
    function bulkDeleteSelected() {
        const checkboxes = document.querySelectorAll('.item-checkbox:checked');
        const selectedIds = Array.from(checkboxes).map(cb => {
            return {
                id: cb.value,
                type: cb.dataset.itemType
            };
        });
        
        if (selectedIds.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No items selected',
                text: 'Please select at least one item.',
            });
            return;
        }
        
        Swal.fire({
            title: 'Are you sure?',
            text: `This will permanently delete ${selectedIds.length} item(s). This action cannot be undone!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, delete them!',
            cancelButtonText: 'Cancel',
        }).then((result) => {
            if (result.isConfirmed) {
                // Show deleting modal
                if (typeof showDeleting === 'function') {
                    showDeleting(`Deleting ${selectedIds.length} item(s)...`);
                }
                
                // Perform bulk delete via AJAX
                fetch('{{ route("items.bulkDelete") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        items: selectedIds
                    })
                })
                .then(response => response.json())
                .then(data => {
                    // Hide loading modal
                    if (typeof hideLoadingModal === 'function') {
                        hideLoadingModal();
                    }
                    
                    if (data.success) {
                        // Show success banner notification
                        if (typeof showNotificationBanner === 'function') {
                            showNotificationBanner(`Successfully deleted ${data.count} item(s).`, 'success', 3000);
                        }
                        
                        clearSelection();
                        setTimeout(() => window.location.reload(), 500);
                    } else {
                        throw new Error(data.message || 'Delete failed');
                    }
                })
                .catch(error => {
                    // Hide loading modal
                    if (typeof hideLoadingModal === 'function') {
                        hideLoadingModal();
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message || 'Failed to delete items. Please try again.',
                    });
                });
            }
        });
    }

    // Bulk Export Selected
    function bulkExportSelected() {
        const checkboxes = document.querySelectorAll('.item-checkbox:checked');
        const selectedIds = Array.from(checkboxes).map(cb => cb.value);
        
        if (selectedIds.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No items selected',
                text: 'Please select at least one item to export.',
            });
            return;
        }
        
        // Build export URL with selected IDs
        const params = new URLSearchParams();
        params.append('ids', selectedIds.join(','));
        params.append('format', 'csv');
        
        window.location.href = '{{ route("items.export") }}?' + params.toString();
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateBulkActionsUI();
    });
</script>

{{-- Image Modal --}}
<div id="image-modal" class="fixed inset-0 bg-black/50 z-[10000] hidden items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-4xl max-h-[90vh] relative">
        <button 
            onclick="closeImageModal()"
            class="absolute top-4 right-4 z-10 bg-white/90 hover:bg-white text-gray-700 rounded-full p-2 transition-colors"
        >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        <img id="modal-image" src="" alt="" class="w-full h-auto rounded-xl max-h-[90vh] object-contain" loading="lazy">
        <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent p-6 rounded-b-xl">
            <h3 id="modal-image-title" class="text-white text-lg font-semibold"></h3>
        </div>
    </div>
</div>

<script>
    function confirmDelete(itemId) {
        Swal.fire({
            title: 'Are you sure?',
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
        }).then((result) => {
            if (result.isConfirmed) {
                // Show deleting modal
                if (typeof showDeleting === 'function') {
                    showDeleting('Deleting item...');
                }
                
                document.getElementById('delete-form-' + itemId)?.submit();
            }
        });
    }

    function showImageModal(imageSrc, title) {
        const modal = document.getElementById('image-modal');
        const modalImage = document.getElementById('modal-image');
        const modalTitle = document.getElementById('modal-image-title');
        
        if (modal && modalImage && modalTitle) {
            modalImage.src = imageSrc;
            modalTitle.textContent = title;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeImageModal() {
        const modal = document.getElementById('image-modal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
        }
    }

    // Close modal on background click or Escape key
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('image-modal');
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeImageModal();
                }
            });
        }

    });

    // Show Item Details Modal
    function showItemModal(item) {
        // Format dates
        const lostFoundDate = item.lost_found_date ? new Date(item.lost_found_date).toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        }) : 'N/A';
        
        const createdAt = item.created_at ? new Date(item.created_at).toLocaleString('en-US', { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        }) : 'N/A';
        
        const updatedAt = item.updated_at ? new Date(item.updated_at).toLocaleString('en-US', { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        }) : 'N/A';

        // Get image URL
        let imageUrl = null;
        if (item.image_path) {
            if (item.image_path.startsWith('http')) {
                imageUrl = item.image_path;
            } else {
                imageUrl = '{{ asset("storage/") }}/' + item.image_path.replace(/^\//, '');
            }
        }

        // Status colors
        const statusColors = {
            'open': 'bg-yellow-100 text-yellow-800',
            'unclaimed': 'bg-blue-100 text-blue-800',
            'matched': 'bg-purple-100 text-purple-800',
            'returned': 'bg-green-100 text-green-800',
            'closed': 'bg-gray-100 text-gray-800',
        };
        const statusColor = statusColors[item.status] || 'bg-gray-100 text-gray-800';
        const statusLabel = (item.status || '').charAt(0).toUpperCase() + (item.status || '').slice(1);

        // Escape HTML to prevent XSS
        const escapeHtml = (text) => {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        };

        // Create modal HTML
        const modalHTML = `
            <div id="itemDetailModal" class="fixed inset-0 bg-black/50 z-[10000] flex items-center justify-center p-4" onclick="closeItemModal(event)">
                <div class="bg-white rounded-xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
                    <div class="bg-gradient-to-r from-[#123A7D] to-[#10316A] text-white px-6 py-4 rounded-t-xl flex items-center justify-between sticky top-0 z-10">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-white/20 rounded-lg">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold">Item Details</h2>
                                <p class="text-sm text-white/80">Complete information about this item</p>
                            </div>
                        </div>
                        <button onclick="closeItemModal()" class="text-white hover:text-gray-200 transition-colors p-2 hover:bg-white/10 rounded-lg">
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
                                        <h3 class="text-2xl font-bold text-gray-900 mb-2">${escapeHtml(item.name || 'Unnamed Item')}</h3>
                                        <div class="flex items-center gap-3 flex-wrap">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold ${item.type === 'lost' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'}">
                                                ${item.type === 'lost' ? 'Lost Item' : 'Found Item'}
                                            </span>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold ${statusColor}">
                                                ${statusLabel}
                                            </span>
                                            ${item.category ? `
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800">
                                                    ${escapeHtml(item.category)}
                                                </span>
                                            ` : ''}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Description Section --}}
                            <div class="bg-white border border-gray-200 rounded-xl p-6">
                                <div class="flex items-center gap-2 mb-4">
                                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path>
                                    </svg>
                                    <h3 class="text-lg font-semibold text-gray-900">Description</h3>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                    <p class="text-sm text-gray-700 whitespace-pre-wrap leading-relaxed">${escapeHtml(item.description || 'No description provided.')}</p>
                                </div>
                            </div>

                            {{-- Details Grid --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- Location & Date --}}
                                <div class="bg-white border border-gray-200 rounded-xl p-6">
                                    <div class="flex items-center gap-2 mb-4">
                                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        <h3 class="text-lg font-semibold text-gray-900">Location & Date</h3>
                                    </div>
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 uppercase mb-1.5 tracking-wide">Location</label>
                                            <div class="flex items-center gap-2">
                                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                </svg>
                                                <p class="text-sm text-gray-900 font-medium">${escapeHtml(item.location || 'Not specified')}</p>
                                            </div>
                                        </div>
                                        <div class="border-t border-gray-200 pt-4">
                                            <label class="block text-xs font-medium text-gray-500 uppercase mb-1.5 tracking-wide">Date ${item.type === 'lost' ? 'Lost' : 'Found'}</label>
                                            <div class="flex items-center gap-2">
                                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                                <p class="text-sm text-gray-900 font-medium">${lostFoundDate}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Posted Information --}}
                                <div class="bg-white border border-gray-200 rounded-xl p-6">
                                    <div class="flex items-center gap-2 mb-4">
                                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                        <h3 class="text-lg font-semibold text-gray-900">Posted Information</h3>
                                    </div>
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 uppercase mb-1.5 tracking-wide">Posted By</label>
                                            <div class="flex items-center gap-2">
                                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                </svg>
                                                <div>
                                                    <p class="text-sm text-gray-900 font-medium">${escapeHtml(item.user_name || 'System')}</p>
                                                    <p class="text-xs text-gray-500">${item.user_role === 'admin' ? 'Admin' : 'Mobile User'}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="border-t border-gray-200 pt-4">
                                            <label class="block text-xs font-medium text-gray-500 uppercase mb-1.5 tracking-wide">Created At</label>
                                            <div class="flex items-center gap-2">
                                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                <p class="text-sm text-gray-900 font-medium">${createdAt}</p>
                                            </div>
                                        </div>
                                        <div class="border-t border-gray-200 pt-4">
                                            <label class="block text-xs font-medium text-gray-500 uppercase mb-1.5 tracking-wide">Last Updated</label>
                                            <div class="flex items-center gap-2">
                                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                                </svg>
                                                <p class="text-sm text-gray-900 font-medium">${updatedAt}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Actions Section --}}
                            <div class="bg-gray-50 border border-gray-200 rounded-xl p-6">
                                <div class="flex flex-wrap gap-3">
                                    <button type="button" onclick="event.stopPropagation(); event.preventDefault(); editItemFromModal(${item.id}, '${item.type}');" class="flex-1 min-w-[140px] px-6 py-3 bg-[#123A7D] text-white rounded-lg hover:bg-[#10316A] transition-colors text-sm font-semibold text-center flex items-center justify-center gap-2 shadow-sm hover:shadow-md cursor-pointer">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                        Edit Item
                                    </button>
                                    ${item.type === 'lost' ? `
                                        <a href="{{ route('admin.matches.index', ['lost_item_id' => ':id']) }}".replace(':id', item.id)" class="flex-1 min-w-[140px] px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors text-sm font-semibold text-center flex items-center justify-center gap-2 shadow-sm hover:shadow-md">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m12.728 0l-.707.707M12 21v-1m-6.657-3.343l.707-.707m12.728 0l.707.707"></path>
                                            </svg>
                                            View Matches
                                        </a>
                                    ` : ''}
                                    <button onclick="confirmDeleteModal(${item.id}, '${item.type}'); closeItemModal();" class="flex-1 min-w-[140px] px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm font-semibold flex items-center justify-center gap-2 shadow-sm hover:shadow-md">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                        Delete Item
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing modal if any
        const existingModal = document.getElementById('itemDetailModal');
        if (existingModal) {
            existingModal.remove();
        }
        
        // Insert modal into body
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        document.body.style.overflow = 'hidden';
    }

    // Close Item Modal
    function closeItemModal(event) {
        if (event && event.target !== event.currentTarget) {
            return;
        }
        const modal = document.getElementById('itemDetailModal');
        if (modal) {
            modal.remove();
            document.body.style.overflow = '';
        }
    }

    // Edit Item from Modal
    async function editItemFromModal(itemId, itemType) {
        console.log('editItemFromModal called with:', { itemId, itemType });
        try {
            // Show modern loading modal
            if (typeof showLoading === 'function') {
                showLoading('Loading item data...');
            }
            
            // Fetch item data
            const response = await fetch(`{{ url('/items') }}/${itemId}/edit?type=${encodeURIComponent(itemType)}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });
            
            // Check content type
            const contentType = response.headers.get('content-type');
            console.log('Response status:', response.status, 'Content-Type:', contentType);
            
            if (!response.ok) {
                const errorText = await response.text();
                console.error('Response error:', response.status, errorText);
                throw new Error(`Failed to load item data (${response.status}): ${errorText.substring(0, 100)}`);
            }
            
            // Handle both JSON and HTML responses
            let data;
            if (contentType && contentType.includes('application/json')) {
                data = await response.json();
            } else {
                // If we got HTML, the AJAX detection might have failed
                const htmlText = await response.text();
                console.error('Received HTML instead of JSON:', htmlText.substring(0, 200));
                throw new Error('Server returned HTML instead of JSON. AJAX request may not be detected properly.');
            }
            
            console.log('Received data:', data);
            
            if (!data || !data.item) {
                console.error('Invalid data structure:', data);
                throw new Error('Invalid response format from server');
            }
            const item = data.item;
            const categories = data.categories;
            const selectedCategoryId = data.selectedCategoryId;
            
            // Close loading modal
            if (typeof hideLoadingModal === 'function') {
                hideLoadingModal();
            }
            
            // Validate data before showing modal
            console.log('Item data:', item);
            console.log('Categories:', categories);
            console.log('Selected category ID:', selectedCategoryId);
            
            // Verify required data
            if (!item || !item.id) {
                throw new Error('Item data is missing or invalid');
            }
            
            if (!categories || !Array.isArray(categories)) {
                throw new Error('Categories data is missing or invalid');
            }
            
            console.log('All data validated, calling showEditItemModal...');
            
            // Create and show edit modal
            if (typeof showEditItemModal === 'function') {
                try {
                    showEditItemModal(item, categories, selectedCategoryId);
                    console.log('showEditItemModal called successfully');
                } catch (modalError) {
                    console.error('Error in showEditItemModal:', modalError);
                    throw modalError;
                }
            } else {
                console.error('showEditItemModal function not found. Available functions:', Object.keys(window));
                throw new Error('showEditItemModal function not found');
            }
        } catch (error) {
            console.error('Error loading item:', error);
            console.error('Error stack:', error.stack);
            // Ensure loading modal is closed
            if (typeof hideLoadingModal === 'function') {
                hideLoadingModal();
            }
            
            // Always show error message
            const errorMessage = error.message || 'Failed to load item data. Please try again.';
            console.error('Displaying error:', errorMessage);
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMessage,
                    confirmButtonText: 'OK'
                }).then(() => {
                    console.log('Error dialog closed');
                });
            } else {
                alert(errorMessage);
            }
        }
    }
    
    // Legacy loading modal functions (kept for backward compatibility)
    // Now using modern loading modal from loading-modal component
    function showLoadingModal() {
        if (typeof showLoading === 'function') {
            return showLoading('Loading...');
        }
        // Fallback if modern modal not available
        const modal = document.createElement('div');
        modal.id = 'loading-modal';
        modal.className = 'fixed inset-0 bg-black/50 z-[10000] flex items-center justify-center';
        modal.innerHTML = `
            <div class="bg-white rounded-xl shadow-xl p-8">
                <div class="flex items-center gap-4">
                    <svg class="animate-spin h-8 w-8 text-[#123A7D]" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-lg font-medium text-gray-700">Loading...</span>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        document.body.style.overflow = 'hidden';
        return modal;
    }
    
    function closeLoadingModal(modal) {
        if (typeof hideLoadingModal === 'function') {
            hideLoadingModal();
        } else if (modal && modal.parentNode) {
            modal.remove();
            document.body.style.overflow = '';
        }
    }
    
    // Show Edit Item Modal
    function showEditItemModal(item, categories, selectedCategoryId) {
        // Remove existing modal if any
        const existingModal = document.getElementById('edit-item-modal');
        if (existingModal) {
            existingModal.remove();
        }
        
        // Escape HTML to prevent XSS
        const escapeHtml = (text) => {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        };
        
        // Build status options based on item type
        const statusOptions = item.type === 'lost' 
            ? ['open', 'matched', 'closed']
            : ['unclaimed', 'matched', 'returned'];
        
        const statusOptionsHTML = statusOptions.map(status => {
            const statusLabels = {
                'open': 'Open',
                'matched': 'Matched',
                'closed': 'Closed',
                'unclaimed': 'Unclaimed',
                'returned': 'Returned'
            };
            return `<option value="${status}" ${item.status === status ? 'selected' : ''}>${statusLabels[status]}</option>`;
        }).join('');
        
        // Build category options
        const categoryOptionsHTML = categories.map(cat => {
            return `<option value="${cat.id}" ${selectedCategoryId == cat.id ? 'selected' : ''}>${cat.name}</option>`;
        }).join('');
        
        // Create modal HTML
        const modalHTML = `
            <div id="edit-item-modal" data-item-id="${item.id}" class="fixed inset-0 bg-black/50 z-[10000] flex items-center justify-center p-0 md:p-4" style="display: flex !important;" onclick="if(event.target === this) closeEditItemModal()">
                <div class="bg-white rounded-none md:rounded-xl shadow-2xl max-w-3xl w-full h-full md:h-auto md:max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
                    <div class="bg-gradient-to-r from-[#123A7D] to-[#10316A] text-white px-4 py-4 md:px-6 md:py-4 rounded-none md:rounded-t-xl flex items-center justify-between sticky top-0 z-10">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-white/20 rounded-lg">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xl md:text-2xl font-bold">Edit Item</h2>
                                <p class="text-xs md:text-sm text-white/80">Update item information</p>
                            </div>
                        </div>
                        <button onclick="closeEditItemModal()" class="text-white hover:text-gray-200 transition-colors p-2 hover:bg-white/10 rounded-lg cursor-pointer">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="p-4 md:p-6">
                        <form id="edit-item-form" enctype="multipart/form-data">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="hidden" name="_method" value="PUT">
                            <input type="hidden" name="type" value="${item.type}" />
                            <input type="hidden" name="originalType" value="${item.type}" />
                            
                            <div class="space-y-6">
                                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6 border border-blue-100">
                                    <div class="flex items-center gap-2 mb-4">
                                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                        </svg>
                                        <h3 class="text-lg font-semibold text-gray-900">Basic Information</h3>
                                    </div>
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Item Name <span class="text-red-500">*</span></label>
                                            <input type="text" name="title" value="${escapeHtml(item.name)}" placeholder="e.g., Black Wallet, iPhone 12" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#123A7D]/50 focus:border-[#123A7D] text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Category <span class="text-red-500">*</span></label>
                                            <select name="category_id" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#123A7D]/50 focus:border-[#123A7D] text-sm">
                                                <option value="">Select Category</option>
                                                ${categoryOptionsHTML}
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                            <select name="status" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#123A7D]/50 focus:border-[#123A7D] text-sm">
                                                ${statusOptionsHTML}
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="bg-white border border-gray-200 rounded-xl p-6">
                                    <div class="flex items-center gap-2 mb-4">
                                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path>
                                        </svg>
                                        <h3 class="text-lg font-semibold text-gray-900">Description</h3>
                                    </div>
                                    <textarea name="description" placeholder="Provide detailed description of the item..." rows="4" required maxlength="1000" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#123A7D]/50 focus:border-[#123A7D] text-sm">${escapeHtml(item.description || '')}</textarea>
                                </div>
                                
                                <div class="bg-white border border-gray-200 rounded-xl p-6">
                                    <div class="flex items-center gap-2 mb-4">
                                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        <h3 class="text-lg font-semibold text-gray-900">Location & Date</h3>
                                    </div>
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                                            <input type="text" name="location" value="${escapeHtml(item.location || '')}" placeholder="Where was it lost/found? (e.g., Library Building, Room 101)" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#123A7D]/50 focus:border-[#123A7D] text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Date ${item.type === 'lost' ? 'Lost' : 'Found'}</label>
                                            <input type="date" name="date" value="${item.lost_found_date || ''}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#123A7D]/50 focus:border-[#123A7D] text-sm">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="bg-gray-50 border border-gray-200 rounded-none md:rounded-xl p-4 md:p-6">
                                    <div id="edit-item-errors" class="mb-4 text-red-600 text-sm space-y-1"></div>
                                    <div class="flex flex-col sm:flex-row gap-3">
                                        <button type="button" onclick="closeEditItemModal()" class="flex-1 min-w-[140px] px-6 py-3 bg-white text-gray-700 rounded-lg hover:bg-gray-100 transition-colors text-sm font-semibold text-center flex items-center justify-center gap-2 shadow-sm hover:shadow-md border border-gray-300 cursor-pointer">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                            Cancel
                                        </button>
                                        <button type="submit" id="save-item-btn" class="flex-1 min-w-[140px] px-6 py-3 bg-gradient-to-r from-[#123A7D] to-[#10316A] text-white rounded-lg hover:from-[#10316A] hover:to-[#0d2757] transition-colors text-sm font-semibold flex items-center justify-center gap-2 shadow-sm hover:shadow-md cursor-pointer">
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
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        document.body.style.overflow = 'hidden';
        
        // Verify modal was created
        const modal = document.getElementById('edit-item-modal');
        if (!modal) {
            console.error('Modal was not created!');
            return;
        }
        
        console.log('Modal created successfully:', modal);
        
        // Ensure modal is visible
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        modal.style.display = 'flex';
        
        // Handle form submission
        const form = document.getElementById('edit-item-form');
        if (form) {
            form.addEventListener('submit', handleEditItemSubmit);
            console.log('Form submission handler attached');
        } else {
            console.error('Edit item form not found after modal creation');
        }
        
        // Animate modal appearance
        setTimeout(() => {
            if (modal) {
                modal.style.opacity = '1';
                const content = modal.querySelector('.bg-white');
                if (content) {
                    content.style.transform = 'scale(1)';
                    content.style.opacity = '1';
                }
                console.log('Modal should now be visible');
            }
        }, 10);
    }
    
    // Make functions globally accessible
    window.editItemFromModal = editItemFromModal;
    window.showEditItemModal = showEditItemModal;
    window.closeEditItemModal = closeEditItemModal;
    
    // Handle Edit Item Form Submission
    function handleEditItemSubmit(e) {
        e.preventDefault();
        
        const form = e.target;
        const submitBtn = document.getElementById('save-item-btn');
        const errorContainer = document.getElementById('edit-item-errors');
        const formData = new FormData(form);
        
        // Get item ID from modal
        const modal = document.getElementById('edit-item-modal');
        const itemId = modal ? modal.getAttribute('data-item-id') : null;
        
        if (!itemId) {
            alert('Item ID not found');
            return;
        }
        
        // Ensure _method is set to PUT
        if (!formData.has('_method')) {
            formData.append('_method', 'PUT');
        }
        
        // Disable submit button
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Saving...';
        
        // Clear previous errors
        errorContainer.innerHTML = '';
        
        // Show modern loading modal
        if (typeof showSaving === 'function') {
            showSaving('Saving changes...');
        }
        
        fetch(`{{ url('/items') }}/${itemId}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || form.querySelector('input[name="_token"]')?.value,
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => {
            return response.json().then(data => {
                return { status: response.status, data: data };
            });
        })
        .then(({ status, data }) => {
            // Hide loading modal
            if (typeof hideLoadingModal === 'function') {
                hideLoadingModal();
            }
            
            if (data.success) {
                // Show success banner notification
                if (typeof showNotificationBanner === 'function') {
                    showNotificationBanner(data.message || 'Item updated successfully.', 'success', 3000);
                }
                
                // Close modal and reload after a short delay
                setTimeout(() => {
                    closeEditItemModal();
                    window.location.reload();
                }, 500);
            } else {
                // Handle validation errors from backend
                let errorMessages = [];
                
                if (status === 422 && data.errors) {
                    // Laravel validation errors
                    Object.keys(data.errors).forEach(key => {
                        if (Array.isArray(data.errors[key])) {
                            data.errors[key].forEach(msg => errorMessages.push(msg));
                        } else {
                            errorMessages.push(data.errors[key]);
                        }
                    });
                } else if (data.message) {
                    errorMessages.push(data.message);
                } else {
                    errorMessages.push('Failed to update item. Please try again.');
                }
                
                if (errorMessages.length > 0) {
                    errorContainer.innerHTML = '<div class="space-y-1">' + errorMessages.map(err => `<p>• ${err}</p>`).join('') + '</div>';
                }
            }
        })
        .catch(error => {
            console.error('Error updating item:', error);
            
            // Hide loading modal
            if (typeof hideLoadingModal === 'function') {
                hideLoadingModal();
            }
            
            // Show error in container if not already shown
            if (errorContainer && !errorContainer.innerHTML) {
                errorContainer.innerHTML = `<p class="text-red-600">${error.message || 'An error occurred. Please try again.'}</p>`;
            }
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'Failed to update item. Please try again.',
                    confirmButtonText: 'OK'
                });
            } else {
                alert(error.message || 'Failed to update item. Please try again.');
            }
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = `
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Save Changes
            `;
        });
    }
    
    // Close Edit Item Modal
    function closeEditItemModal() {
        const modal = document.getElementById('edit-item-modal');
        if (modal) {
            modal.remove();
            document.body.style.overflow = '';
        }
    }

    // Confirm Delete from Modal
    function confirmDeleteModal(itemId, itemType) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Delete Item?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, delete it',
                cancelButtonText: 'Cancel',
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show deleting modal
                    if (typeof showDeleting === 'function') {
                        showDeleting('Deleting item...');
                    }
                    
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route("items.destroy", ":id") }}'.replace(':id', itemId);
                    form.innerHTML = `
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="type" value="${itemType}">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        } else {
            if (confirm('Are you sure you want to delete this item?')) {
                // Show deleting modal
                if (typeof showDeleting === 'function') {
                    showDeleting('Deleting item...');
                }
                
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("items.destroy", ":id") }}'.replace(':id', itemId);
                form.innerHTML = `
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="type" value="${itemType}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    }
</script>
