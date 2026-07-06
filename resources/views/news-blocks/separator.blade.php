@props(['data'])
@php $style = $data['style'] ?? 'line'; @endphp

@if($style === 'line')
    <hr class="border-t border-border max-w-md mx-auto">
@elseif($style === 'dots')
    <div class="flex items-center justify-center gap-2" aria-hidden="true">
        <span class="w-1.5 h-1.5 rounded-full bg-brand-accent/60"></span>
        <span class="w-1.5 h-1.5 rounded-full bg-brand-accent/60"></span>
        <span class="w-1.5 h-1.5 rounded-full bg-brand-accent/60"></span>
    </div>
@else
    <div class="h-8" aria-hidden="true"></div>
@endif
