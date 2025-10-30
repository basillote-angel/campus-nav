<div class="w-full overflow-x-auto shadow-md rounded-lg">
    <table class="w-full divide-y divide-gray-200">
        <thead class="bg-blue-600">
            <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">category</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Item Name</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Description</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Type</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Status</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Location</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Date</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider bg-blue-600 sticky right-0 z-10 shadow-md">
                    Actions
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @foreach ($items as $item)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900">{{ data_get($item, 'category') }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ data_get($item, 'name') }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-500 max-w-xs truncate">{{ data_get($item, 'description') }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if(data_get($item, 'type') == 'lost')
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                {{ data_get($item, 'type') }}
                            </span>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                found
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if(data_get($item, 'status') == 'unclaimed')
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                {{ data_get($item, 'status') }}
                            </span>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800">
                                {{ data_get($item, 'status') }}
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ data_get($item, 'location') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ data_get($item, 'lost_found_date') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium bg-white sticky right-0 z-10 shadow-md">
                        <div class="flex items-center">
                            <a href="{{ route('items.edit', ['id' => data_get($item, 'id'), 'type' => data_get($item, 'type')]) }}" class="text-blue-600 rounded text-xs mr-2">
                                <x-heroicon-m-pencil-square class="h-5 w-5"/>
                            </a>
                            <form id="delete-form-{{ data_get($item, 'id') }}" method="POST" action="{{ route('items.destroy', data_get($item, 'id')) }}" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="type" value="{{ data_get($item, 'type') }}">
                                <button
                                    type="button"
                                    onclick="confirmDelete({{ data_get($item, 'id') }})"
                                    class="text-red-600 rounded text-xs cursor-pointer"
                                >
                                    <x-heroicon-c-trash class="h-5 w-5 "/>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $items->appends(request()->query())->links('pagination::tailwind') }}
</div>

<script>
    function confirmDelete(itemId) {
        Swal.fire({
            title: 'Are you sure?',
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + itemId).submit();
            }
        });
    }
</script>
