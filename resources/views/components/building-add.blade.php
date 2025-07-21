<div id="addBuildingModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
  <div class="bg-white rounded-lg p-6 w-full max-w-2xl">
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-xl font-bold">Add Building</h2>
      <button onclick="toggleModal('addBuildingModal')" class="text-gray-500 hover:text-black">&times;</button>
    </div>
    <form action="{{ route('admin.buildings.store') }}" method="POST">
      @csrf
      <div class="space-y-4">
        <div>
          <label class="block font-medium">Building Name</label>
          <input name="name" class="w-full border border-gray-300 p-2 rounded" required>
        </div>
        <div>
          <label class="block font-medium">Description</label>
          <textarea name="description" class="w-full border border-gray-300 p-2 rounded"></textarea>
        </div>
        <div>
          <label class="block font-medium">Category</label>
          <select name="category_id" class="w-full border border-gray-300 p-2 rounded" required>
            @foreach($categories as $category)
              <option value="{{ $category->id }}">{{ $category->name }}</option>
            @endforeach
          </select>
        </div>
        <div id="roomFields">
          <label class="block font-medium">Rooms</label>
          <div class="flex space-x-2 mb-2">
            <input name="rooms[]" class="flex-1 border border-gray-300 p-2 rounded" placeholder="Room Name" required>
            <button type="button" class="bg-green-500 text-white px-3 rounded" onclick="addRoomField()">+</button>
          </div>
        </div>
        <div>
          <label class="block font-medium">Location (Coordinates)</label>
          <input name="coordinates" class="w-full border border-gray-300 p-2 rounded" placeholder="e.g., 10.123, 123.456">
        </div>
      </div>
      <div class="mt-6 flex justify-end">
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Save</button>
        <button type="button" onclick="toggleModal('addBuildingModal')" class="ml-2 text-gray-600">Cancel</button>
      </div>
    </form>
  </div>
</div>
