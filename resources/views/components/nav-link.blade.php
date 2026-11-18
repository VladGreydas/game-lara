@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-[#eab308] text-sm font-medium leading-5 text-[#e5e5e5] focus:outline-none focus:border-[#eab308] transition duration-150 ease-in-out'
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-[#a3a3a3] hover:text-[#eab308] hover:border-[#eab308] focus:outline-none focus:text-[#eab308] focus:border-[#eab308] transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
