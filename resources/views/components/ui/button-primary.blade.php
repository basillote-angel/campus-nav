{{-- 
    Reusable Primary Button Component
    Props: 
    - type: button type (button|submit|reset)
    - variant: primary|secondary|danger|success|outline|outline-danger (default: primary)
    - size: sm|md|lg (default: md)
    - disabled: true|false
    - loading: true|false - shows loading spinner
    - icon-left: SVG path for left icon
    - icon-right: SVG path for right icon
    - class: additional CSS classes
--}}
@props([
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'md',
    'disabled' => false,
    'loading' => false,
    'iconLeft' => null,
    'iconRight' => null,
])

@php
    // Base classes for all buttons
    $baseClasses = 'inline-flex items-center justify-center font-semibold rounded-lg transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-offset-2 cursor-pointer';
    
    // Size variants
    $sizeClasses = [
        'sm' => 'px-3 py-1.5 text-xs gap-1.5',
        'md' => 'px-4 py-2 text-sm gap-2',
        'lg' => 'px-6 py-3 text-base gap-2.5',
    ];
    
    // Color variants
    $variantClasses = [
        'primary' => 'bg-[#123A7D] text-white hover:bg-[#0E2F5E] focus:ring-[#123A7D]/50 disabled:opacity-50 disabled:cursor-not-allowed',
        'secondary' => 'bg-gray-600 text-white hover:bg-gray-700 focus:ring-gray-500/50 disabled:opacity-50 disabled:cursor-not-allowed',
        'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500/50 disabled:opacity-50 disabled:cursor-not-allowed',
        'success' => 'bg-green-600 text-white hover:bg-green-700 focus:ring-green-500/50 disabled:opacity-50 disabled:cursor-not-allowed',
        'outline' => 'bg-white text-[#123A7D] border-2 border-[#123A7D] hover:bg-[#123A7D]/5 focus:ring-[#123A7D]/50 disabled:opacity-50 disabled:cursor-not-allowed',
        'outline-danger' => 'bg-white text-red-600 border-2 border-red-600 hover:bg-red-50 focus:ring-red-500/50 disabled:opacity-50 disabled:cursor-not-allowed',
        'ghost' => 'bg-transparent text-gray-700 hover:bg-gray-100 focus:ring-gray-500/50 disabled:opacity-50 disabled:cursor-not-allowed',
    ];
    
    $classes = $baseClasses . ' ' . $sizeClasses[$size] . ' ' . $variantClasses[$variant];
    
    // Disable if loading
    $isDisabled = $disabled || $loading;
@endphp

<button 
    type="{{ $type }}" 
    {{ $isDisabled ? 'disabled' : '' }}
    {{ $attributes->merge(['class' => $classes]) }}
>
    {{-- Loading Spinner --}}
    @if($loading)
        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    @endif
    
    {{-- Left Icon --}}
    @if($iconLeft && !$loading)
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconLeft }}" />
        </svg>
    @endif
    
    {{-- Button Content --}}
    @if(!$loading)
        {{ $slot }}
    @else
        <span class="opacity-75">Loading...</span>
    @endif
    
    {{-- Right Icon --}}
    @if($iconRight && !$loading)
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconRight }}" />
        </svg>
    @endif
</button>


