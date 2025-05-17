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

		 <!-- Image upload field -->
		<div class="mb-4">
			<input 
				type="file" 
				id="image-input" 
				name="image" 
				accept="image/*"
				class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
			/>
		</div>

		<!-- Image preview container -->
		<div class="mb-4">
			<div id="image-preview-container" class="relative {{ $item->image_url ? '' : 'hidden' }} mt-2 border-2 border-dashed border-gray-300 rounded-md p-2 text-center">
				
				@if ($item->image_url)
					<img id="image-preview" class="max-h-40 mx-auto" src="{{ $item->image_url }}" alt="Image Preview">
				@else
					<img id="image-preview" class="max-h-40 mx-auto" src="#" alt="Image Preview">
				@endif
				
				<button id="close-image-preview-btn" class="h-6 w-6 absolute top-2 right-2 text-gray-400 hover:text-red-500 cursor-pointer" type="button">
					<x-heroicon-c-x-mark />
				</button>
			</div>
		</div>
                
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
            <input 
                type="text" 
                name="contact_info"
                value="{{ $item->contact_info }}"
                placeholder="Contact Info (phone or email)" 
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

<script>
	// Image preview
	const inputImage = document.getElementById('image-input');
	const imagePreview = document.getElementById('image-preview');
	const imagePreviewContainer = document.getElementById('image-preview-container');
	const closeImagePreviewBtn = document.getElementById('close-image-preview-btn');

	// Preview selected image
	inputImage.addEventListener('change', (e) => {
		if (e.target.files.length > 0) {
			const selectedFile = e.target.files[0];

			// Check if selected file is an image
			if (selectedFile.type.match('image.*')) {
				const reader = new FileReader();

				reader.onload = function(e) {
					imagePreview.src = e.target.result;
					imagePreviewContainer.classList.remove('hidden');
				}

				reader.readAsDataURL(selectedFile);
			} else {
				clearImage();
				alert('Please select an image file.');
			}
		} else {
			clearImage();
		}
	});

	// Clear image preview
	const clearImage = () => {
		imagePreviewContainer.classList.add('hidden');
		imagePreview.src = '#';
		inputImage.value = ''; // Clear the file input
	}

	closeImagePreviewBtn.addEventListener('click', () => {
		clearImage();
	});

</script>
@endsection
