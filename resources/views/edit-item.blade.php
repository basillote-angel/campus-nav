@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto bg-white p-6 rounded-md mt-10">
	<div class="flex items-center  mb-4">
		<a href="{{ route('item') }}" class="text-gray-600 hover:text-blue-600 rounded text-xs mr-2">
			<x-heroicon-o-arrow-small-left class="h-8 w-8"/>
		</a>
		<h2 class="text-2xl font-bold">Edit Item</h2>
	</div>

    @if(session('success'))
        <div class="bg-blue-100 text-blue-800 p-2 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('items.update', $item->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <input 
                type="text" 
                name="title"
                value="{{ $item->name }}"
                placeholder="Item Title" 
                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                required
            />
        </div>

        <div class="mb-4">
            <select name="category_id" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                <option value="" disabled>Select Category</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ (int)$selectedCategoryId === (int)$cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-4">
            <textarea 
                name="description"
                placeholder="Description"
                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
            >{{ $item->description }}</textarea>
        </div>

        <div class="mb-4">
            <input 
                type="text" 
                name="location"
                value="{{ $item->location }}"
                placeholder="Location" 
                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
            />
        </div>

        <div class="mb-4">
            <input 
                type="date" 
                name="date"
                value="{{ $item->lost_found_date }}"
                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
        </div>

        <div class="mb-4">
            <input type="hidden" name="type" value="{{ $item->type }}" />
            <input type="hidden" name="originalType" value="{{ $item->type }}" />
            <select name="type" disabled class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-100 text-gray-600">
                <option value="lost" {{ $item->type == 'lost' ? 'selected' : '' }}>Lost</option>
                <option value="found" {{ $item->type == 'found' ? 'selected' : '' }}>Found</option>
            </select>
        </div>

		<div class="mb-4">
            <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                @if($item->type === 'lost')
                    <option value="open" {{ $item->status == 'open' ? 'selected' : '' }}>Open</option>
                    <option value="matched" {{ $item->status == 'matched' ? 'selected' : '' }}>Matched</option>
                    <option value="closed" {{ $item->status == 'closed' ? 'selected' : '' }}>Closed</option>
                @else
                    <option value="unclaimed" {{ $item->status == 'unclaimed' ? 'selected' : '' }}>Unclaimed</option>
                    <option value="matched" {{ $item->status == 'matched' ? 'selected' : '' }}>Matched</option>
                    <option value="returned" {{ $item->status == 'returned' ? 'selected' : '' }}>Returned</option>
                @endif
            </select>
        </div>

        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded w-full">
            Save Changes
        </button>
    </form>
</div>
@endsection
