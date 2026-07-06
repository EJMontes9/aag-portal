@props([
    'variant' => 'primary',
    'href' => null,
    'target' => null,
])

@php
    $classes = match($variant) {
        'ghost' => 'btn-ghost',
        'accent' => 'inline-flex items-center justify-center gap-2 rounded-pill bg-brand-accent text-on-navy px-5 py-2.5 text-sm font-medium transition hover:-translate-y-px',
        'on-navy' => 'inline-flex items-center justify-center gap-2 rounded-pill bg-white text-brand-navy px-5 py-2.5 text-sm font-medium transition hover:-translate-y-px',
        default => 'btn-primary',
    };
@endphp

@if($href)
    <a href="{{ $href }}" @if($target) target="{{ $target }}" @endif {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['class' => $classes, 'type' => 'button']) }}>
        {{ $slot }}
    </button>
@endif
