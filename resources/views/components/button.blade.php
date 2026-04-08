@props([
    'variant' => 'primary',
    'type' => 'button',
    'href' => null,
])

@php
    $classes = match($variant) {
        'primary'   => 'bg-violet-700 hover:bg-violet-600 text-white',
        'secondary' => 'bg-slate-700 hover:bg-slate-600 text-slate-100 border border-slate-600',
        'danger'    => 'bg-red-900/60 hover:bg-red-800 text-red-300 border border-red-800',
        default     => 'bg-violet-700 hover:bg-violet-600 text-white',
    };

    $base = 'inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2 focus:ring-offset-slate-900';
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => "$base $classes"]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => "$base $classes"]) }}>{{ $slot }}</button>
@endif