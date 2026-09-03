@props(['type' => 'success'])

@php
$classes = match($type) {
    'success' => 'badge-success',
    'warning' => 'badge-warning',
    'danger' => 'badge-danger',
    'info' => 'badge-info',
    default => 'badge-info',
};
@endphp

<span class="{{ $classes }}">
    {{ $slot }}
</span>
