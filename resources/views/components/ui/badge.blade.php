{{-- 
    Reusable Badge Component
    Props:
    - variant: primary|success|warning|danger|info (default: primary)
    - size: sm|md (default: md)
--}}
@props([
    'variant' => 'primary',
    'size' => 'md',
])

@php
    $sizeClasses = [
        'sm' => 'px-2 py-0.5 text-xs',
        'md' => 'px-2.5 py-0.5 text-xs',
    ];
    
    $variantClasses = [
        'primary' => 'bg-[rgba(59,130,246,0.08)] text-[rgba(59,130,246,0.8)] border border-[rgba(59,130,246,0.2)]',
        'success' => 'bg-green-100 text-green-800 border border-green-200',
        'warning' => 'bg-amber-100 text-amber-800 border border-amber-200',
        'danger' => 'bg-red-100 text-red-800 border border-red-200',
        'info' => 'bg-blue-100 text-blue-800 border border-blue-200',
    ];
    
    $classes = 'inline-flex items-center rounded-full font-medium ' . $sizeClasses[$size] . ' ' . $variantClasses[$variant];
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>


