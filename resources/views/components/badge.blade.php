@props(['color' => '#6b7280'])

@php
    $bg = $color . '22';
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center px-2 py-0.5 rounded text-xs font-medium']) }}
      style="background-color: {{ $bg }}; color: {{ $color }}; border: 1px solid {{ $color }}33">
    {{ $slot }}
</span>