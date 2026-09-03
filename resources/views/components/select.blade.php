@props(['label' => null, 'name', 'options' => [], 'selected' => null])

<div class="space-y-1">
    @if($label)
        <label class="text-sm font-medium text-gray-700">{{ $label }}</label>
    @endif

    <select 
        name="{{ $name }}"
        {{ $attributes->merge([
            'class' => 'w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200'
        ]) }}
    >
        <option value="">-- Pilih --</option>

        @foreach($options as $key => $value)
            <option value="{{ $key }}" @selected(old($name,$selected)==$key)>
                {{ $value }}
            </option>
        @endforeach
    </select>
</div>
