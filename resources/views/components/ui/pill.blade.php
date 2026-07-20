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
        // Mismos tonos apagados que .chip-abierto / .chip-proceso de app.css
        'success' => 'bg-[#E6F4EA] text-[#1B5E32] border border-[#C3E3CE]',
        'warn' => 'bg-[#FDF3D3] text-[#7A5B00] border border-[#EBD79A]',
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
