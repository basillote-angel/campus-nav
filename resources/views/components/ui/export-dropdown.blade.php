{{--
    Reusable Export Dropdown Component
    Props:
    - id: unique ID for the dropdown (default: 'export-dropdown')
    - routes: array of export routes ['csv' => route('items.export', ['format' => 'csv']), 'pdf' => ...]
    - labels: array of labels ['csv' => 'Export Items (CSV)', 'pdf' => 'Export Items (PDF)']
    - buttonText: text for the dropdown button (default: 'Export')
    - buttonClass: additional classes for the button
--}}
@props([
    'id' => 'export-dropdown',
    'routes' => [],
    'labels' => [],
    'buttonText' => 'Export',
    'buttonClass' => '',
])

@php
    // Default export routes if not provided
    $defaultRoutes = [
        'csv' => route('items.export', array_merge(request()->query(), ['format' => 'csv'])),
        'pdf' => route('items.export', array_merge(request()->query(), ['format' => 'pdf'])),
    ];
    
    $defaultLabels = [
        'csv' => 'Export Items (CSV)',
        'pdf' => 'Export Items (PDF)',
    ];
    
    $exportRoutes = !empty($routes) ? $routes : $defaultRoutes;
    $exportLabels = !empty($labels) ? $labels : $defaultLabels;
    
    $dropdownId = $id;
    $arrowId = $id . '-arrow';
    $containerId = $id . '-container';
@endphp

<div class="relative w-full sm:w-auto" id="{{ $containerId }}">
    <button 
        onclick="toggleExportDropdown('{{ $dropdownId }}')"
        class="w-full sm:w-auto bg-white border border-gray-300 rounded-lg px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#123A7D]/50 transition-all duration-200 flex items-center justify-center gap-2 min-h-[44px] {{ $buttonClass }}"
    >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
        </svg>
        {{ $buttonText }}
        <svg id="{{ $arrowId }}" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>
    <div 
        id="{{ $dropdownId }}"
        class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-10 hidden"
    >
        @foreach($exportRoutes as $format => $route)
            <a 
                href="{{ $route }}" 
                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2"
            >
                @if(str_contains($format, 'csv'))
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                @elseif($format === 'xlsx' || $format === 'excel')
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                @elseif($format === 'pdf')
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                @else
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                @endif
                {{ $exportLabels[$format] ?? "Export ({$format})" }}
            </a>
            @if(!$loop->last)
                <div class="border-t border-gray-200 my-1"></div>
            @endif
        @endforeach
    </div>
</div>

@once
    @push('scripts')
    <script>
        function toggleExportDropdown(dropdownId) {
            const dropdown = document.getElementById(dropdownId);
            const arrow = document.getElementById(dropdownId + '-arrow');
            
            if (dropdown && arrow) {
                dropdown.classList.toggle('hidden');
                arrow.classList.toggle('rotate-180');
            }
        }
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const containers = document.querySelectorAll('[id$="-container"]');
            containers.forEach(container => {
                const dropdown = container.querySelector('[id^="export-dropdown"]');
                if (dropdown && !container.contains(event.target)) {
                    dropdown.classList.add('hidden');
                    const arrow = container.querySelector('[id$="-arrow"]');
                    if (arrow) {
                        arrow.classList.remove('rotate-180');
                    }
                }
            });
        });
    </script>
    @endpush
@endonce

