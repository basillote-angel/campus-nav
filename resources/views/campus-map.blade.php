@extends('layouts.app')

@section('content')
<div class="p-6">
  <h2 class="text-2xl font-bold mb-4">Campus Navigation – Buildings</h2>

  <!-- Action Bar -->
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
    <div class="flex items-center gap-2">
      <input type="text" placeholder="Search buildings..." class="flex-grow px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
    </div>
    <div class="flex gap-2">
      <button id="mapViewBtn" class="view-toggle px-4 py-2 text-sm rounded transition-colors duration-200 bg-yellow-300 text-black">View Map</button>
      <button id="listViewBtn" class="view-toggle px-4 py-2 text-sm rounded transition-colors duration-200 bg-blue-500 text-white hover:bg-yellow-300 hover:text-black">View Building List</button>

      <button onclick="openAddModal()" class="px-4 py-2 text-sm bg-blue-500 rounded text-white hover:bg-yellow-300 hover:text-black">Add Building</button>
      <button onclick="openCategoryModal()" class="px-4 py-2 text-sm bg-blue-500 rounded text-white hover:bg-yellow-300 hover:text-black">Manage Categories</button>
    </div>
  </div>

  <!-- MAP VIEW -->
<!-- MAP VIEW -->
<div id="mapSection" class="h-[500px] w-full rounded shadow">
  <div id="leafletMap" class="h-full w-full rounded"></div>
</div>


  <!-- BUILDING LIST VIEW (Hidden by Default) -->
  <div id="buildingListSection" class="hidden">
  <div class="w-full overflow-x-auto shadow-md rounded-lg mt-4">
    <table class="w-full divide-y divide-gray-200 text-sm">
      <thead class="bg-blue-600">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">#</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Building Name</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Description</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Category</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Room Count</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Rooms</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Longitude</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Latitude</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider sticky right-0 z-10 bg-blue-600 shadow-md">Actions</th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-200">
        @for ($i = 1; $i <= 5; $i++)
        <tr class="hover:bg-gray-50">
          <td class="px-6 py-4 text-sm text-gray-900">{{ $i }}</td>
          <td class="px-6 py-4 text-sm font-medium text-gray-900">Building {{ $i }}</td>
          <td class="px-6 py-4 text-sm text-gray-500">Description for Building {{ $i }}</td>
          <td class="px-6 py-4 text-sm text-gray-900">{{ ['Academic', 'Facility', 'Admin'][$i % 3] }}</td>
          <td class="px-6 py-4 text-sm text-gray-900">3</td>
          <td class="px-6 py-4 text-sm text-gray-500">Room A, Room B, Room C</td>
          <td class="px-6 py-4 text-sm text-gray-500">7.359209</td>
          <td class="px-6 py-4 text-sm text-gray-500">125.706379</td>
          <td class="px-6 py-4 text-sm font-medium sticky right-0 z-10 bg-white shadow-md">
            <div class="flex space-x-2">
              <button onclick="openViewModal({{ $i }})" class="text-blue-600 hover:underline text-xs">View</button>
              <button onclick="openEditModal({{ $i }})" class="text-yellow-500 hover:underline text-xs">Edit</button>
              <button onclick="confirmDelete({{ $i }})" class="text-red-600 hover:underline text-xs">Delete</button>
            </div>
          </td>
        </tr>
        @endfor
      </tbody>
    </table>
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

  function activateButton(activeBtn, inactiveBtn) {
    // Set active button to yellow/black
    activeBtn.classList.remove('bg-blue-500', 'text-white');
    activeBtn.classList.add('bg-yellow-300', 'text-black');

    // Set inactive button back to blue/white
    inactiveBtn.classList.remove('bg-yellow-300', 'text-black');
    inactiveBtn.classList.add('bg-blue-500', 'text-white');
  }

  mapBtn.addEventListener('click', () => {
    mapSection.classList.remove('hidden');
    buildingListSection.classList.add('hidden');
    activateButton(mapBtn, listBtn);
  });

  listBtn.addEventListener('click', () => {
    buildingListSection.classList.remove('hidden');
    mapSection.classList.add('hidden');
    activateButton(listBtn, mapBtn);
  });

document.getElementById('listViewBtn').addEventListener('click', (event) => {
  buildingListSection.classList.remove('hidden');
  mapSection.classList.add('hidden');

  viewBtns.forEach(btn => {
    btn.classList.remove('active', 'bg-yellow-300', 'text-black');
    btn.classList.add('bg-blue-500', 'text-white');
  });

  event.target.classList.remove('bg-blue-500', 'text-white');
  event.target.classList.add('active', 'bg-yellow-300', 'text-black');
});

  function openAddModal() {
    document.getElementById('addEditModal').classList.remove('hidden');
  }

  function openEditModal(id) {
    document.getElementById('addEditModal').classList.remove('hidden');
  }

  function openViewModal(id) {
    document.getElementById('viewModal').classList.remove('hidden');
  }

  function openCategoryModal() {
    document.getElementById('categoryModal').classList.remove('hidden');
  }

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

   const map = L.map('leafletMap').setView([7.359209, 125.706379], 18); // Or your correct campus coordinates
 // Replace w/ your campus coords

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

    const buildings = @json($buildings);

    buildings.forEach(b => {
        if (b.latitude && b.longitude) {
            const marker = L.marker([b.latitude, b.longitude]).addTo(map);
            marker.bindPopup(`<strong>${b.name}</strong><br>${b.category.name}<br>Rooms: ${b.rooms?.join(', ')}`);
        }
    });

    // Toggle views
    document.getElementById('toggleMap').onclick = () => {
        document.getElementById('mapContainer').classList.remove('hidden');
        document.getElementById('tableContainer').classList.add('hidden');
    };
    document.getElementById('toggleTable').onclick = () => {
        document.getElementById('mapContainer').classList.add('hidden');
        document.getElementById('tableContainer').classList.remove('hidden');
    };
    
</script>
@endsection
