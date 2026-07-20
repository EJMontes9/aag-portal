@props([
    'value' => '',
    'label' => '',
])

<div class="flex flex-col gap-1.5">
    <span class="font-serif text-[38px] md:text-[44px] font-normal text-fg leading-none tracking-[-0.02em]">
        {{ $value }}
    </span>
    {{-- El rotulo de la cifra sube de 10 a 12px: en mayusculas, condensada y con
         tracking amplio, 10px queda por debajo del umbral de lectura comoda. --}}
    <span class="font-sans text-[12px] tracking-[0.18em] uppercase text-muted font-semibold leading-tight">
        {{ $label }}
    </span>
</div>
