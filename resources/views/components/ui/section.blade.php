@props([
    'tone' => 'light',
    'id' => null,
])

@php
    $toneClass = match($tone) {
        'navy' => 'bg-brand-navy text-on-navy',
        'soft' => 'bg-brand-soft/40',
        'muted' => 'bg-card',
        default => 'bg-bg',
    };
@endphp

<section @if($id) id="{{ $id }}" @endif {{ $attributes->merge(['class' => $toneClass]) }}>
    <div class="section-wrap">
        {{ $slot }}
    </div>
</section>
