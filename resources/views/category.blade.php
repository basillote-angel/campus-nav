@extends('layouts.app')

@section('content')
    <h1 class="text-3xl font-bold text-blue-800 mb-6">Item Categories</h1>

    <button 
        id="add-category-btn"
        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-6 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition">
        + Add New Category
    </button>

    <!-- Search -->
    <div class="mb-4">
        <input 
            type="text" 
            id="search-category"
            placeholder="Search categories..." 
            class="w-full md:w-1/3 px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        />
    </div>

    <!-- Category Table -->
    <div id="categories-table">
        @include('categories.partials.table', ['categories' => $categories])
    </div>

    <!-- Add Modal -->
    <div id="add-category-modal" class="absolute top-0 left-0 w-full h-screen overflow-y-auto bg-black/50 hidden items-center justify-center z-50">
        <div class="bg-white p-6 w-full max-w-md rounded-lg shadow-lg">
            <h2 class="text-2xl font-bold mb-4">Add New Category</h2>

            <form id="add-category-form">
                @csrf
                <div class="mb-4">
                    <input 
                        type="text" 
                        name="name"
                        placeholder="Category Name" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                        required
                    />
                </div>

                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded w-full">
                    Submit
                </button>
            </form>
        </div>
    </div>

    <script>
        const searchInput = document.getElementById('search-category');
        const categoriesTable = document.getElementById('categories-table');

        const addCategoryModal = document.getElementById('add-category-modal');
        const addCategoryButton = document.getElementById('add-category-btn');
        const addCategoryForm = document.getElementById('add-category-form');

        let debounceTimeout;

        const debounce = (func, delay = 500) => {
            clearTimeout(debounceTimeout);
            debounceTimeout = setTimeout(() => func(), delay);
        };

        const fetchCategories = () => {
            const search = searchInput.value;
            const query = new URLSearchParams({ search }).toString();

            fetch(`{{ route('categories.index') }}?${query}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.text())
            .then(html => categoriesTable.innerHTML = html)
            .catch(console.error);
        };

        searchInput.addEventListener('input', () => debounce(fetchCategories));

        addCategoryButton.addEventListener('click', () => {
            addCategoryModal.classList.remove('hidden');
            addCategoryModal.classList.add('flex');
        });

        addCategoryModal.addEventListener('click', (e) => {
            if (e.target !== addCategoryModal) return;
            addCategoryModal.classList.add('hidden');
        });

        addCategoryForm.addEventListener('submit', (e) => {
            e.preventDefault();

            const formData = new FormData(addCategoryForm);

            fetch(`{{ route('categories.store') }}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    fetchCategories();
                    addCategoryModal.classList.add('hidden');
                    addCategoryForm.reset();
                } else {
                    alert('Error adding category.');
                }
            })
            .catch(console.error);
        });
    </script>
@endsection
