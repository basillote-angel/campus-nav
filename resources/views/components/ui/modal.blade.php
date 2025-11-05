{{-- 
    Reusable Modal Component
    Props:
    - id: unique modal identifier (required)
    - title: modal title
    - size: sm|md|lg|xl (default: md)
    - closeOnBackdrop: true|false (default: true)
--}}
@props([
    'id',
    'title' => '',
    'size' => 'md',
    'closeOnBackdrop' => true,
])

@php
    $sizeClasses = [
        'sm' => 'max-w-md',
        'md' => 'max-w-lg',
        'lg' => 'max-w-2xl',
        'xl' => 'max-w-4xl',
    ];
@endphp

{{-- Modal Backdrop --}}
<div 
    id="{{ $id }}"
    class="fixed inset-0 bg-black/50 hidden items-center justify-center z-[10000] transition-opacity duration-300"
    @if($closeOnBackdrop)
        onclick="if(event.target === this) document.getElementById('{{ $id }}').classList.add('hidden')"
    @endif
>
    {{-- Modal Content --}}
    {{-- Mobile: full-screen, Desktop: constrained width --}}
    <div class="bg-white rounded-none md:rounded-xl shadow-xl {{ $sizeClasses[$size] }} w-full h-full md:h-auto md:mx-4 transform transition-all duration-300 scale-95 opacity-0 overflow-y-auto" id="{{ $id }}Content">
        {{-- Modal Header --}}
        @if($title)
            <div class="px-4 py-4 md:px-6 md:py-4 border-b border-gray-200 flex items-center justify-between sticky top-0 bg-white z-10">
                <h3 class="text-base md:text-lg font-semibold text-gray-900">{{ $title }}</h3>
                <button 
                    type="button"
                    onclick="document.getElementById('{{ $id }}').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 transition-colors"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif
        
        {{-- Modal Body --}}
        <div class="px-4 py-4 md:px-6 md:py-4">
            {{ $slot }}
        </div>
        
        {{-- Modal Footer (optional slot) --}}
        @if(isset($footer) || $attributes->has('footer'))
            <div class="px-4 py-4 md:px-6 md:py-4 border-t border-gray-200 flex flex-col sm:flex-row justify-end gap-3 sticky bottom-0 bg-white">
                {{ $footer ?? '' }}
            </div>
        @endif
    </div>
</div>

{{-- JavaScript to handle modal show/hide animations --}}
@once
    @push('scripts')
    <script>
        // Helper function to show modal
        function showModal(modalId) {
            const modal = document.getElementById(modalId);
            const content = document.getElementById(modalId + 'Content');
            if (modal && content) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                setTimeout(() => {
                    content.classList.remove('scale-95', 'opacity-0');
                    content.classList.add('scale-100', 'opacity-100');
                }, 10);
            }
        }
        
        // Helper function to hide modal
        function hideModal(modalId) {
            const modal = document.getElementById(modalId);
            const content = document.getElementById(modalId + 'Content');
            if (modal && content) {
                content.classList.remove('scale-100', 'opacity-100');
                content.classList.add('scale-95', 'opacity-0');
                setTimeout(() => {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }, 300);
            }
        }
        
    </script>
    @endpush
@endonce

