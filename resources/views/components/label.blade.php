@props([
    'for' => null,
    'value' => null,
])

<label 
    @if($for) for="{{ $for }}" @endif 
    {{ $attributes->merge(['class' => 'form-label']) }}
>
    {{ $value ?? $slot }}
</label>
