<!-- VIEW MODAL -->
<div id="viewModal" class="fixed inset-0 bg-white bg-opacity-50 z-50 flex items-center justify-center hidden">
  <div class="bg-white w-full max-w-lg rounded-lg shadow p-6 space-y-4">
    <h3 class="text-xl font-bold mb-2">Building Details</h3>
    <div class="text-sm space-y-1">
      <p><strong>Name:</strong> Building 1</p>
      <p><strong>Description:</strong> Sample building for demonstration.</p>
      <p><strong>Category:</strong> Academic</p>
      <p><strong>Room Count:</strong> 3</p>
      <p><strong>Rooms:</strong> Room A, Room B, Room C</p>
      <p><strong>Location:</strong> Lat: 8.2, Lng: 124.5</p>
    </div>
    <div class="flex justify-end mt-4">
      <button onclick="document.getElementById('viewModal').classList.add('hidden')" class="px-4 py-2 text-sm rounded bg-blue-600 text-white hover:bg-blue-700">Close</button>
    </div>
  </div>
</div>
