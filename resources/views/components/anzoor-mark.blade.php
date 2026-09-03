@props(['size' => 'md'])

@php
    $box = $size === 'sm' ? 'size-10 rounded-xl' : 'size-14 rounded-2xl';
    $letter = $size === 'sm' ? 'text-lg' : 'text-2xl';
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex '.$box.' items-center justify-center bg-white text-blue-700 shadow-lg shadow-indigo-950/20']) }}>
    <span class="{{ $letter }} font-bold leading-none">A</span>
</span>
