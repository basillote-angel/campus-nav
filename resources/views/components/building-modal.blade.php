<!-- ADD/EDIT MODAL -->
<div id="addEditModal" class="fixed inset-0 bg-transparent z-[9999] flex items-center justify-center hidden" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0;">
  <div class="bg-white w-full max-w-lg rounded-xl shadow-2xl p-6 space-y-4 mx-4 max-h-[90vh] overflow-y-auto transform transition-all duration-300 ease-out scale-95 opacity-0 border border-gray-200" id="addEditModalContent">
    <div class="flex justify-between items-center sticky top-0 bg-white pb-4 border-b border-gray-200 -mx-6 px-6">
      <h3 class="text-xl font-bold text-gray-900">Add / Edit Building</h3>
      <button onclick="closeModal('addEditModal')" class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-full hover:bg-gray-100">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>
    
    <div class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Building Name</label>
        <input type="text" placeholder="Enter building name" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
        <textarea placeholder="Enter building description" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors resize-none"></textarea>
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
        <select class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
          <option value="">Select a category</option>
          <option value="academic">Academic</option>
          <option value="facility">Facility</option>
          <option value="admin">Admin</option>
        </select>
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Room List (comma-separated)</label>
        <input type="text" placeholder="Room A, Room B, Room C" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
      </div>
      
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Latitude</label>
          <input type="number" step="any" placeholder="7.359209" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Longitude</label>
          <input type="number" step="any" placeholder="125.706379" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
        </div>
      </div>
    </div>

    <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 sticky bottom-0 bg-white -mx-6 px-6">
      <button onclick="closeModal('addEditModal')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
        Cancel
      </button>
      <button class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
        Save Building
      </button>
    </div>
  </div>
</div>
