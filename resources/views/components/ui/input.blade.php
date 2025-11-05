{{--
    Reusable Input Component
    Props:
    - type: text|email|password|number|date|tel|url (default: text)
    - label: input label text
    - name: input name attribute (required)
    - value: input value
    - placeholder: placeholder text
    - required: true|false
    - error: error message to display
    - helper: helper text below input
    - icon-left: left icon SVG path
    - icon-right: right icon SVG path
    - disabled: true|false
--}}
@props([
    'type' => 'text',
    'label' => null,
    'name',
    'value' => null,
    'placeholder' => '',
    'required' => false,
    'error' => null,
    'helper' => null,
    'iconLeft' => null,
    'iconRight' => null,
    'disabled' => false,
])

@php
    // Base input classes
    $inputBaseClasses = 'w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 transition-colors duration-200';
    
    // Error state classes
    $inputClasses = $error 
        ? $inputBaseClasses . ' border-red-300 focus:ring-red-500/50 focus:border-red-500' 
        : $inputBaseClasses . ' border-gray-300 focus:ring-[#123A7D]/50 focus:border-[#123A7D]';
    
    // Disabled state
    if ($disabled) {
        $inputClasses .= ' bg-gray-100 cursor-not-allowed opacity-60';
    }
    
    // Icon padding
    $iconPaddingLeft = $iconLeft ? 'pl-10' : '';
    $iconPaddingRight = $iconRight ? 'pr-10' : '';
    
    $inputClasses .= ' ' . $iconPaddingLeft . ' ' . $iconPaddingRight;
@endphp

<div class="w-full">
    {{-- Label --}}
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-2">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif
    
    {{-- Input Wrapper with Icons --}}
    <div class="relative">
        {{-- Left Icon --}}
        @if($iconLeft)
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconLeft }}" />
                </svg>
            </div>
        @endif
        
        {{-- Input Field --}}
        <input
            type="{{ $type }}"
            name="{{ $name }}"
            id="{{ $name }}"
            value="{{ old($name, $value) }}"
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $attributes->merge(['class' => $inputClasses]) }}
        />
        
        {{-- Right Icon --}}
        @if($iconRight)
            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconRight }}" />
                </svg>
            </div>
        @endif
    </div>
    
    {{-- Helper Text --}}
    @if($helper && !$error)
        <p class="mt-1 text-xs text-gray-500">{{ $helper }}</p>
    @endif
    
    {{-- Error Message --}}
    @if($error)
        <p class="mt-1 text-xs text-red-600 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ $error }}
        </p>
    @endif
</div>

