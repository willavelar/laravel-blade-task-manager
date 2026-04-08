@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-slate-600 bg-slate-700 text-slate-100 focus:border-violet-500 focus:ring-violet-500 rounded-md shadow-sm placeholder-slate-500']) }}>