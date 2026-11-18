@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-[#eab308] text-start text-base font-medium text-[#eab308] bg-[#2d2d2d] focus:outline-none focus:text-[#eab308] focus:bg-[#2d2d2d] focus:border-[#eab308] transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-[#a3a3a3] hover:text-[#eab308] hover:bg-[#2d2d2d] hover:border-[#eab308] focus:outline-none focus:text-[#eab308] focus:bg-[#2d2d2d] focus:border-[#eab308] transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
