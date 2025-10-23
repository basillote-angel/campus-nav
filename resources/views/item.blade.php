@extends('layouts.app')

@section('content')
<div class="min-h-full">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-blue-800">Lost and Found Items</h1>
        <div class="flex items-center space-x-3">
            <div class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium flex items-center">
                <svg class="w-4 h-4 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                {{ Auth::user()->name }}
            </div>
        </div>
    </div>
    
    <button 
        id="add-item-btn"
        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-6 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition">
        + Add New Item
    </button>

    <!-- Filters and Search -->
    <div class="flex space-x-2 mb-4">
        <input 
            type="text" 
            id="search-input"
            value="{{ request()->search }}" 
            placeholder="Search items..." 
            class="flex-grow px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        />

        <select 
            id="type-select"
            class="w-full md:w-48 px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="">All Types</option>
            <option value="lost" {{ request()->type == 'lost' ? 'selected' : '' }}>Lost</option>
            <option value="found" {{ request()->type == 'found' ? 'selected' : '' }}>Found</option>
        </select>

        
    </div>
    
    <!-- Table -->
    <div id="items-table">
        @include('components.item-table', ['items' => $items])
    </div>

    <!-- Create Modal -->
    <div id="add-item-modal" class="fixed top-0 left-0 w-full h-screen overflow-y-auto bg-black/50 hidden items-center justify-center z-50">
        <div class="bg-white p-6 w-full max-w-md rounded-lg shadow-lg my-8">
            <h2 class="text-2xl font-bold mb-4">Add New Item</h2>

           <form id="add-item-form">
                @csrf

                <div class="mb-4">
                    <input 
                        type="text" 
                        name="name"
                        placeholder="Item Name" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                        required
                    />
                </div>

                <div class="mb-4">
                    <select name="category" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
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
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                    ></textarea>
                </div>

                <div class="mb-4">
                    <input 
                        type="text" 
                        name="location"
                        placeholder="Location" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                    />
                </div>

                <div class="mb-4">
                    <input 
                        type="date" 
                        name="lost_found_date"
                        placeholder="Lost or Found Date" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                    />
                </div>

                <div class="mb-4">
                    <select name="type" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="lost">Lost</option>
                        <option value="found">Found</option>
                    </select>
                </div>

                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded w-full">
                    Submit
                </button>
            </form>
        </div>
    </div>
    
    @if(session('success'))
    <script>
        window.__toastShownAt = window.__toastShownAt || 0;
        function showOnceToast(opts){
            const now = Date.now();
            if (now - window.__toastShownAt < 1000) return; // guard: 1s window
            window.__toastShownAt = now;
            Swal.fire(Object.assign({
                icon: 'success',
                position: 'center',
                showConfirmButton: false,
                timer: 1600,
                timerProgressBar: true,
            }, opts));
        }
        document.addEventListener('DOMContentLoaded', function () {
            showOnceToast({ title: @json(session('success')) });
        });
    </script>
    @endif
    
    <script>
        // Search, Filters, and table
        const searchInput = document.getElementById('search-input');
        const typeSelect = document.getElementById('type-select');
        
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
            const status = '';

            // Build the query string
            const query = new URLSearchParams({ search, type }).toString();

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
                    // Toast success at center (guarded)
                    if (typeof showOnceToast === 'function') {
                        showOnceToast({ title: 'Item added successfully.' });
                    } else {
                        // Fallback just in case helper didn't load
                        Swal.fire({
                            icon: 'success',
                            title: 'Item added successfully.',
                            position: 'center',
                            showConfirmButton: false,
                            timer: 1600,
                            timerProgressBar: true,
                        });
                    }
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
