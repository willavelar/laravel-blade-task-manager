@props(['label', 'name', 'required' => false])

<div {{ $attributes->merge(['class' => 'space-y-1']) }}>
    <label for="{{ $name }}"
           class="block text-sm font-medium text-slate-300">
        {{ $label }}
        @if($required)
            <span class="text-red-400">*</span>
        @endif
    </label>

    {{ $slot }}

    @error($name)
        <p class="text-xs text-red-400">{{ $message }}</p>
    @enderror
</div>