@props(['tone' => 'muted'])

@php
    $toneClass = match($tone) {
        'primary' => 'text-brand-primary',
        'accent' => 'text-brand-accent',
        'on-navy' => 'text-brand-accent',
        default => 'text-muted',
    };
@endphp

<span {{ $attributes->merge(['class' => "kicker $toneClass"]) }}>
    {{ $slot }}
</span>
