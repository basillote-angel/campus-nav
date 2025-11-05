{{-- 
    Reusable Card Component
    Props:
    - padding: sm|md|lg|none (default: md)
    - shadow: sm|md|lg|none (default: sm)
    - hover: true|false - adds hover shadow effect
    - clickable: true|false - makes card clickable with cursor pointer
    - header: optional header slot
    - footer: optional footer slot
    - divider: true|false - adds divider between header/body/footer (default: true)
--}}
@props([
    'padding' => 'md',
    'shadow' => 'sm',
    'hover' => false,
    'clickable' => false,
    'divider' => true,
])

@php
    $paddingClasses = [
        'none' => '',
        'sm' => 'p-4',
        'md' => 'p-6',
        'lg' => 'p-8',
    ];
    
    $shadowClasses = [
        'none' => '',
        'sm' => 'shadow-sm',
        'md' => 'shadow-md',
        'lg' => 'shadow-lg',
    ];
    
    $hoverClasses = $hover ? 'hover:shadow-md transition-all duration-300 transform hover:-translate-y-1' : '';
    $clickableClasses = $clickable ? 'cursor-pointer transition-all duration-200 hover:shadow-md' : '';
    
    $classes = 'bg-white rounded-xl border border-gray-100 ' . $paddingClasses[$padding] . ' ' . $shadowClasses[$shadow] . ' ' . $hoverClasses . ' ' . $clickableClasses;
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    {{-- Card Header --}}
    @if(isset($header))
        <div class="{{ $padding !== 'none' ? 'mb-' . ($padding === 'lg' ? '6' : ($padding === 'sm' ? '3' : '4')) : 'mb-4' }} {{ $divider ? 'pb-' . ($padding === 'lg' ? '6' : ($padding === 'sm' ? '3' : '4')) . ' border-b border-gray-200' : '' }}">
            {{ $header }}
        </div>
    @endif
    
    {{-- Card Body (main content) --}}
    <div>
        {{ $slot }}
    </div>
    
    {{-- Card Footer --}}
    @if(isset($footer))
        <div class="{{ $padding !== 'none' ? 'mt-' . ($padding === 'lg' ? '6' : ($padding === 'sm' ? '3' : '4')) : 'mt-4' }} {{ $divider ? 'pt-' . ($padding === 'lg' ? '6' : ($padding === 'sm' ? '3' : '4')) . ' border-t border-gray-200' : '' }}">
            {{ $footer }}
        </div>
    @endif
</div>


