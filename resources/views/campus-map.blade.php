@extends('layouts.app')

@section('content')
<div class="bg-gray-50 min-h-full">
  <!-- Header Section -->
  <div class="bg-white shadow-sm border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="py-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
          <div class="mb-4 sm:mb-0">
            <h1 class="text-3xl font-bold text-gray-900">Campus Navigation</h1>
            <p class="mt-1 text-sm text-gray-600">Explore and manage campus buildings and facilities</p>
          </div>
          <div class="flex flex-col sm:flex-row gap-3">
            <button onclick="openAddModal()" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
              </svg>
              Add Building
            </button>
            <button onclick="openCategoryModal()" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
              </svg>
              Manage Categories
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Main Content -->
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Search and Filter Section -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
      <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div class="flex-1 max-w-md">
          <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search Buildings</label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
            </div>
            <input type="text" id="search" placeholder="Search by building name, description, or category..." class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
          </div>
        </div>
        
        <!-- View Toggle Buttons -->
        <div class="flex items-center space-x-2">
          <span class="text-sm font-medium text-gray-700">View:</span>
          <div class="bg-gray-100 rounded-lg p-1">
            <button id="mapViewBtn" class="view-toggle px-4 py-2 text-sm font-medium rounded-md transition-all duration-200 bg-white text-gray-900 shadow-sm border border-gray-300 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
              <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 4m0 13V4m-6 3l6-3"></path>
              </svg>
              Map View
            </button>
            <button id="listViewBtn" class="view-toggle px-4 py-2 text-sm font-medium rounded-md transition-all duration-200 text-gray-600 hover:text-gray-900 hover:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
              <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
              </svg>
              List View
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Map View Section -->
    <div id="mapSection" class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-6">
      <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
        <h3 class="text-lg font-medium text-gray-900 flex items-center">
          <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
          </svg>
          Campus Map
        </h3>
        <p class="mt-1 text-sm text-gray-600">Interactive map showing all campus buildings and facilities</p>
      </div>
      <div class="h-[600px] w-full relative">
        <div id="leafletMap" class="h-full w-full"></div>
      </div>
    </div>

    <!-- Building List View Section (Hidden by Default) -->
    <div id="buildingListSection" class="hidden">
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
          <h3 class="text-lg font-medium text-gray-900 flex items-center">
            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
            </svg>
            Building Directory
          </h3>
          <p class="mt-1 text-sm text-gray-600">Complete list of all campus buildings with detailed information</p>
        </div>
        
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Building Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room Count</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rooms</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Coordinates</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sticky right-0 z-10 bg-gray-50">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              @for ($i = 1; $i <= 5; $i++)
              <tr class="hover:bg-gray-50 transition-colors duration-150">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $i }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">Building {{ $i }}</td>
                <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate">Description for Building {{ $i }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    {{ ['Academic', 'Facility', 'Admin'][$i % 3] }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">3</td>
                <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate">Room A, Room B, Room C</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <div class="flex flex-col">
                    <span class="text-xs">Lat: 7.359209</span>
                    <span class="text-xs">Lng: 125.706379</span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium sticky right-0 z-10 bg-white shadow-md">
                  <div class="flex items-center space-x-2">
                    <button onclick="openViewModal({{ $i }})" class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150">
                      <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                      </svg>
                      View
                    </button>
                    <button onclick="openEditModal({{ $i }})" class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-yellow-700 bg-yellow-100 hover:bg-yellow-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-colors duration-150">
                      <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                      </svg>
                      Edit
                    </button>
                    <button onclick="confirmDelete({{ $i }})" class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-150">
                      <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                      </svg>
                      Delete
                    </button>
                  </div>
                </td>
              </tr>
              @endfor
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modals -->
@include('components.building-modal')
@include('components.building-view')
@include('components.category-modal')

<!-- Leaflet CDN -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<!-- Scripts -->
<script>
  const mapBtn = document.getElementById('mapViewBtn');
  const listBtn = document.getElementById('listViewBtn');
  const mapSection = document.getElementById('mapSection');
  const buildingListSection = document.getElementById('buildingListSection');
  let map = null;

  function activateButton(activeBtn, inactiveBtn) {
    // Set active button to white background with shadow
    activeBtn.classList.remove('text-gray-600');
    activeBtn.classList.add('bg-white', 'text-gray-900', 'shadow-sm', 'border-gray-300');

    // Set inactive button back to transparent
    inactiveBtn.classList.remove('bg-white', 'text-gray-900', 'shadow-sm', 'border-gray-300');
    inactiveBtn.classList.add('text-gray-600');
  }

  mapBtn.addEventListener('click', () => {
    mapSection.classList.remove('hidden');
    buildingListSection.classList.add('hidden');
    activateButton(mapBtn, listBtn);
    // Reinitialize map if it was destroyed
    if (!map) {
      initializeMap();
    }
  });

  listBtn.addEventListener('click', () => {
    buildingListSection.classList.remove('hidden');
    mapSection.classList.add('hidden');
    activateButton(listBtn, mapBtn);
  });

  // Search functionality
  document.getElementById('search').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('#buildingListSection tbody tr');
    
    rows.forEach(row => {
      const text = row.textContent.toLowerCase();
      if (text.includes(searchTerm)) {
        row.style.display = '';
      } else {
        row.style.display = 'none';
      }
    });
  });

  function openAddModal() {
    // Disable map interaction when modal opens
    if (map) {
      map.dragging.disable();
      map.touchZoom.disable();
      map.doubleClickZoom.disable();
      map.scrollWheelZoom.disable();
      map.boxZoom.disable();
      map.keyboard.disable();
      map.removeEventListener('click');
    }
    
    const modal = document.getElementById('addEditModal');
    const modalContent = document.getElementById('addEditModalContent');
    
    modal.classList.remove('hidden');
    
    // Trigger animation
    setTimeout(() => {
      modalContent.classList.remove('scale-95', 'opacity-0');
      modalContent.classList.add('scale-100', 'opacity-100');
    }, 10);
  }

  function openEditModal(id) {
    // Disable map interaction when modal opens
    if (map) {
      map.dragging.disable();
      map.touchZoom.disable();
      map.doubleClickZoom.disable();
      map.scrollWheelZoom.disable();
      map.boxZoom.disable();
      map.keyboard.disable();
      map.removeEventListener('click');
    }
    
    const modal = document.getElementById('addEditModal');
    const modalContent = document.getElementById('addEditModalContent');
    
    modal.classList.remove('hidden');
    
    // Trigger animation
    setTimeout(() => {
      modalContent.classList.remove('scale-95', 'opacity-0');
      modalContent.classList.add('scale-100', 'opacity-100');
    }, 10);
  }

  function openViewModal(id) {
    // Disable map interaction when modal opens
    if (map) {
      map.dragging.disable();
      map.touchZoom.disable();
      map.doubleClickZoom.disable();
      map.scrollWheelZoom.disable();
      map.boxZoom.disable();
      map.keyboard.disable();
      map.removeEventListener('click');
    }
    
    const modal = document.getElementById('viewModal');
    const modalContent = document.getElementById('viewModalContent');
    
    modal.classList.remove('hidden');
    
    // Trigger animation
    setTimeout(() => {
      modalContent.classList.remove('scale-95', 'opacity-0');
      modalContent.classList.add('scale-100', 'opacity-100');
    }, 10);
  }

  function openCategoryModal() {
    // Disable map interaction when modal opens
    if (map) {
      map.dragging.disable();
      map.touchZoom.disable();
      map.doubleClickZoom.disable();
      map.scrollWheelZoom.disable();
      map.boxZoom.disable();
      map.keyboard.disable();
      map.removeEventListener('click');
    }
    
    const modal = document.getElementById('categoryModal');
    const modalContent = document.getElementById('categoryModalContent');
    
    modal.classList.remove('hidden');
    
    // Trigger animation
    setTimeout(() => {
      modalContent.classList.remove('scale-95', 'opacity-0');
      modalContent.classList.add('scale-100', 'opacity-100');
    }, 10);
  }

  // Close modal functions
  function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    const modalContent = document.getElementById(modalId + 'Content');
    
    // Start closing animation
    modalContent.classList.remove('scale-100', 'opacity-100');
    modalContent.classList.add('scale-95', 'opacity-0');
    
    // Hide modal after animation
    setTimeout(() => {
      modal.classList.add('hidden');
    }, 300);
    
    // Re-enable map interaction if we're in map view
    if (!mapSection.classList.contains('hidden') && map) {
      setTimeout(() => {
        map.dragging.enable();
        map.touchZoom.enable();
        map.doubleClickZoom.enable();
        map.scrollWheelZoom.enable();
        map.boxZoom.enable();
        map.keyboard.enable();
        map.invalidateSize();
      }, 350);
    }
  }

  // Close modal when clicking outside
  document.addEventListener('click', function(event) {
    const modals = ['addEditModal', 'viewModal', 'categoryModal'];
    
    modals.forEach(modalId => {
      const modal = document.getElementById(modalId);
      const modalContent = document.getElementById(modalId + 'Content');
      
      if (modal && !modal.classList.contains('hidden')) {
        if (event.target === modal) {
          closeModal(modalId);
        }
      }
    });
  });

  // Close modal with Escape key
  document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
      const modals = ['addEditModal', 'viewModal', 'categoryModal'];
      
      modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (modal && !modal.classList.contains('hidden')) {
          closeModal(modalId);
        }
      });
    }
  });

  function confirmDelete(id) {
    Swal.fire({
      title: 'Are you sure?',
      text: 'You won\'t be able to revert this!',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Yes, delete it!'
    });
  }

  // Initialize map function
  function initializeMap() {
    if (map) {
      map.remove();
    }
    
    map = L.map('leafletMap').setView([7.359209, 125.706379], 18);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    const buildings = @json($buildings);

    buildings.forEach(b => {
      if (b.latitude && b.longitude) {
        const marker = L.marker([b.latitude, b.longitude]).addTo(map);
        marker.bindPopup(`
          <div class="p-2">
            <h3 class="font-semibold text-gray-900">${b.name}</h3>
            <p class="text-sm text-gray-600">${b.category.name}</p>
            <p class="text-xs text-gray-500">Rooms: ${b.rooms?.join(', ') || 'N/A'}</p>
          </div>
        `);
      }
    });
  }

  // Initialize map on page load
  document.addEventListener('DOMContentLoaded', function() {
    initializeMap();
  });

  // Handle window resize
  window.addEventListener('resize', function() {
    if (map && !mapSection.classList.contains('hidden')) {
      setTimeout(() => {
        map.invalidateSize();
      }, 100);
    }
  });
</script>
@endsection
