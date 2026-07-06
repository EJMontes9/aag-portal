@props(['data'])
@php
    $items = collect($data['items'] ?? [])->filter(fn ($i) => !empty($i['label']) && !empty($i['value']))->values();
    if ($items->isEmpty()) return;
@endphp

<div class="rounded-card border border-border bg-card p-6 space-y-4">
    @if(!empty($data['kicker']))
        <p class="text-[10px] tracking-[0.18em] uppercase text-muted font-semibold">
            {{ $data['kicker'] }}
        </p>
    @endif

    @foreach($items as $item)
        <div class="space-y-1">
            <dt class="text-[10px] tracking-[0.14em] uppercase text-muted font-semibold">
                {{ $item['label'] }}
            </dt>
            <dd class="text-base font-semibold text-fg">
                {{ $item['value'] }}
            </dd>
        </div>
    @endforeach
</div>
