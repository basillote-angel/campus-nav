<div id="editBuildingModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
  <div class="bg-white rounded-lg p-6 w-full max-w-2xl">
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-xl font-bold">Edit Building</h2>
      <button onclick="toggleModal('editBuildingModal')" class="text-gray-500 hover:text-black">&times;</button>
    </div>
    <form id="editBuildingForm" method="POST">
      @csrf
      @method('PUT')
      <input type="hidden" name="id" id="edit-building-id">
      <div class="space-y-4">
        <div>
          <label class="block font-medium">Building Name</label>
          <input name="name" id="edit-building-name" class="w-full border border-gray-300 p-2 rounded">
        </div>
        <div>
          <label class="block font-medium">Description</label>
          <textarea name="description" id="edit-building-description" class="w-full border border-gray-300 p-2 rounded"></textarea>
        </div>
        <div>
          <label class="block font-medium">Category</label>
          <select name="category_id" id="edit-building-category" class="w-full border border-gray-300 p-2 rounded">
            @foreach($categories as $category)
              <option value="{{ $category->id }}">{{ $category->name }}</option>
            @endforeach
          </select>
        </div>
        <div id="editRoomFields">
          <label class="block font-medium">Rooms</label>
          <div class="flex space-x-2 mb-2">
            <input name="rooms[]" class="flex-1 border border-gray-300 p-2 rounded" placeholder="Room Name">
            <button type="button" class="bg-green-500 text-white px-3 rounded" onclick="addEditRoomField()">+</button>
          </div>
        </div>
        <div>
          <label class="block font-medium">Location (Coordinates)</label>
          <input name="coordinates" id="edit-building-coordinates" class="w-full border border-gray-300 p-2 rounded">
        </div>
      </div>
      <div class="mt-6 flex justify-end">
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Save Changes</button>
        <button type="button" onclick="toggleModal('editBuildingModal')" class="ml-2 text-gray-600">Cancel</button>
      </div>
    </form>
  </div>
</div>
