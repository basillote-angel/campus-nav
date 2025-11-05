<!-- VIEW MODAL -->
<div id="viewModal" class="fixed inset-0 bg-black/50 z-[10000] flex items-center justify-center hidden">
  <div class="bg-white w-full max-w-lg rounded-xl shadow-2xl p-6 space-y-4 mx-4 max-h-[90vh] overflow-y-auto transform transition-all duration-300 ease-out scale-95 opacity-0 border border-gray-200" id="viewModalContent">
    <div class="flex justify-between items-center sticky top-0 bg-white pb-4 border-b border-gray-200 -mx-6 px-6">
      <h3 class="text-xl font-bold text-gray-900">Building Details</h3>
      <button onclick="closeModal('viewModal')" class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-full hover:bg-gray-100">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>
    
    <div class="space-y-4">
      <div class="grid grid-cols-1 gap-4">
        <div class="p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
          <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Basic Information</h4>
          <div class="space-y-2">
            <div class="flex justify-between">
              <span class="text-sm font-medium text-gray-700">Name:</span>
              <span class="text-sm text-gray-900">Building 1</span>
            </div>
            <div class="flex justify-between">
              <span class="text-sm font-medium text-gray-700">Category:</span>
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                Academic
              </span>
            </div>
          </div>
        </div>
        
        <div class="p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
          <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Description</h4>
          <p class="text-sm text-gray-900">Sample building for demonstration purposes. This building contains various facilities and rooms for academic activities.</p>
        </div>
        
        <div class="p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
          <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Room Information</h4>
          <div class="space-y-2">
            <div class="flex justify-between">
              <span class="text-sm font-medium text-gray-700">Room Count:</span>
              <span class="text-sm text-gray-900">3</span>
            </div>
            <div>
              <span class="text-sm font-medium text-gray-700">Rooms:</span>
              <div class="mt-1 flex flex-wrap gap-1">
                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-green-100 text-green-800">Room A</span>
                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-green-100 text-green-800">Room B</span>
                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-green-100 text-green-800">Room C</span>
              </div>
            </div>
          </div>
        </div>
        
        <div class="p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
          <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Location</h4>
          <div class="space-y-1">
            <div class="flex justify-between">
              <span class="text-sm font-medium text-gray-700">Latitude:</span>
              <span class="text-sm text-gray-900">8.2</span>
            </div>
            <div class="flex justify-between">
              <span class="text-sm font-medium text-gray-700">Longitude:</span>
              <span class="text-sm text-gray-900">124.5</span>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="flex justify-end pt-4 border-t border-gray-200 sticky bottom-0 bg-white -mx-6 px-6">
      <button onclick="closeModal('viewModal')" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
        Close
      </button>
    </div>
  </div>
</div>
