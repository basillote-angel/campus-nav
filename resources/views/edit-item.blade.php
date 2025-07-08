@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto bg-white p-6 rounded-md mt-10">
	<div class="flex items-center  mb-4">
		<a href="{{ route('item') }}" class="text-gray-600 hover:text-green-600 rounded text-xs mr-2">
			<x-heroicon-o-arrow-small-left class="h-8 w-8"/>
		</a>
		<h2 class="text-2xl font-bold">Edit Item</h2>
	</div>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-2 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('items.update', $item->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <input 
                type="text" 
                name="name"
                value="{{ $item->name }}"
                placeholder="Item Name" 
                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500" 
                required
            />
        </div>

        <div class="mb-4">
            <select name="category" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500" required>
                <option value="" disabled>Select Category</option>
                <option value="electronics" {{ $item->category == 'electronics' ? 'selected' : '' }}>Electronics</option>
                <option value="documents" {{ $item->category == 'documents' ? 'selected' : '' }}>Documents</option>
                <option value="accessories" {{ $item->category == 'accessories' ? 'selected' : '' }}>Accessories</option>
                <option value="idOrCards" {{ $item->category == 'idOrCards' ? 'selected' : '' }}>ID or Cards</option>
                <option value="clothing" {{ $item->category == 'clothing' ? 'selected' : '' }}>Clothing</option>
                <option value="bagOrPouches" {{ $item->category == 'bagOrPouches' ? 'selected' : '' }}>Bag or Pouches</option>
                <option value="personalItems" {{ $item->category == 'personalItems' ? 'selected' : '' }}>Personal Items</option>
                <option value="schoolSupplies" {{ $item->category == 'schoolSupplies' ? 'selected' : '' }}>School Supplies</option>
                <option value="others" {{ $item->category == 'others' ? 'selected' : '' }}>Others</option>
            </select>
        </div>

        <div class="mb-4">
            <textarea 
                name="description"
                placeholder="Description"
                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                required
            >{{ $item->description }}</textarea>
        </div>

        <div class="mb-4">
            <input 
                type="text" 
                name="location"
                value="{{ $item->location }}"
                placeholder="Location" 
                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                required
            />
        </div>

        <div class="mb-4">
            <input 
                type="date" 
                name="lost_found_date"
                value="{{ $item->lost_found_date }}"
                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                required
            />
        </div>

        <div class="mb-4">
            <select name="type" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                <option value="lost" {{ $item->type == 'lost' ? 'selected' : '' }}>Lost</option>
                <option value="found" {{ $item->type == 'found' ? 'selected' : '' }}>Found</option>
            </select>
        </div>

		<div class="mb-4">
            <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                <option value="claimed" {{ $item->status == 'claimed' ? 'selected' : '' }}>Claimed</option>
                <option value="unclaimed" {{ $item->status == 'unclaimed' ? 'selected' : '' }}>Unclaimed</option>
            </select>
        </div>

        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded w-full">
            Save Changes
        </button>
    </form>
</div>
@endsection
