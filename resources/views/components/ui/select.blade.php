{{--
    Reusable Select Component
    Props:
    - label: select label text
    - name: select name attribute (required)
    - value: selected value
    - options: array of options [value => label] or Collection
    - placeholder: placeholder text
    - required: true|false
    - error: error message to display
    - helper: helper text below select
    - disabled: true|false
--}}
@props([
    'label' => null,
    'name',
    'value' => null,
    'options' => [],
    'placeholder' => 'Select an option',
    'required' => false,
    'error' => null,
    'helper' => null,
    'disabled' => false,
])

@php
    // Base select classes
    $selectBaseClasses = 'w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 transition-colors duration-200 appearance-none bg-white';
    
    // Error state classes
    $selectClasses = $error 
        ? $selectBaseClasses . ' border-red-300 focus:ring-red-500/50 focus:border-red-500' 
        : $selectBaseClasses . ' border-gray-300 focus:ring-[#123A7D]/50 focus:border-[#123A7D]';
    
    // Disabled state
    if ($disabled) {
        $selectClasses .= ' bg-gray-100 cursor-not-allowed opacity-60';
    }
    
    // Convert Collection to array if needed
    if (is_object($options) && method_exists($options, 'toArray')) {
        $options = $options->toArray();
    }
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
    
    {{-- Select Wrapper with Dropdown Icon --}}
    <div class="relative">
        <select
            name="{{ $name }}"
            id="{{ $name }}"
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $attributes->merge(['class' => $selectClasses]) }}
        >
            {{-- Placeholder Option --}}
            @if($placeholder && !$required)
                <option value="">{{ $placeholder }}</option>
            @endif
            
            {{-- Options --}}
            @foreach($options as $optionValue => $optionLabel)
                <option 
                    value="{{ $optionValue }}"
                    {{ old($name, $value) == $optionValue ? 'selected' : '' }}
                >
                    {{ $optionLabel }}
                </option>
            @endforeach
        </select>
        
        {{-- Dropdown Arrow Icon --}}
        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </div>
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

