@props(['data'])
@php $style = $data['style'] ?? 'line'; @endphp

@if($style === 'line')
    <hr class="border-t border-border max-w-md mx-auto">
@elseif($style === 'dots')
    {{-- Puntos de 6px: se dejan cuadrados, en B no existen circulos --}}
    <div class="flex items-center justify-center gap-2" aria-hidden="true">
        <span class="w-1.5 h-1.5 bg-brand-accent"></span>
        <span class="w-1.5 h-1.5 bg-brand-accent"></span>
        <span class="w-1.5 h-1.5 bg-brand-accent"></span>
    </div>
@else
    <div class="h-8" aria-hidden="true"></div>
@endif
