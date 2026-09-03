@props(['id', 'maxWidth' => 'md', 'title' => null])

@php
$maxWidthClass = [
    'sm' => 'max-w-sm',
    'md' => 'max-w-md',
    'lg' => 'max-w-lg',
    'xl' => 'max-w-xl',
    '2xl' => 'max-w-2xl',
][$maxWidth] ?? 'max-w-md';
@endphp

<div id="{{ $id }}" 
     class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center z-[300] p-4 transition-all duration-300"
     aria-modal="true"
     role="dialog">
    <div class="bg-white rounded-3xl shadow-2xl w-full {{ $maxWidthClass }} p-8 relative transform transition-all duration-300 scale-100 overflow-hidden" onclick="event.stopPropagation()">
        
        @if(isset($icon))
            <div class = "w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4 text-2xl">
                {{ $icon }}
            </div>
        @endif

        @if($title)
            <div class="{{ isset($icon) ? 'text-center' : 'text-left mb-6' }}">
                <h3 class="text-xl font-bold text-slate-800">{{ $title }}</h3>
                @if(isset($subtitle))
                    <p class="text-sm text-slate-500 mt-1">{{ $subtitle }}</p>
                @endif
            </div>
        @endif

        <div class="text-sm text-slate-600 mb-8 {{ isset($icon) ? 'text-center' : 'mt-4' }}">
            {{ $slot }}
        </div>

        <div class="flex {{ isset($icon) ? 'justify-center' : 'justify-end' }} gap-3">
            {{ $footer ?? '' }}
        </div>
    </div>
</div>
