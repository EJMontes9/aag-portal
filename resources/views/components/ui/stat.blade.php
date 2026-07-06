@props([
    'value' => '',
    'label' => '',
])

<div class="flex flex-col gap-1.5">
    <span class="font-serif text-[38px] md:text-[44px] font-normal text-fg leading-none tracking-[-0.02em]">
        {{ $value }}
    </span>
    <span class="font-sans text-[10px] tracking-[0.18em] uppercase text-muted font-semibold leading-tight">
        {{ $label }}
    </span>
</div>
