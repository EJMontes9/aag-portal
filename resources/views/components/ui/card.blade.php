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

    $hoverClass = $hover ? 'transition hover:-translate-y-1 hover:shadow-lg' : '';
@endphp

<div {{ $attributes->merge(['class' => trim("$toneClass $paddingClass $hoverClass")]) }}>
    {{ $slot }}
</div>
