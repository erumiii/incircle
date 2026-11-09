@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-semibold text-sm']) }} style="color: var(--text-on-light);">
    {{ $value ?? $slot }}
</label>
