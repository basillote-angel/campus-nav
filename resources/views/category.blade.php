@extends('layouts.app')

@section('content')
<div class="min-h-full bg-gradient-to-br from-blue-50 via-white to-indigo-50">
    <x-ui.page-header 
        title="Item Categories"
        description="Manage item categories for lost and found items"
    >
        <x-ui.button-primary 
            id="add-category-btn"
            type="button"
            variant="primary"
            size="md"
            iconLeft="M12 4v16m8-8H4"
        >
            Add New Category
        </x-ui.button-primary>
    </x-ui.page-header>

    {{-- Main Content Area --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-8">
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

                <x-ui.button-primary 
                    type="submit"
                    variant="primary"
                    size="md"
                    class="w-full"
                >
                    Submit
                </x-ui.button-primary>
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
            
            // Show loading state
            categoriesTable.innerHTML = '<div class="flex justify-center items-center py-8"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-[#123A7D]"></div></div>';

            fetch(`{{ route('categories.index') }}?${query}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.text())
            .then(html => categoriesTable.innerHTML = html)
            .catch(error => {
                console.error('Error fetching categories:', error);
                categoriesTable.innerHTML = '<div class="text-center py-8 text-red-600">Error loading categories. Please try again.</div>';
            });
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

            const submitBtn = addCategoryForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="animate-spin rounded-full h-4 w-4 border-b-2 border-white inline-block"></span> Submitting...';

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
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                
                if (data.success) {
                    fetchCategories();
                    addCategoryModal.classList.add('hidden');
                    addCategoryForm.reset();
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Category added successfully',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }
                } else {
                    let errorMessage = data.message || 'Error adding category.';
                    if (data.errors) {
                        const errorList = Object.values(data.errors).flat().join('<br>• ');
                        errorMessage = `Please fix the following errors:<br>• ${errorList}`;
                    }
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
            })
            .catch(error => {
                console.error('Error:', error);
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Connection Error',
                        text: 'Failed to connect to server. Please check your connection and try again.',
                        confirmButtonText: 'OK'
                    });
                } else {
                    alert('Connection error. Please try again.');
                }
            });
        });
    </script>
    </div>
</div>
@endsection
