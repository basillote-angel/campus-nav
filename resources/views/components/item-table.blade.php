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
                        <div class="text-sm font-medium text-gray-900">{{$item['category']}}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{$item['name']}}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-500 max-w-xs truncate">{{$item['description']}}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($item['type'] == 'lost')
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                {{$item['type']}}
                            </span>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                found
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($item['status'] == 'unclaimed')
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                {{$item['status']}}
                            </span>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800">
                                {{$item['status']}}
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{$item['location']}}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{$item['lost_found_date']}}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium bg-white sticky right-0 z-10 shadow-md">
                        <div class="flex items-center">
                            <a href="{{ route('items.edit', ['id' => $item['id'], 'type' => $item['type']]) }}" class="text-blue-600 rounded text-xs mr-2">
                                <x-heroicon-m-pencil-square class="h-5 w-5"/>
                            </a>
                            <form id="delete-form-{{ $item['id'] }}" method="POST" action="{{ route('items.destroy', $item['id']) }}" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="type" value="{{ $item['type'] }}">
                                <button
                                    type="button"
                                    onclick="confirmDelete({{ $item['id'] }})"
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
