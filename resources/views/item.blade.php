@extends('layouts.app')

@section('content')
    <h1 class="text-3xl font-bold text-green-800 mb-6">Lost and Found Items</h1>
    
    <button 
        id="add-item-btn"
        class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded mb-6 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50 transition">
        + Add New Item
    </button>

    <!-- Filters and Search -->
    <div class="flex space-x-2 mb-4">
        <input 
            type="text" 
            id="search-input"
            value="{{ request()->search }}" 
            placeholder="Search items..." 
            class="flex-grow px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
        />

        <select 
            id="type-select"
            class="w-full md:w-48 px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
            <option value="">All Types</option>
            <option value="lost" {{ request()->type == 'lost' ? 'selected' : '' }}>Lost</option>
            <option value="found" {{ request()->type == 'found' ? 'selected' : '' }}>Found</option>
        </select>

        <select 
            id="status-select"
            class="w-full md:w-48 px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
            <option value="">All Statuses</option>
            <option value="unclaimed" {{ request()->status == 'unclaimed' ? 'selected' : '' }}>Unclaimed</option>
            <option value="claimed" {{ request()->status == 'claimed' ? 'selected' : '' }}>Claimed</option>
        </select>
    </div>
    
    <!-- Table -->
    <div id="items-table">
        @include('components.item-table', ['items' => $items])
    </div>

    <!-- Create Modal -->
    <div id="add-item-modal" class="absolute top-0 left-0 w-full h-screen overflow-y-auto bg-black/50 hidden items-center justify-center z-50">
        <div class="bg-white p-6 w-full max-w-md rounded-lg shadow-lg">
            <h2 class="text-2xl font-bold mb-4">Add New Item</h2>

           <form id="add-item-form">
                @csrf

                <div class="mb-4">
                    <input 
                        type="text" 
                        name="name"
                        placeholder="Item Name" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500" 
                        required
                    />
                </div>

                <div class="mb-4">
                    <select name="category" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500" required>
                        <option value="" disabled selected>Select Category</option>
                        <option value="electronics">Electronics</option>
                        <option value="documents">Documents</option>
                        <option value="accessories">Accessories</option>
                        <option value="idOrCards">ID or Cards</option>
                        <option value="clothing">Clothing</option>
                        <option value="bagOrPouches">Bag or Pouches</option>
                        <option value="personalItems">Personal Items</option>
                        <option value="schoolSupplies">School Supplies</option>
                        <option value="others">Others</option>
                    </select>
                </div>

                <div class="mb-4">
                    <textarea 
                        type="text" 
                        name="description"
                        placeholder="Description" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                        required
                    ></textarea>
                </div>

                <div class="mb-4">
                    <input 
                        type="text" 
                        name="location"
                        placeholder="Location" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                        required
                    />
                </div>

                <div class="mb-4">
                    <input 
                        type="date" 
                        name="lost_found_date"
                        placeholder="Lost or Found Date" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                        required
                    />
                </div>

                <div class="mb-4">
                    <select name="type" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                        <option value="lost">Lost</option>
                        <option value="found">Found</option>
                    </select>
                </div>

                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded w-full">
                    Submit
                </button>
            </form>
        </div>
    </div>
    
    <script>
        // Search, Filters, and table
        const searchInput = document.getElementById('search-input');
        const typeSelect = document.getElementById('type-select');
        const statusSelect = document.getElementById('status-select');
        const itemsTable = document.getElementById('items-table');

        //Image preview
        const inputImage = document.getElementById('image-input');
        const imagePreview = document.getElementById('image-preview');
        const imagePreviewContainer = document.getElementById('image-preview-container');
        const closeImagePreviewBtn = document.getElementById('close-image-preview-btn');

        // For modal
        const addItemModal = document.getElementById('add-item-modal');
        const addItemButton = document.getElementById('add-item-btn');
        const addItemForm = document.getElementById('add-item-form');

        // Debounce for optimized AJAX requests
        let debounceTimeout;
       
        const debounce = (func, delay = 500) => {
            clearTimeout(debounceTimeout);
            debounceTimeout = setTimeout(() => func(), delay);
        };

        // Fetch Data
        const fetchItems = () => {
            const search = searchInput.value;
            const type = typeSelect.value;
            const status = statusSelect.value;

            // Build the query string
            const query = new URLSearchParams({ search, type, status }).toString();

            // Send AJAX request
            fetch(`{{ route('item') }}?${query}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(data => {
                console.log('Data', data)
                itemsTable.innerHTML = data;
            })
            .catch(error => console.error('Error fetching items:', error));
        };

        // Event Listeners
        searchInput.addEventListener('input', () => debounce(fetchItems));
        typeSelect.addEventListener('change', fetchItems);
        statusSelect.addEventListener('change', fetchItems);

        addItemButton.addEventListener('click', () => {
            addItemModal.classList.toggle('hidden');
            addItemModal.classList.add('flex');
        });

        addItemModal.addEventListener('click', (e) => {
            if (e.target !== addItemModal) return;
            addItemModal.classList.add('hidden');
        });

        // Handle form submission
        addItemForm.addEventListener('submit', (e) => {
            e.preventDefault();

            const formData = new FormData(addItemForm);

            fetch("{{ route('item.store') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Close modal
                    addItemModal.classList.add('hidden');
                    // Clear form
                    addItemForm.reset();
                    // Refresh items list
                    fetchItems();
                } else {
                    alert('Error adding item.');
                }
            })
            .catch(error => console.error('Error:', error));
        });

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
                    // If not an image, hide the preview
                    clearImage();
                    alert('Please select an image file.');
                }
            } else {
                // If no file selected, hide the preview
                imagePreviewContainer.classList.add('hidden');
                imagePreview.src = '#';
            }
        })

        const clearImage = () => {
            imagePreviewContainer.classList.add('hidden');
            imagePreview.src = '#';
            inputImage.value = ''; // Clear the file input
        }

        closeImagePreviewBtn.addEventListener('click', () => {
            clearImage();
        })
    </script>
@endsection
