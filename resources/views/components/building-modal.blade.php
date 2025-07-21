<!-- ADD/EDIT MODAL -->
<div id="addEditModal" class="fixed inset-0 bg-white bg-opacity-50 z-50 flex items-center justify-center hidden">
  <div class="bg-white w-full max-w-lg rounded-lg shadow p-6 space-y-4">
    <h3 class="text-xl font-bold mb-2">Add / Edit Building</h3>
    
    <div class="space-y-2">
      <input type="text" placeholder="Building Name" class="w-full px-4 py-2 border rounded bg-gray-100 text-sm">
      <textarea placeholder="Description" class="w-full px-4 py-2 border rounded bg-gray-100 text-sm"></textarea>
      <select class="w-full px-4 py-2 border rounded bg-gray-100 text-sm">
        <option>Academic</option>
        <option>Facility</option>
        <option>Admin</option>
      </select>
      <input type="text" placeholder="Room List (comma-separated)" class="w-full px-4 py-2 border rounded bg-gray-100 text-sm">
      <input type="text" placeholder="Latitude" class="w-full px-4 py-2 border rounded bg-gray-100 text-sm">
      <input type="text" placeholder="Longitude" class="w-full px-4 py-2 border rounded bg-gray-100 text-sm">
    </div>

    <div class="flex justify-end gap-2 mt-4">
      <button onclick="document.getElementById('addEditModal').classList.add('hidden')" class="px-4 py-2 text-sm rounded bg-gray-300 hover:bg-gray-400">Cancel</button>
      <button class="px-4 py-2 text-sm rounded bg-blue-600 text-white hover:bg-blue-700">Save</button>
    </div>
  </div>
</div>
