<div class="w-full overflow-x-auto shadow-md rounded-lg">
    <table class="w-full divide-y divide-gray-200">
        <thead class="bg-blue-600">
            <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Name</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Email</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Role</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Status</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider bg-blue-600 sticky right-0 z-10 shadow-md">
                    Actions
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @foreach ($users as $user)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{$user['name']}}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-500 max-w-xs truncate">{{$user['email']}}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                            ADMIN
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <!-- This well be hardcoded for now -->
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                            Active
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium bg-white sticky right-0 z-10 shadow-md">
                        <div class="flex items-center">
                            <a href="{{ route('users.edit-view', $user['id']) }}" class="text-blue-600 rounded text-xs mr-2">
                                <x-heroicon-m-pencil-square class="h-5 w-5"/>
                            </a>
                            <form id="delete-form-{{ $user['id'] }}" method="POST" action="{{ route('users.destroy', $user['id']) }}" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button
                                    type="button"
                                    onclick="confirmDelete({{ $user['id'] }})"
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
    {{ $users->appends(request()->query())->links('pagination::tailwind') }}
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
