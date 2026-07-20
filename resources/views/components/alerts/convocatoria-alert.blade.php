@php
    $conv = \App\Models\Convocatoria::featured();
    if (! $conv || ! $conv->isAlertActive()) return;
@endphp

<div x-data="convocatoriaAlert({{ $conv->id }}, '{{ $conv->alert_mode }}', '{{ $conv->alert_frequency }}')" x-cloak>
    {{-- MODAL --}}
    @if($conv->alert_mode === 'modal')
        <div x-show="show" x-transition.opacity class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4" @keydown.escape.window="close()">
            {{-- Excepcion justificada a la regla de "cero sombras": el modal flota sobre el
                 backdrop y necesita despegarse del contenido. Se baja de shadow-2xl a una
                 sombra discreta y se refuerza con el borde marcado propio de B. --}}
            {{-- role/aria-modal + aria-labelledby: sin ellos el lector de pantalla
                 anuncia el modal como un div suelto y no lee su titulo. --}}
            <div x-show="show" x-transition role="dialog" aria-modal="true" aria-labelledby="conv-alert-title"
                 class="bg-card rounded-card border border-border max-w-xl w-full p-6 sm:p-8 shadow-lg" @click.outside="close()">
                <div class="flex items-start justify-between gap-4 mb-5">
                    <span class="pill bg-brand-primary text-on-primary">CONVOCATORIA ABIERTA</span>
                    <button @click="close()" class="rounded-pill text-muted hover:text-fg transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:ring-offset-2 focus-visible:ring-offset-card" aria-label="Cerrar">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <h3 id="conv-alert-title" class="font-serif text-2xl uppercase text-brand-navy leading-tight">{{ $conv->title }}</h3>
                @if($conv->short_description)
                    <p class="text-[15px] text-muted mt-3 leading-[1.6]">{{ $conv->short_description }}</p>
                @endif
                @if($conv->closes_at)
                    {{-- num-tabular en vez de font-mono: la mono (JetBrains) es una
                         familia ajena al manual; esto alinea las cifras sin salir
                         de Barlow. --}}
                    <p class="text-[13px] text-muted mt-3 num-tabular">Cierre: {{ $conv->closes_at->translatedFormat('d \\d\\e F · H:i') }}</p>
                @endif
                <div class="mt-7 flex flex-wrap gap-3">
                    <a href="/convocatorias/{{ $conv->slug }}" class="btn-primary">Ver detalles</a>
                    <button @click="close()" class="btn-ghost">Mas tarde</button>
                </div>
            </div>
        </div>
    @endif

    {{-- TOAST --}}
    @if($conv->alert_mode === 'toast')
        {{-- max-w-sm (408px) desbordaba a 360px: por debajo de sm el toast se
             ancla a los dos lados y ocupa el ancho disponible menos el margen. --}}
        <div x-show="show" x-transition class="fixed bottom-4 left-4 right-4 sm:left-auto sm:bottom-6 sm:right-6 z-40 sm:max-w-sm">
            {{-- Excepcion justificada: el toast flota sobre la pagina, sin sombra se
                 confunde con el contenido. Sombra minima + borde de B. --}}
            <div class="bg-card rounded-card border border-border shadow-md p-4 flex gap-3">
                <div class="w-10 h-10 rounded-card bg-brand-soft flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-brand-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.34 3.94c.36-1.25 2.14-1.25 2.5 0l.72 2.5a1.3 1.3 0 0 0 1.25.91h2.62c1.3 0 1.84 1.67.79 2.43l-2.12 1.54a1.3 1.3 0 0 0-.47 1.45l.81 2.5c.4 1.24-1.02 2.27-2.07 1.5L12 15.28l-2.37 1.48c-1.05.77-2.47-.26-2.07-1.5l.81-2.5a1.3 1.3 0 0 0-.47-1.45L5.78 9.77c-1.05-.76-.51-2.43.79-2.43h2.62a1.3 1.3 0 0 0 1.25-.91l.72-2.5Z"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-[12px] tracking-[0.12em] uppercase text-muted font-bold">CONVOCATORIA</p>
                    <h4 class="font-sans font-semibold text-[15px] text-fg mt-1 leading-[1.3]">{{ $conv->title }}</h4>
                    <a href="/convocatorias/{{ $conv->slug }}"
                       class="rounded-pill text-[13px] font-bold uppercase tracking-[0.07em] text-brand-primary hover:underline mt-2.5 inline-block focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:ring-offset-2 focus-visible:ring-offset-card">Ver detalles &rsaquo;</a>
                </div>
                <button @click="close()" class="rounded-pill shrink-0 text-muted hover:text-fg transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:ring-offset-2 focus-visible:ring-offset-card" aria-label="Cerrar">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
    @endif

    {{-- BANNER --}}
    @if($conv->alert_mode === 'banner')
        <div x-show="show" x-transition class="bg-brand-primary text-on-primary">
            {{-- A 360px los cuatro elementos no caben: el chip se oculta (es
                 redundante con el enlace) y el titulo cede el ancho, en vez de
                 dejar "Ver detalles" partido o fuera de pantalla. --}}
            <div class="max-w-[1440px] mx-auto px-5 md:px-10 lg:px-14 py-3 flex items-center gap-3 sm:gap-4">
                <span class="pill bg-white/15 text-on-primary border border-white/20 shrink-0 hidden sm:inline-flex">CONVOCATORIA</span>
                <p class="text-[15px] flex-1 min-w-0 truncate">{{ $conv->title }}</p>
                <a href="/convocatorias/{{ $conv->slug }}"
                   class="rounded-pill text-[13px] font-bold uppercase tracking-[0.07em] whitespace-nowrap hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-brand-primary">Ver detalles &rsaquo;</a>
                <button @click="close()" class="rounded-pill shrink-0 text-on-primary/80 hover:text-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-brand-primary" aria-label="Cerrar">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
    @endif
</div>
