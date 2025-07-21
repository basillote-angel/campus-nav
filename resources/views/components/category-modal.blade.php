<!-- CATEGORY MODAL -->
<div id="categoryModal" class="fixed inset-0 bg-white bg-opacity-50 z-50 flex items-center justify-center hidden">
  <div class="bg-white w-full max-w-md rounded-lg shadow p-6 space-y-4">
    <h3 class="text-xl font-bold mb-2">Manage Categories</h3>
    
    <div class="space-y-2">
      <input type="text" placeholder="New Category Name" class="w-full px-4 py-2 border rounded bg-gray-100 text-sm">
      <button class="w-full px-4 py-2 text-sm bg-green-500 text-white rounded hover:bg-green-600">Add Category</button>
    </div>

    <ul class="mt-4 text-sm space-y-1">
      <li class="flex justify-between items-center border-b pb-1">
        <span>Academic</span>
        <button class="text-red-500 hover:underline text-xs">Delete</button>
      </li>
      <li class="flex justify-between items-center border-b pb-1">
        <span>Facility</span>
        <button class="text-red-500 hover:underline text-xs">Delete</button>
      </li>
      <li class="flex justify-between items-center">
        <span>Admin</span>
        <button class="text-red-500 hover:underline text-xs">Delete</button>
      </li>
    </ul>

    <div class="flex justify-end mt-4">
      <button onclick="document.getElementById('categoryModal').classList.add('hidden')" class="px-4 py-2 text-sm rounded bg-blue-600 text-white hover:bg-blue-700">Close</button>
    </div>
  </div>
</div>
