<button {{ $attributes->merge(['type' => 'submit', 'class' => 'pill']) }}>
    {{ $slot }}
</button>
