@props(['label' => null, 'name', 'type' => 'text', 'value' => null])

<div class="space-y-1">
    @if($label)
        <label class="text-sm font-medium text-gray-700">{{ $label }}</label>
    @endif

    <input 
        type="{{ $type }}"
        name="{{ $name }}"
        value="{{ old($name, $value) }}"
        {{ $attributes->merge([
            'class' => 'w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200'
        ]) }}
    >

    @error($name)
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
    @enderror
</div>
