@props(['data'])
@php
    if (empty($data['file'])) return;
    $url = \Illuminate\Support\Facades\Storage::disk('public')->url($data['file']);
    $ext = strtoupper(pathinfo($data['file'], PATHINFO_EXTENSION));

    // Tamaño del archivo si esta accesible
    $path = \Illuminate\Support\Facades\Storage::disk('public')->path($data['file']);
    $size = file_exists($path) ? filesize($path) : null;
    $sizeHuman = $size ? round($size / 1024 / 1024, 1).' MB' : null;
@endphp
<a href="{{ $url }}"
   target="_blank"
   rel="noopener"
   download
   class="group flex items-center gap-4 p-5 rounded-card border border-border bg-brand-soft/20 hover:bg-brand-soft/40 hover:border-brand-primary transition-colors no-underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:ring-offset-2 focus-visible:ring-offset-card">
    <div class="shrink-0 w-12 h-12 rounded-card bg-brand-primary text-on-primary flex items-center justify-center">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
        </svg>
    </div>
    <div class="flex-1 min-w-0">
        <p class="font-serif text-lg text-brand-navy leading-tight">{{ $data['label'] }}</p>
        @if(!empty($data['description']))
            <p class="text-[15px] text-muted mt-1.5 leading-[1.55]">{{ $data['description'] }}</p>
        @endif
        {{-- Se cambia font-mono (JetBrains, familia ajena al manual) por
             num-tabular: conserva las cifras alineadas sin salirse de Barlow. --}}
        <p class="text-[12px] uppercase tracking-[0.07em] font-bold text-muted mt-2.5 num-tabular">
            {{ $ext }}@if($sizeHuman) · {{ $sizeHuman }} @endif
        </p>
    </div>
    {{-- Sin desplazamiento en hover: B es un diseno estatico, solo cambia el color --}}
    <svg class="w-5 h-5 text-muted shrink-0 group-hover:text-brand-primary transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
    </svg>
</a>
