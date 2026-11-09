@props(['messages'])

@if ($messages)
    <ul {{ $attributes->merge(['class' => 'text-sm font-medium space-y-1']) }} style="color: #6E0A0A;">
        @foreach ((array) $messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif
