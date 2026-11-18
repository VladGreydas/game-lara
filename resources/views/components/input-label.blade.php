@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-[#d4d4d4]']) }}>
    {{ $value ?? $slot }}
</label>
