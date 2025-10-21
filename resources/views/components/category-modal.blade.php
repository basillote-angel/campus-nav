<!-- CATEGORY MODAL -->
<div id="categoryModal" class="fixed inset-0 bg-transparent z-[9999] flex items-center justify-center hidden" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0;">
  <div class="bg-white w-full max-w-md rounded-xl shadow-2xl p-6 space-y-4 mx-4 max-h-[90vh] overflow-y-auto transform transition-all duration-300 ease-out scale-95 opacity-0 border border-gray-200" id="categoryModalContent">
    <div class="flex justify-between items-center sticky top-0 bg-white pb-4 border-b border-gray-200 -mx-6 px-6">
      <h3 class="text-xl font-bold text-gray-900">Manage Categories</h3>
      <button onclick="closeModal('categoryModal')" class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-full hover:bg-gray-100">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>
    
    <div class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">New Category Name</label>
        <div class="flex gap-2">
          <input type="text" placeholder="Enter category name" class="flex-1 px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
          <button class="px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
            Add
          </button>
        </div>
      </div>

      <div>
        <h4 class="text-sm font-medium text-gray-700 mb-2">Existing Categories</h4>
        <div class="space-y-2">
          <div class="flex justify-between items-center p-3 bg-gray-50 rounded-md hover:bg-gray-100 transition-colors">
            <span class="text-sm text-gray-900">Academic</span>
            <button class="text-red-500 hover:text-red-700 text-xs font-medium hover:underline transition-colors">Delete</button>
          </div>
          <div class="flex justify-between items-center p-3 bg-gray-50 rounded-md hover:bg-gray-100 transition-colors">
            <span class="text-sm text-gray-900">Facility</span>
            <button class="text-red-500 hover:text-red-700 text-xs font-medium hover:underline transition-colors">Delete</button>
          </div>
          <div class="flex justify-between items-center p-3 bg-gray-50 rounded-md hover:bg-gray-100 transition-colors">
            <span class="text-sm text-gray-900">Admin</span>
            <button class="text-red-500 hover:text-red-700 text-xs font-medium hover:underline transition-colors">Delete</button>
          </div>
        </div>
      </div>
    </div>

    <div class="flex justify-end pt-4 border-t border-gray-200 sticky bottom-0 bg-white -mx-6 px-6">
      <button onclick="closeModal('categoryModal')" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
        Close
      </button>
    </div>
  </div>
</div>
