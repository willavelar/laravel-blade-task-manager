@props(['priority'])

@php
    $config = match($priority) {
        'high'   => ['label' => 'Alta',  'class' => 'bg-red-900/40 text-red-400 border border-red-800'],
        'medium' => ['label' => 'Média', 'class' => 'bg-yellow-900/40 text-yellow-400 border border-yellow-800'],
        'low'    => ['label' => 'Baixa', 'class' => 'bg-green-900/40 text-green-400 border border-green-800'],
        default  => ['label' => $priority, 'class' => 'bg-slate-700 text-slate-400'],
    };
@endphp

<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $config['class'] }}">
    {{ $config['label'] }}
</span>