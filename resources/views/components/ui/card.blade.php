@props([
    'tone' => 'surface',
    'padding' => 'md',
    'hover' => false,
])

@php
    $toneClass = match($tone) {
        'navy' => 'bg-brand-navy text-on-navy rounded-card',
        'primary' => 'bg-brand-primary text-on-primary rounded-card',
        'soft' => 'bg-brand-soft/50 rounded-card',
        'hero' => 'card-surface rounded-hero',
        default => 'card-surface',
    };

    $paddingClass = match($padding) {
        'sm' => 'p-4',
        'lg' => 'p-8',
        'xl' => 'p-10',
        'none' => '',
        default => 'p-6',
    };

    // B es un diseño estático: el hover solo cambia el color del borde, nunca desplaza
    // la caja ni saca una sombra.
    $hoverClass = $hover ? 'transition-colors hover:border-brand-primary' : '';
@endphp

<div {{ $attributes->merge(['class' => trim("$toneClass $paddingClass $hoverClass")]) }}>
    {{ $slot }}
</div>
