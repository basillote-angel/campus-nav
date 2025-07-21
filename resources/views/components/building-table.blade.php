<div id="buildingTable"  class="w-full overflow-x-auto shadow-md rounded-lg">
    <table class="w-full divide-y divide-gray-200">
        <thead class="bg-blue-600">
            <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">name</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">description</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">category</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">rooms</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">latitude</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">longitude</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider bg-blue-600 sticky right-0 z-10 shadow-md">
                    Actions
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @foreach ($buildings as $building)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900">{{$building['name']}}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{$building['description']}}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-500 max-w-xs truncate">{{ $building['category_id']}}</div>
                    </td>
                   
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{$building['rooms']}}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{$building['latitude']}}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{$building['longitude']}}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium bg-white sticky right-0 z-10 shadow-md">
                        <div class="flex items-center">
                            <a href="{{ route('buildings.edit', $building['id']) }}" class="text-blue-600 rounded text-xs mr-2">
                                <x-heroicon-m-pencil-square class="h-5 w-5"/>
                            </a>
                            <form id="delete-form-{{ $building['id'] }}" method="POST" action="{{ route('building.destroy', $building['id']) }}" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button
                                    type="button"
                                    onclick="confirmDelete({{ $building['id'] }})"
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
    function confirmDelete(buildingId) {
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
                document.getElementById('delete-form-' + buildingId).submit();
            }
        });
    }
</script>
