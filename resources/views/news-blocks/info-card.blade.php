@props(['data'])
@php
    $items = collect($data['items'] ?? [])->filter(fn ($i) => !empty($i['label']) && !empty($i['value']))->values();
    if ($items->isEmpty()) return;
@endphp

{{-- El contenedor pasa de <div> a <dl>: los <dt>/<dd> que ya habia solo tienen
     semantica (y solo los anuncia el lector de pantalla como pares) dentro de
     una lista de definiciones. Los rotulos suben de 10 a 12px, que es el suelo
     legible para mayusculas condensadas con tracking amplio. --}}
<dl class="rounded-card border border-border bg-card p-6 space-y-4">
    @if(!empty($data['kicker']))
        <p class="text-[12px] tracking-[0.16em] uppercase text-muted font-semibold">
            {{ $data['kicker'] }}
        </p>
    @endif

    @foreach($items as $item)
        <div class="space-y-1">
            <dt class="text-[12px] tracking-[0.12em] uppercase text-muted font-semibold">
                {{ $item['label'] }}
            </dt>
            <dd class="text-[16px] font-semibold text-fg leading-snug">
                {{ $item['value'] }}
            </dd>
        </div>
    @endforeach
</dl>
