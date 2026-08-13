@props(['variant' => 'primary', 'type' => 'submit'])

<button {{ $attributes->merge(['class' => "btn btn-{$variant}", 'type' => $type]) }}>
    {{ $slot }}
</button>
