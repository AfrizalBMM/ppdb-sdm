@props([
    'type' => 'button',
    'variant' => 'primary'
])

@php
$classes = match($variant) {
    'primary' => 'btn-primary',
    'secondary' => 'btn-secondary',
    'danger' => 'btn-danger',
    default => 'btn-primary',
};
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</button>
