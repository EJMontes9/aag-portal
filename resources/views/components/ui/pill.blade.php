@props([
    'tone' => 'neutral',
    'dot' => false,
    'dotTone' => 'emerald',
])

@php
    $toneClass = match($tone) {
        'primary' => 'bg-brand-primary text-on-primary',
        'soft' => 'bg-brand-soft/70 text-brand-navy',
        'on-navy' => 'bg-white/10 text-on-navy border border-white/15',
        'success' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
        'warn' => 'bg-amber-50 text-amber-800 border border-amber-200',
        default => 'bg-card text-fg border border-border',
    };

    $dotClass = match($dotTone) {
        'amber' => 'status-dot-amber',
        'red' => 'status-dot-red',
        default => 'status-dot',
    };
@endphp

<span {{ $attributes->merge(['class' => "pill $toneClass"]) }}>
    @if($dot) <span class="{{ $dotClass }}"></span> @endif
    {{ $slot }}
</span>
