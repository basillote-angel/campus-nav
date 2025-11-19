@extends('layouts.app')

@section('content')
{{-- 
    Lost & Found Items Management Page - Modernized UI
    Uses reusable components for consistent design and better maintainability
--}}

<div class="min-h-full bg-gradient-to-br from-blue-50 via-white to-indigo-50">
    {{-- Page Header --}}
    <x-ui.page-header 
        title="Lost and Found Items"
        description="View and manage lost and found items in the system"
    >
        {{-- Export Dropdown --}}
        <x-ui.export-dropdown 
            id="export-dropdown"
            :routes="[
                'csv' => route('items.export', array_merge(request()->query(), ['format' => 'csv'])),
                'pdf' => route('items.export', array_merge(request()->query(), ['format' => 'pdf']))
            ]"
            :labels="[
                'csv' => 'Export Items (CSV)',
                'pdf' => 'Export Items (PDF)'
            ]"
        />
        
        {{-- Add New Item Button --}}
        <button 
            type="button"
            onclick="showAddItemModal()"
            class="w-full sm:w-auto bg-gradient-to-r from-[#123A7D] to-[#10316A] text-white px-4 sm:px-6 py-2.5 rounded-lg hover:from-[#10316A] hover:to-[#0d2757] transition-all duration-200 flex items-center justify-center gap-2 shadow-sm hover:shadow-md cursor-pointer text-sm md:text-base font-semibold min-h-[44px] whitespace-nowrap"
        >
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            <span>Add New Item</span>
        </button>
    </x-ui.page-header>

    {{-- Main Content Area --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-8">
        {{-- Success/Error Messages - Converted to banner notifications via JavaScript --}}
        @if(session('success'))
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    if (typeof showNotificationBanner === 'function') {
                        showNotificationBanner('{{ session('success') }}', 'success', 3000);
                    }
                });
            </script>
        @elseif(session('error'))
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    if (typeof showNotificationBanner === 'function') {
                        showNotificationBanner('{{ session('error') }}', 'error', 4000);
                    }
                });
            </script>
        @endif

        {{-- Search and Filter Section --}}
        <x-ui.card class="mb-6">
            <div class="space-y-4">
                {{-- Search Input with Search and Clear Buttons --}}
                <div class="flex gap-3">
                    <div class="flex-1 relative">
                        <input 
                            type="text" 
                            id="search-input"
                            value="{{ request()->search }}" 
                            placeholder="Search items by title, description, or location..." 
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#123A7D]/50 focus:border-[#123A7D] text-sm"
                        />
                        {{-- Search loading indicator (shown during debounced search) --}}
                        <div id="search-loading" class="absolute right-3 top-1/2 -translate-y-1/2 hidden">
                            <svg class="animate-spin h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </div>
                    <x-ui.button-primary type="button" onclick="applyFilters()" size="md">
                        Search
                    </x-ui.button-primary>
                    @if(request()->hasAny(['search', 'type', 'status', 'category', 'date_from', 'date_to']))
                        <a 
                            href="{{ route('item') }}" 
                            class="px-4 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm font-medium whitespace-nowrap flex items-center justify-center"
                        >
                            Clear
                        </a>
                    @endif
                </div>

                {{-- Advanced Filters (Collapsible) --}}
                <div class="border-t border-gray-200 pt-4">
                    <button 
                        type="button" 
                        onclick="toggleAdvancedFilters()"
                        class="w-full flex items-center justify-between text-left font-semibold text-gray-900 hover:text-[#123A7D] transition-colors py-2"
                    >
                        <span class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            Advanced Filters
                        </span>
                        <svg id="advanced-filters-arrow" class="w-5 h-5 transform transition-transform duration-300 {{ request()->hasAny(['type']) || request()->has('status') || request()->has('category') || request()->hasAny(['date_from', 'date_to']) ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    
                    <div id="advanced-filters-content" class="{{ request()->hasAny(['type']) || request()->has('status') || request()->has('category') || request()->hasAny(['date_from', 'date_to']) ? '' : 'hidden' }} mt-4 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            {{-- Type Filter --}}
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">Type</label>
                                <select 
                                    id="type-select"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#123A7D]/50 focus:border-[#123A7D] text-sm"
                                >
                                    <option value="">All Types</option>
                                    <option value="lost" {{ request()->type == 'lost' ? 'selected' : '' }}>Lost Items</option>
                                    <option value="found" {{ request()->type == 'found' ? 'selected' : '' }}>Found Items</option>
                                </select>
                            </div>

                            {{-- Status Filter (Multi-select with Checkboxes) --}}
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">Status</label>
                                <div class="relative" id="status-dropdown-container">
                                    <button 
                                        type="button"
                                        onclick="toggleStatusDropdown()"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#123A7D]/50 focus:border-[#123A7D] text-sm text-left bg-white flex items-center justify-between"
                                    >
                                        <span id="status-selected-text" class="text-gray-700">
                                            @php
                                                $selectedStatuses = (array)request()->get('status', []);
                                                echo empty($selectedStatuses) ? 'All Status' : count($selectedStatuses) . ' selected';
                                            @endphp
                                        </span>
                                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>
                                    @php
                                        $statusGroups = [
                                            'Lost Item Statuses' => [
                                                'LOST_REPORTED' => 'Lost Reported',
                                                'RESOLVED' => 'Resolved',
                                            ],
                                            'Found Item Statuses' => [
                                                'FOUND_UNCLAIMED' => 'Found Unclaimed',
                                                'CLAIM_PENDING' => 'Claim Pending',
                                                'CLAIM_APPROVED' => 'Claim Approved',
                                                'COLLECTED' => 'Collected',
                                            ],
                                        ];
                                        $activeStatuses = (array) request()->get('status', []);
                                    @endphp
                                    <div 
                                        id="status-dropdown"
                                        class="absolute z-20 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-64 overflow-y-auto hidden"
                                    >
                                        <div class="p-2 space-y-4">
                                            @foreach($statusGroups as $groupLabel => $options)
                                                <div>
                                                    <p class="px-2 text-xs font-semibold text-gray-500">{{ $groupLabel }}</p>
                                                    @foreach($options as $value => $label)
                                                        <label class="flex items-center px-2 py-1.5 hover:bg-gray-50 rounded cursor-pointer">
                                                            <input 
                                                                type="checkbox" 
                                                                class="status-checkbox w-4 h-4 text-[#123A7D] border-gray-300 rounded focus:ring-[#123A7D]"
                                                                value="{{ $value }}"
                                                                data-status="{{ $label }}"
                                                                {{ in_array($value, $activeStatuses) ? 'checked' : '' }}
                                                                onchange="updateStatusSelection()"
                                                            />
                                                            <span class="ml-2 text-sm text-gray-700">{{ $label }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Category Filter (Multi-select with Checkboxes) --}}
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">Category</label>
                                <div class="relative" id="category-dropdown-container">
                                    <button 
                                        type="button"
                                        onclick="toggleCategoryDropdown()"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#123A7D]/50 focus:border-[#123A7D] text-sm text-left bg-white flex items-center justify-between"
                                    >
                                        <span id="category-selected-text" class="text-gray-700">
                                            @php
                                                $selectedCategories = (array)request()->get('category', []);
                                                echo empty($selectedCategories) ? 'All Categories' : count($selectedCategories) . ' selected';
                                            @endphp
                                        </span>
                                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>
                                    <div 
                                        id="category-dropdown"
                                        class="absolute z-20 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-64 overflow-y-auto hidden"
                                    >
                                        <div class="p-2 space-y-1">
                                            @foreach($categories as $category)
                                                <label class="flex items-center px-2 py-1.5 hover:bg-gray-50 rounded cursor-pointer">
                                                    <input 
                                                        type="checkbox" 
                                                        class="category-checkbox w-4 h-4 text-[#123A7D] border-gray-300 rounded focus:ring-[#123A7D]"
                                                        value="{{ $category->id }}"
                                                        data-category="{{ $category->name }}"
                                                        {{ in_array($category->id, (array)request()->get('category', [])) ? 'checked' : '' }}
                                                        onchange="updateCategorySelection()"
                                                    />
                                                    <span class="ml-2 text-sm text-gray-700">{{ $category->name }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Date Range Filter with Apply Button --}}
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">Date Range (Lost/Found Date)</label>
                                <div class="flex flex-col sm:flex-row gap-2 sm:items-end">
                                    <div class="flex flex-col sm:flex-row gap-2 flex-1">
                                        <input 
                                            type="date" 
                                            id="date-from"
                                            value="{{ request()->date_from }}" 
                                            class="flex-1 px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#123A7D]/50 focus:border-[#123A7D] text-sm min-h-[44px]"
                                            placeholder="From"
                                        />
                                        <span class="self-center text-gray-500 hidden sm:inline">to</span>
                                        <input 
                                            type="date" 
                                            id="date-to"
                                            value="{{ request()->date_to }}" 
                                            class="flex-1 px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#123A7D]/50 focus:border-[#123A7D] text-sm min-h-[44px]"
                                            placeholder="To"
                                        />
                                    </div>
                                    <div class="flex gap-2 sm:flex-shrink-0">
                                        <button 
                                            onclick="applyFilters()"
                                            class="flex-1 sm:flex-none px-4 py-2.5 bg-[#123A7D] text-white rounded-lg hover:bg-[#10316A] active:bg-[#0d2757] transition-colors text-sm font-medium whitespace-nowrap min-h-[44px] cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#123A7D]/50 focus:ring-offset-2"
                                        >
                                            Apply
                                        </button>
                                        @if(request()->hasAny(['search', 'type', 'status', 'category', 'date_from', 'date_to']))
                                            <button 
                                                onclick="clearFilters()"
                                                class="flex-1 sm:flex-none px-4 py-2.5 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 active:bg-gray-400 transition-colors text-sm font-medium whitespace-nowrap min-h-[44px] cursor-pointer focus:outline-none focus:ring-2 focus:ring-gray-400/50 focus:ring-offset-2"
                                            >
                                                Clear
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </x-ui.card>

        {{-- Items Table --}}
        <div id="items-table">
            @include('components.item-table', ['items' => $items, 'currentSort' => $currentSort ?? ['column' => 'created_at', 'direction' => 'desc']])
        </div>
    </div>

    {{-- Add Item Modal (Preserving existing functionality) --}}
    {{-- Mobile: full-screen, Desktop: constrained --}}
    <div id="add-item-modal" class="fixed inset-0 bg-black/50 z-[10000] flex items-center justify-center p-0 md:p-4 hidden" onclick="if(event.target === this && event.target.id === 'add-item-modal') closeAddItemModal()">
        <div class="bg-white rounded-none md:rounded-xl shadow-2xl max-w-3xl w-full h-full md:h-auto md:max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
            {{-- Header --}}
            <div class="bg-gradient-to-r from-[#123A7D] to-[#10316A] text-white px-4 py-4 md:px-6 md:py-4 rounded-none md:rounded-t-xl flex items-center justify-between sticky top-0 z-10">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-white/20 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl md:text-2xl font-bold">Add New Item</h2>
                        <p class="text-xs md:text-sm text-white/80">Create a new found item</p>
                    </div>
                </div>
                <button 
                    type="button"
                    onclick="closeAddItemModal(); event.stopPropagation();"
                    class="text-white hover:text-gray-200 transition-colors p-2 hover:bg-white/10 rounded-lg cursor-pointer"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div class="p-4 md:p-6">

                <form id="add-item-form">
                    @csrf

                    <div class="space-y-6">
                        {{-- Basic Information Section --}}
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6 border border-blue-100">
                            <div class="flex items-center gap-2 mb-4">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                </svg>
                                <h3 class="text-lg font-semibold text-gray-900">Basic Information</h3>
                            </div>

                            <div class="space-y-4">
                                {{-- Item Name --}}
                                <x-ui.input
                                    label="Item Name"
                                    name="name"
                                    placeholder="e.g., Black Wallet, iPhone 12"
                                    required
                                />

                                {{-- Category --}}
                                <x-ui.select
                                    label="Category"
                                    name="category_id"
                                    placeholder="Select Category"
                                    :options="$categories->pluck('name', 'id')"
                                    required
                                />

                                {{-- Hidden field for type - automatically set to found for admin posts --}}
                                <input type="hidden" name="type" value="found">
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
                            <x-ui.textarea
                                name="description"
                                placeholder="Provide detailed description of the item..."
                                rows="4"
                                required
                                maxlength="1000"
                            />
                        </div>

                        {{-- Location & Date Section --}}
                        <div class="bg-white border border-gray-200 rounded-xl p-6">
                            <div class="flex items-center gap-2 mb-4">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <h3 class="text-lg font-semibold text-gray-900">Location & Date</h3>
                            </div>
                            <div class="space-y-4">
                                {{-- Location --}}
                                <x-ui.input
                                    label="Location"
                                    name="location"
                                    placeholder="Where was it lost/found? (e.g., Library Building, Room 101)"
                                    required
                                />

                                {{-- Date Lost/Found --}}
                                <x-ui.input
                                    label="Date Lost/Found"
                                    name="lost_found_date"
                                    type="date"
                                    value="{{ date('Y-m-d') }}"
                                    required
                                />
                            </div>
                        </div>


                        {{-- Actions Section --}}
                        <div class="bg-gray-50 border border-gray-200 rounded-none md:rounded-xl p-4 md:p-6">
                            <div class="flex flex-col sm:flex-row gap-3">
                                <button 
                                    type="button"
                                    onclick="closeAddItemModal()"
                                    class="flex-1 min-w-[140px] px-6 py-3 bg-white text-gray-700 rounded-lg hover:bg-gray-100 transition-colors text-sm font-semibold text-center flex items-center justify-center gap-2 shadow-sm hover:shadow-md border border-gray-300 cursor-pointer"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    Cancel
                                </button>
                                <button 
                                    type="submit"
                                    id="submit-item-btn"
                                    class="flex-1 min-w-[140px] px-6 py-3 bg-gradient-to-r from-[#123A7D] to-[#10316A] text-white rounded-lg hover:from-[#10316A] hover:to-[#0d2757] transition-colors text-sm font-semibold flex items-center justify-center gap-2 shadow-sm hover:shadow-md cursor-pointer"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Add Item
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Show Add Item Modal
    function showAddItemModal() {
        const modal = document.getElementById('add-item-modal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
        
        // Reset form and set default date
        const form = document.getElementById('add-item-form');
        if (form) {
            form.reset();
            // Set date to today
            const dateInput = form.querySelector('input[name="lost_found_date"]');
            if (dateInput) {
                const today = new Date().toISOString().split('T')[0];
                dateInput.value = today;
            }
        }
    }

    // Close Add Item Modal
    function closeAddItemModal() {
        const modal = document.getElementById('add-item-modal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
        
        // Reset form
        const form = document.getElementById('add-item-form');
        if (form) {
            form.reset();
        }
    }

    // Toggle Advanced Filters
    function toggleAdvancedFilters() {
        const content = document.getElementById('advanced-filters-content');
        const arrow = document.getElementById('advanced-filters-arrow');
        
        if (content) {
            content.classList.toggle('hidden');
        }
        if (arrow) {
            arrow.classList.toggle('rotate-180');
        }
    }

    // Toggle Status Dropdown
    function toggleStatusDropdown() {
        const dropdown = document.getElementById('status-dropdown');
        const categoryDropdown = document.getElementById('category-dropdown');
        
        // Close category dropdown if open
        if (categoryDropdown) {
            categoryDropdown.classList.add('hidden');
        }
        
        if (dropdown) {
            dropdown.classList.toggle('hidden');
        }
    }

    // Toggle Category Dropdown
    function toggleCategoryDropdown() {
        const dropdown = document.getElementById('category-dropdown');
        const statusDropdown = document.getElementById('status-dropdown');
        
        // Close status dropdown if open
        if (statusDropdown) {
            statusDropdown.classList.add('hidden');
        }
        
        if (dropdown) {
            dropdown.classList.toggle('hidden');
        }
    }

    // Update Status Selection Text
    function updateStatusSelection() {
        const checkboxes = document.querySelectorAll('.status-checkbox:checked');
        const selectedText = document.getElementById('status-selected-text');
        
        if (selectedText) {
            if (checkboxes.length === 0) {
                selectedText.textContent = 'All Status';
            } else {
                selectedText.textContent = checkboxes.length + ' selected';
            }
        }
    }

    // Update Category Selection Text
    function updateCategorySelection() {
        const checkboxes = document.querySelectorAll('.category-checkbox:checked');
        const selectedText = document.getElementById('category-selected-text');
        
        if (selectedText) {
            if (checkboxes.length === 0) {
                selectedText.textContent = 'All Categories';
            } else {
                selectedText.textContent = checkboxes.length + ' selected';
            }
        }
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        const statusContainer = document.getElementById('status-dropdown-container');
        const statusDropdown = document.getElementById('status-dropdown');
        const categoryContainer = document.getElementById('category-dropdown-container');
        const categoryDropdown = document.getElementById('category-dropdown');
        
        if (statusContainer && statusDropdown && !statusContainer.contains(event.target)) {
            statusDropdown.classList.add('hidden');
        }
        
        if (categoryContainer && categoryDropdown && !categoryContainer.contains(event.target)) {
            categoryDropdown.classList.add('hidden');
        }
    });

    // Apply Filters
    function applyFilters() {
        const search = document.getElementById('search-input')?.value || '';
        const type = document.getElementById('type-select')?.value || '';
        
        // Get multiple selected values for status from checkboxes
        const statusCheckboxes = document.querySelectorAll('.status-checkbox:checked');
        const selectedStatus = Array.from(statusCheckboxes).map(cb => cb.value);
        
        // Get multiple selected values for category from checkboxes
        const categoryCheckboxes = document.querySelectorAll('.category-checkbox:checked');
        const selectedCategories = Array.from(categoryCheckboxes).map(cb => cb.value);
        
        const dateFrom = document.getElementById('date-from')?.value || '';
        const dateTo = document.getElementById('date-to')?.value || '';
        
        const params = new URLSearchParams();
        if (search) params.append('search', search);
        if (type) params.append('type', type);
        
        // Add multiple status values
        selectedStatus.forEach(status => {
            params.append('status[]', status);
        });
        
        // Add multiple category values
        selectedCategories.forEach(category => {
            params.append('category[]', category);
        });
        
        if (dateFrom) params.append('date_from', dateFrom);
        if (dateTo) params.append('date_to', dateTo);
        
        const query = params.toString();
        window.location.href = query ? `{{ route('item') }}?${query}` : '{{ route('item') }}';
    }

    // Clear Filters
    function clearFilters() {
        window.location.href = '{{ route('item') }}';
    }

    // Search with debounce functionality
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('search-input');
        
        // Debounce function to limit API calls
        let searchDebounceTimeout;
        const DEBOUNCE_DELAY = 500; // 500ms delay
        
        // Function to perform search
        const performSearch = () => {
            // Show loading state if needed
            const loadingIndicator = document.getElementById('search-loading');
            if (loadingIndicator) {
                loadingIndicator.classList.remove('hidden');
            }
            
            // Apply filters (which includes search)
            applyFilters();
        };
        
        // Add debounced search on input
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                // Clear previous timeout
                clearTimeout(searchDebounceTimeout);
                
                // Set loading indicator
                let loadingIndicator = document.getElementById('search-loading');
                if (!loadingIndicator) {
                    // Create loading indicator if it doesn't exist
                    loadingIndicator = document.createElement('div');
                    loadingIndicator.id = 'search-loading';
                    loadingIndicator.className = 'absolute right-3 top-1/2 -translate-y-1/2 hidden';
                    loadingIndicator.innerHTML = '<svg class="animate-spin h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
                    searchInput.parentElement.style.position = 'relative';
                    searchInput.parentElement.appendChild(loadingIndicator);
                }
                loadingIndicator.classList.remove('hidden');
                
                // Set new timeout for search
                searchDebounceTimeout = setTimeout(() => {
                    performSearch();
                    if (loadingIndicator) {
                        loadingIndicator.classList.add('hidden');
                    }
                }, DEBOUNCE_DELAY);
            });
            
            // Search on Enter key (immediate, no debounce)
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(searchDebounceTimeout);
                    performSearch();
                }
            });
        }

        // Form submission handler
        const form = document.getElementById('add-item-form');
        if (form) {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const submitBtn = document.getElementById('submit-item-btn');
                const originalText = submitBtn.innerHTML;
                submitBtn.setAttribute('loading', 'true');
                submitBtn.disabled = true;
                
                // Show modern loading modal
                if (typeof showSaving === 'function') {
                    showSaving('Adding item...', { subMessage: 'Please wait while we process your request' });
                }
                
                try {
                    const formData = new FormData(form);
                    const response = await fetch('{{ route('item.store') }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        },
                    });

                    // Check if response is ok
                    const responseData = await response.json();
                    
                    // Hide loading modal
                    if (typeof hideLoadingModal === 'function') {
                        hideLoadingModal();
                    }
                    
                    if (response.ok) {
                        // Show success banner notification
                        if (typeof showNotificationBanner === 'function') {
                            showNotificationBanner(responseData.message || 'Item added successfully', 'success', 3000);
                        }
                        
                        // Hide modal
                        closeAddItemModal();
                        
                        // Reload page after delay
                        setTimeout(() => {
                            window.location.reload();
                        }, 500);
                    } else {
                        // Handle validation errors or other errors
                        let errorMessage = 'Failed to add item. Please try again.';
                        
                        // Check for validation errors
                        if (responseData.errors) {
                            // Format validation errors
                            const errorList = Object.values(responseData.errors).flat().join('<br>• ');
                            errorMessage = `Please fix the following errors:<br>• ${errorList}`;
                        } else if (responseData.message) {
                            errorMessage = responseData.message;
                        }
                        
                        // Show detailed error message
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                html: errorMessage,
                                confirmButtonText: 'OK'
                            });
                        } else {
                            alert(errorMessage);
                        }
                    }
                } catch (error) {
                    // Hide loading modal
                    if (typeof hideLoadingModal === 'function') {
                        hideLoadingModal();
                    }
                    
                    // Show error message for network or unexpected errors
                    const errorMessage = error.message || 'An unexpected error occurred. Please check your connection and try again.';
                    
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Connection Error',
                            text: errorMessage,
                            confirmButtonText: 'OK'
                        });
                    } else {
                        alert(errorMessage);
                    }
                    
                    console.error('Error submitting form:', error);
                } finally {
                    submitBtn.removeAttribute('loading');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            });
        }


        {{-- Session flash messages are now handled by banner notifications above --}}
        {{-- Removed duplicate SweetAlert2 notifications to avoid duplicate messages --}}
    });
</script>
@endpush
