{{-- 
    Card Skeleton Loader Component
    Shows animated skeleton loading state for cards
    Props:
    - count: number of skeleton cards to show (default: 1)
    - showImage: true|false - show skeleton image placeholder (default: false)
--}}
@props([
    'count' => 1,
    'showImage' => false,
])

@for($i = 0; $i < $count; $i++)
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 animate-pulse">
        @if($showImage)
            {{-- Skeleton Image --}}
            <div class="w-full h-48 bg-gray-200 rounded-lg mb-4"></div>
        @endif
        
        {{-- Skeleton Title --}}
        <div class="h-6 bg-gray-200 rounded w-3/4 mb-3"></div>
        
        {{-- Skeleton Content Lines --}}
        <div class="space-y-2">
            <div class="h-4 bg-gray-200 rounded w-full"></div>
            <div class="h-4 bg-gray-200 rounded w-5/6"></div>
            <div class="h-4 bg-gray-200 rounded w-4/6"></div>
        </div>
        
        {{-- Skeleton Footer/Button --}}
        <div class="mt-4 flex justify-end">
            <div class="h-10 bg-gray-200 rounded w-24"></div>
        </div>
    </div>
@endfor

