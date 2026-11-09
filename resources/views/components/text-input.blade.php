@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'mt-1 block w-full']) }} style="background: var(--surface-input); border: 1px solid var(--border-input); border-radius: var(--radius-input); padding: 8px 12px; font: 400 15px/1.4 Verdana, sans-serif; color: var(--text-on-light);">
