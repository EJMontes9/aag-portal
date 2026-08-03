@props([
    'variant' => 'primary',
    'href' => null,
    'target' => null,
])

@php
    $classes = match($variant) {
        'ghost' => 'btn-ghost',
        // Sin desplazamiento en hover (B es estático) y sobre el amarillo va el
        // token on-accent, no texto blanco.
        'accent' => 'btn-white',
        'on-navy' => 'btn-base bg-card text-brand-navy hover:bg-brand-accent hover:text-on-accent',
        default => 'btn-primary',
    };
@endphp

@if($href)
    <a href="{{ $href }}"
       @if($target) target="{{ $target }}" @endif
       @if(is_internal_link($href, $target)) wire:navigate @endif
       {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['class' => $classes, 'type' => 'button']) }}>
        {{ $slot }}
    </button>
@endif
