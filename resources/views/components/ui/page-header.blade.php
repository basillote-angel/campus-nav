{{--
    Reusable Page Header Component
    Props:
    - title: page title (required)
    - description: page description/subtitle
    - action-label: label for primary action button
    - action-url: URL for primary action button
    - action-onclick: onclick handler for primary action button
    - action-icon: SVG path for action button icon
    - breadcrumbs: array of breadcrumbs [label => url]
--}}
@props([
    'title',
    'description' => null,
    'actionLabel' => null,
    'actionUrl' => null,
    'actionOnclick' => null,
    'actionIcon' => null,
    'breadcrumbs' => null,
])

<div class="bg-white shadow-sm border-b border-gray-200 mb-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 md:py-6">
        {{-- Breadcrumbs (if provided) --}}
        @if($breadcrumbs && is_array($breadcrumbs) && count($breadcrumbs) > 0)
            <nav class="mb-4" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2 text-sm text-gray-500">
                    @foreach($breadcrumbs as $label => $url)
                        @if(!$loop->last)
                            <li>
                                <a href="{{ $url }}" class="hover:text-[#123A7D] transition-colors">
                                    {{ $label }}
                                </a>
                            </li>
                            <li>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </li>
                        @else
                            <li class="text-gray-900 font-medium">{{ $label }}</li>
                        @endif
                    @endforeach
                </ol>
            </nav>
        @endif
        
        {{-- Header Content --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex-1 min-w-0">
                {{-- Title --}}
                <h1 class="text-2xl md:text-3xl font-bold text-[#123A7D] mb-1 md:mb-2 leading-tight">
                    {{ $title }}
                </h1>
                
                {{-- Description --}}
                @if($description)
                    <p class="text-xs md:text-sm text-gray-600 mt-1">
                        {{ $description }}
                    </p>
                @endif
            </div>
            
            {{-- Action Button Area --}}
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-3 w-full sm:w-auto">
                {{-- Primary Action Button --}}
                @if($actionLabel)
                    @if($actionUrl)
                        <a 
                            href="{{ $actionUrl }}"
                            class="inline-flex items-center justify-center px-4 py-2.5 bg-[#123A7D] text-white font-semibold rounded-lg hover:bg-[#0E2F5E] focus:outline-none focus:ring-2 focus:ring-[#123A7D]/50 focus:ring-offset-2 transition-colors text-sm md:text-base min-h-[44px]"
                        >
                            @if($actionIcon)
                                <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $actionIcon }}" />
                                </svg>
                            @endif
                            <span class="whitespace-nowrap">{{ $actionLabel }}</span>
                        </a>
                    @elseif($actionOnclick)
                        <button 
                            type="button"
                            onclick="{{ $actionOnclick }}"
                            class="inline-flex items-center justify-center px-4 py-2.5 bg-[#123A7D] text-white font-semibold rounded-lg hover:bg-[#0E2F5E] focus:outline-none focus:ring-2 focus:ring-[#123A7D]/50 focus:ring-offset-2 transition-colors text-sm md:text-base min-h-[44px]"
                        >
                            @if($actionIcon)
                                <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $actionIcon }}" />
                                </svg>
                            @endif
                            <span class="whitespace-nowrap">{{ $actionLabel }}</span>
                        </button>
                    @else
                        <x-ui.button-primary class="w-full sm:w-auto justify-center min-h-[44px]">
                            @if($actionIcon)
                                <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $actionIcon }}" />
                                </svg>
                            @endif
                            <span class="whitespace-nowrap">{{ $actionLabel }}</span>
                        </x-ui.button-primary>
                    @endif
                @endif
                
                {{-- Additional Actions Slot (for export dropdowns, multiple buttons, etc.) --}}
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 w-full sm:w-auto">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
</div>

