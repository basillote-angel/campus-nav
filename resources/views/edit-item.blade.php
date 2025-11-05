@extends('layouts.app')

@section('content')
<div class="min-h-full bg-gradient-to-br from-blue-50 via-white to-indigo-50 py-8">
    {{-- Modal Container --}}
    <div class="flex items-center justify-center px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
            {{-- Header --}}
            <div class="bg-gradient-to-r from-[#123A7D] to-[#10316A] text-white px-6 py-4 rounded-t-xl flex items-center justify-between sticky top-0 z-10">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-white/20 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold">Edit Item</h2>
                        <p class="text-sm text-white/80">Update item information</p>
                    </div>
                </div>
                <a href="{{ route('item') }}" class="text-white hover:text-gray-200 transition-colors p-2 hover:bg-white/10 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </a>
            </div>

            {{-- Success/Error Messages --}}
            @if(session('success'))
                <div class="mx-6 mt-6">
                    <x-ui.alert type="success" dismissible="true">
                        {{ session('success') }}
                    </x-ui.alert>
                </div>
            @elseif(session('error'))
                <div class="mx-6 mt-6">
                    <x-ui.alert type="error" dismissible="true">
                        {{ session('error') }}
                    </x-ui.alert>
                </div>
            @endif

            {{-- Form --}}
            <form action="{{ route('items.update', $item->id) }}" method="POST" enctype="multipart/form-data" id="edit-item-form" class="p-6">
                @csrf
                @method('PUT')

                {{-- Hidden Fields --}}
                <input type="hidden" name="type" value="{{ $item->type }}" />
                <input type="hidden" name="originalType" value="{{ $item->type }}" />

                <div class="space-y-6">
                    {{-- Item Header Section --}}
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
                                name="title"
                                value="{{ old('title', $item->name) }}"
                                placeholder="e.g., Black Wallet, iPhone 12"
                                required
                            />

                            {{-- Category --}}
                            <x-ui.select
                                label="Category"
                                name="category_id"
                                value="{{ old('category_id', $selectedCategoryId) }}"
                                placeholder="Select Category"
                                :options="$categories->pluck('name', 'id')"
                                required
                            />

                            {{-- Status --}}
                            <x-ui.select
                                label="Status"
                                name="status"
                                value="{{ old('status', $item->status) }}"
                                placeholder="Select Status"
                                :options="$item->type === 'lost' 
                                    ? ['open' => 'Open', 'matched' => 'Matched', 'closed' => 'Closed']
                                    : ['unclaimed' => 'Unclaimed', 'matched' => 'Matched', 'returned' => 'Returned']"
                            />
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
                            value="{{ old('description', $item->description) }}"
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
                                value="{{ old('location', $item->location) }}"
                                placeholder="Where was it lost/found? (e.g., Library Building, Room 101)"
                                required
                            />

                            {{-- Date Lost/Found --}}
                            <x-ui.input
                                label="Date {{ $item->type === 'lost' ? 'Lost' : 'Found' }}"
                                name="date"
                                type="date"
                                value="{{ old('date', $item->lost_found_date) }}"
                            />
                        </div>
                    </div>

                    {{-- Actions Section --}}
                    <div class="bg-gray-50 border border-gray-200 rounded-xl p-6">
                        <div class="flex flex-wrap gap-3">
                            <a href="{{ route('item') }}" class="flex-1 min-w-[140px] px-6 py-3 bg-white text-gray-700 rounded-lg hover:bg-gray-100 transition-colors text-sm font-semibold text-center flex items-center justify-center gap-2 shadow-sm hover:shadow-md border border-gray-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Cancel
                            </a>
                            <button type="submit" class="flex-1 min-w-[140px] px-6 py-3 bg-gradient-to-r from-[#123A7D] to-[#10316A] text-white rounded-lg hover:from-[#10316A] hover:to-[#0d2757] transition-colors text-sm font-semibold flex items-center justify-center gap-2 shadow-sm hover:shadow-md">
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

@endsection
