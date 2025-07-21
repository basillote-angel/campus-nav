<table class="w-full table-auto border text-left text-sm">
    <thead class="bg-gray-100">
        <tr>
            <th class="px-4 py-2">#</th>
            <th class="px-4 py-2">Name</th>
            <th class="px-4 py-2">Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($categories as $category)
            <tr class="border-t">
                <td class="px-4 py-2">{{ $category->id }}</td>
                <td class="px-4 py-2">{{ $category->name }}</td>
                <td class="px-4 py-2">
                    <a href="{{ route('categories.edit', $category) }}" class="text-blue-600 hover:underline">Edit</a>
                    <form action="{{ route('categories.destroy', $category) }}" method="POST" class="inline-block ml-2" onsubmit="return confirm('Delete this category?')">
                        @csrf @method('DELETE')
                        <button class="text-red-600 hover:underline">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="3" class="px-4 py-2 text-center text-gray-500">No categories found.</td>
            </tr>
        @endforelse
    </tbody>
</table>

@if(method_exists($categories, 'links'))
    <div class="mt-4">{{ $categories->links() }}</div>
@endif
