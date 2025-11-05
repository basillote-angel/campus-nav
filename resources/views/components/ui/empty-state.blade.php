{{--
    Reusable Empty State Component
    Props:
    - icon: SVG path for icon (optional)
    - title: main title text
    - description: description text
    - action-label: label for action button (optional)
    - action-url: URL for action button (optional)
    - action-onclick: onclick handler for action button (optional)
--}}
@props([
    'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
    'title' => 'No records found',
    'description' => 'There are no items to display at this time.',
    'actionLabel' => null,
    'actionUrl' => null,
    'actionOnclick' => null,
])

<div class="text-center py-12 px-4">
    {{-- Icon --}}
    @if($icon)
        <div class="flex justify-center mb-4">
            <div class="p-4 bg-gray-100 rounded-full">
                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}" />
                </svg>
            </div>
        </div>
    @endif
    
    {{-- Title --}}
    <h3 class="text-lg font-semibold text-gray-900 mb-2">
        {{ $title }}
    </h3>
    
    {{-- Description --}}
    <p class="text-gray-600 mb-6 max-w-md mx-auto">
        {{ $description }}
    </p>
    
    {{-- Custom Content Slot --}}
    @if(strlen($slot) > 0)
        <div class="mb-6">
            {{ $slot }}
        </div>
    @endif
    
    {{-- Action Button --}}
    @if($actionLabel)
        @if($actionUrl)
            <a 
                href="{{ $actionUrl }}"
                class="inline-flex items-center px-4 py-2 bg-[#123A7D] text-white font-semibold rounded-lg hover:bg-[#0E2F5E] focus:outline-none focus:ring-2 focus:ring-[#123A7D]/50 focus:ring-offset-2 transition-colors"
            >
                {{ $actionLabel }}
            </a>
        @elseif($actionOnclick)
            <button 
                type="button"
                onclick="{{ $actionOnclick }}"
                class="inline-flex items-center px-4 py-2 bg-[#123A7D] text-white font-semibold rounded-lg hover:bg-[#0E2F5E] focus:outline-none focus:ring-2 focus:ring-[#123A7D]/50 focus:ring-offset-2 transition-colors"
            >
                {{ $actionLabel }}
            </button>
        @else
            <x-ui.button-primary>
                {{ $actionLabel }}
            </x-ui.button-primary>
        @endif
    @endif
</div>

