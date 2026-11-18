@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border-[#3f3f3f] focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm']) !!}>
