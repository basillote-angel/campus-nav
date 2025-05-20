@props(['label', 'value', 'iconPath'])

<div class="bg-white p-6 rounded-lg flex items-center space-x-4 border"
     style="
        border-color: rgba(78, 178, 100, 0.34);
        box-shadow: 0 4px 10px rgba(58, 99, 67, 0.25);
     ">
    <div class="p-3 rounded-full" style="background-color: rgba(45, 175, 73, 0.25); color: #2DAF49;">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPath }}" />
        </svg>
    </div>
    <div>
        <h2 class="text-lg font-semibold text-gray-700">{{ $label }}</h2>
        <p class="text-2xl font-bold text-[#2DAF49]">{{ $value }}</p>
    </div>
</div>
