{{--
    Reusable Table Component
    Props:
    - headers: array of header labels [label1, label2, ...] or [key => label] for sortable
    - sortable: array of sortable column keys (optional)
    - current-sort: current sort column (optional)
    - sort-direction: asc|desc (optional)
    - striped: true|false - alternate row colors (default: true)
    - hover: true|false - hover effect on rows (default: true)
    - empty-message: message when no data (optional)
    - empty-action: action for empty state (optional)
--}}
@props([
    'headers' => [],
    'sortable' => [],
    'currentSort' => null,
    'sortDirection' => 'asc',
    'striped' => true,
    'hover' => true,
    'emptyMessage' => 'No records found',
    'emptyAction' => null,
])

@php
    $stripedClass = $striped ? 'divide-y divide-gray-200' : '';
    $hoverClass = $hover ? 'hover:bg-gray-50' : '';
@endphp

<div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y {{ $stripedClass }}">
            {{-- Table Header --}}
            @if(!empty($headers))
                <thead class="bg-gray-50">
                    <tr>
                        @foreach($headers as $key => $header)
                            @php
                                $isSortable = in_array(is_numeric($key) ? $header : $key, $sortable);
                                $columnKey = is_numeric($key) ? $header : $key;
                                $isCurrentSort = $currentSort === $columnKey;
                            @endphp
                            <th 
                                scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider
                                {{ $isSortable ? 'cursor-pointer select-none hover:bg-gray-100' : '' }}
                                transition-colors"
                                @if($isSortable)
                                    onclick="toggleSort('{{ $columnKey }}')"
                                @endif
                            >
                                <div class="flex items-center gap-2">
                                    <span>{{ $header }}</span>
                                    @if($isSortable)
                                        <div class="flex flex-col">
                                            <svg class="w-3 h-3 {{ $isCurrentSort && $sortDirection === 'asc' ? 'text-[#123A7D]' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                            </svg>
                                            <svg class="w-3 h-3 -mt-1 {{ $isCurrentSort && $sortDirection === 'desc' ? 'text-[#123A7D]' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
            @endif
            
            {{-- Table Body --}}
            <tbody class="bg-white {{ $stripedClass }}">
                {{ $slot }}
                
                {{-- Empty State Row --}}
                @if(isset($empty) && $empty)
                    <tr>
                        <td colspan="{{ count($headers) }}" class="px-6 py-12">
                            <x-ui.empty-state 
                                title="{{ $emptyMessage }}"
                                description="There are no records to display at this time."
                                action-label="{{ $emptyAction['label'] ?? null }}"
                                action-onclick="{{ $emptyAction['onclick'] ?? null }}"
                                action-url="{{ $emptyAction['url'] ?? null }}"
                            />
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

{{-- JavaScript for sortable columns --}}
@if(!empty($sortable))
    @push('scripts')
    <script>
        function toggleSort(column) {
            const currentUrl = new URL(window.location);
            const currentSort = currentUrl.searchParams.get('sort');
            const currentDirection = currentUrl.searchParams.get('direction');
            
            let newDirection = 'asc';
            
            // If clicking the same column, toggle direction
            if (currentSort === column) {
                newDirection = currentDirection === 'asc' ? 'desc' : 'asc';
            }
            
            // Update URL parameters
            currentUrl.searchParams.set('sort', column);
            currentUrl.searchParams.set('direction', newDirection);
            
            // Reload page with new sort parameters
            window.location.href = currentUrl.toString();
        }
    </script>
    @endpush
@endif



