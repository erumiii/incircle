<button {{ $attributes->merge(['type' => 'submit', 'class' => 'pill pill--decline']) }}>
    {{ $slot }}
</button>
