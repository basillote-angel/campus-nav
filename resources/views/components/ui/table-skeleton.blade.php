{{-- 
    Table Skeleton Loader Component
    Shows animated skeleton loading state for tables
    Props:
    - rows: number of skeleton rows to show (default: 5)
    - columns: number of columns per row (default: 5)
--}}
@props([
    'rows' => 5,
    'columns' => 5,
])

<div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            {{-- Skeleton Header --}}
            <thead class="bg-gradient-to-r from-[#123A7D] to-[#10316A]">
                <tr>
                    @for($i = 0; $i < $columns; $i++)
                        <th scope="col" class="px-4 py-3">
                            <div class="h-4 bg-white/20 rounded animate-pulse"></div>
                        </th>
                    @endfor
                </tr>
            </thead>
            
            {{-- Skeleton Body --}}
            <tbody class="bg-white divide-y divide-gray-200">
                @for($j = 0; $j < $rows; $j++)
                    <tr class="animate-pulse">
                        @for($i = 0; $i < $columns; $i++)
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="h-4 bg-gray-200 rounded"></div>
                                @if($i === 0)
                                    <div class="h-3 bg-gray-100 rounded mt-2 w-2/3"></div>
                                @endif
                            </td>
                        @endfor
                    </tr>
                @endfor
            </tbody>
        </table>
    </div>
</div>

