@extends('layouts.app')

@section('content')
<div class="p-6">
  <h2 class="text-2xl font-bold mb-4">Campus Navigation – Buildings</h2>

  <!-- Action Bar -->
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
    <div class="flex flex-col md:flex-row md:items-center gap-2 w-full md:w-auto">
      <input type="text" placeholder="Search by name or category" class="px-4 py-2 border rounded-md w-full md:w-64" />
      <button class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700" id="btnViewMap">View Map</button>
      <button class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400" id="btnViewList">View Building List</button>
    </div>
    <div class="flex gap-2">
      <button class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700" onclick="toggleModal('addBuildingModal')">+ Add Building</button>
      <button class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700" onclick="toggleModal('manageCategoryModal')">Manage Categories</button>
    </div>
  </div>

  <!-- Map Section -->
  <div id="mapSection" class="h-96 rounded-lg overflow-hidden border mb-6">
    <!-- Sample Leaflet Map -->
    <iframe src="https://www.openstreetmap.org/export/embed.html" class="w-full h-full"></iframe>
  </div>

  <!-- Building List Table -->
  <div id="buildingListSection" class="hidden overflow-x-auto">
    <table class="min-w-full table-auto border border-gray-300">
      <thead class="bg-gray-100">
        <tr>
          <th class="px-4 py-2">#</th>
          <th class="px-4 py-2">Building Name</th>
          <th class="px-4 py-2">Description</th>
          <th class="px-4 py-2">Category</th>
          <th class="px-4 py-2">Rooms</th>
          <th class="px-4 py-2">Location</th>
          <th class="px-4 py-2">Actions</th>
        </tr>
      </thead>
      <tbody>
        @for ($i = 1; $i <= 5; $i++)
        <tr class="border-t">
          <td class="px-4 py-2">{{ $i }}</td>
          <td class="px-4 py-2">Building {{ $i }}</td>
          <td class="px-4 py-2">Description for building {{ $i }}</td>
          <td class="px-4 py-2">Academic</td>
          <td class="px-4 py-2">Room A, Room B</td>
          <td class="px-4 py-2">8.2142, 124.2521</td>
          <td class="px-4 py-2">
            <button class="text-blue-600 hover:underline" onclick="toggleModal('viewBuildingModal')">🔍 View</button>
            <button class="text-yellow-600 hover:underline ml-2" onclick="toggleModal('editBuildingModal')">✏️ Edit</button>
            <button class="text-red-600 hover:underline ml-2" onclick="confirm('Delete this building?')">🗑️ Delete</button>
          </td>
        </tr>
        @endfor
      </tbody>
    </table>
  </div>

  <!-- Modals -->
  @include('components.building-modals')

</div>

<script>
  const mapSection = document.getElementById('mapSection');
  const buildingListSection = document.getElementById('buildingListSection');
  const btnViewMap = document.getElementById('btnViewMap');
  const btnViewList = document.getElementById('btnViewList');

  btnViewMap.onclick = () => {
    mapSection.classList.remove('hidden');
    buildingListSection.classList.add('hidden');
    btnViewMap.classList.replace('bg-gray-300', 'bg-blue-600');
    btnViewMap.classList.replace('text-gray-800', 'text-white');
    btnViewList.classList.replace('bg-blue-600', 'bg-gray-300');
    btnViewList.classList.replace('text-white', 'text-gray-800');
  };

  btnViewList.onclick = () => {
    mapSection.classList.add('hidden');
    buildingListSection.classList.remove('hidden');
    btnViewList.classList.replace('bg-gray-300', 'bg-blue-600');
    btnViewList.classList.replace('text-gray-800', 'text-white');
    btnViewMap.classList.replace('bg-blue-600', 'bg-gray-300');
    btnViewMap.classList.replace('text-white', 'text-gray-800');
  };

  function toggleModal(id) {
    const modal = document.getElementById(id);
    modal.classList.toggle('hidden');
  }
</script>
@endsection
