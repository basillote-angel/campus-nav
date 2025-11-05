{{--
    Reusable Textarea Component
    Props:
    - label: textarea label text
    - name: textarea name attribute (required)
    - value: textarea value
    - placeholder: placeholder text
    - rows: number of rows (default: 4)
    - required: true|false
    - error: error message to display
    - helper: helper text below textarea
    - maxlength: maximum character count
    - disabled: true|false
    - resize: true|false - allow manual resizing (default: true)
--}}
@props([
    'label' => null,
    'name',
    'value' => null,
    'placeholder' => '',
    'rows' => 4,
    'required' => false,
    'error' => null,
    'helper' => null,
    'maxlength' => null,
    'disabled' => false,
    'resize' => true,
])

@php
    // Base textarea classes
    $textareaBaseClasses = 'w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 transition-colors duration-200 resize-none';
    
    // Error state classes
    $textareaClasses = $error 
        ? $textareaBaseClasses . ' border-red-300 focus:ring-red-500/50 focus:border-red-500' 
        : $textareaBaseClasses . ' border-gray-300 focus:ring-[#123A7D]/50 focus:border-[#123A7D]';
    
    // Disabled state
    if ($disabled) {
        $textareaClasses .= ' bg-gray-100 cursor-not-allowed opacity-60';
    }
    
    // Resize option
    if ($resize) {
        $textareaClasses .= ' resize-y';
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
            @if($maxlength)
                <span class="text-xs text-gray-500 font-normal">(Max {{ $maxlength }} characters)</span>
            @endif
        </label>
    @endif
    
    {{-- Textarea Field --}}
    <textarea
        name="{{ $name }}"
        id="{{ $name }}"
        rows="{{ $rows }}"
        placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        {{ $maxlength ? 'maxlength=' . $maxlength : '' }}
        {{ $attributes->merge(['class' => $textareaClasses]) }}
    >{{ old($name, $value) }}</textarea>
    
    {{-- Character Counter (if maxlength is set) --}}
    @if($maxlength)
        <div class="flex justify-between items-center mt-1">
            <span class="text-xs text-gray-500"></span>
            <span id="{{ $name }}-counter" class="text-xs text-gray-500">
                <span id="{{ $name }}-char-count">{{ strlen(old($name, $value ?? '')) }}</span> / {{ $maxlength }}
            </span>
        </div>
        
        {{-- JavaScript for character counter --}}
        @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const textarea = document.getElementById('{{ $name }}');
                const charCount = document.getElementById('{{ $name }}-char-count');
                
                if (textarea && charCount) {
                    textarea.addEventListener('input', function() {
                        const currentLength = this.value.length;
                        charCount.textContent = currentLength;
                        
                        // Add warning class if approaching limit
                        if (currentLength > {{ $maxlength }} * 0.9) {
                            charCount.classList.add('text-amber-600', 'font-semibold');
                        } else {
                            charCount.classList.remove('text-amber-600', 'font-semibold');
                        }
                    });
                }
            });
        </script>
        @endpush
    @endif
    
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

