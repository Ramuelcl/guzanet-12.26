{{-- C:\laragon\www\laravel\guzanet-12.26\resources\views\components\nav-link-custom.blade.php --}}
@props(['active', 'href'])

@php
$classes = ($active ?? false)
            ? 'text-indigo-600 dark:text-info border-b-2 border-indigo-600 dark:border-info pb-1'
            : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-100 transition duration-150 ease-in-out';
@endphp

<a href="{{ $href }}" wire:navigate {{ $attributes->merge(['class' => 'text-[11px] font-black uppercase tracking-widest ' . $classes]) }}>
    {{ $slot }}
</a>