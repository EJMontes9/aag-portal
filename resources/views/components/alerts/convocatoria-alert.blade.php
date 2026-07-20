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
            <div x-show="show" x-transition class="bg-card rounded-card border border-border max-w-xl w-full p-8 shadow-lg" @click.outside="close()">
                <div class="flex items-start justify-between gap-4 mb-5">
                    <span class="pill bg-brand-primary text-on-primary">CONVOCATORIA ABIERTA</span>
                    <button @click="close()" class="text-muted hover:text-fg" aria-label="Cerrar">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <h3 class="font-serif text-2xl text-fg">{{ $conv->title }}</h3>
                @if($conv->short_description)
                    <p class="text-sm text-muted mt-3 leading-relaxed">{{ $conv->short_description }}</p>
                @endif
                @if($conv->closes_at)
                    <p class="text-xs text-muted mt-3 font-mono">Cierre: {{ $conv->closes_at->translatedFormat('d \\d\\e F · H:i') }}</p>
                @endif
                <div class="mt-6 flex gap-3">
                    <a href="/convocatorias/{{ $conv->slug }}" class="btn-primary">Ver detalles</a>
                    <button @click="close()" class="btn-ghost">Mas tarde</button>
                </div>
            </div>
        </div>
    @endif

    {{-- TOAST --}}
    @if($conv->alert_mode === 'toast')
        <div x-show="show" x-transition class="fixed bottom-6 right-6 z-40 max-w-sm">
            {{-- Excepcion justificada: el toast flota sobre la pagina, sin sombra se
                 confunde con el contenido. Sombra minima + borde de B. --}}
            <div class="bg-card rounded-card border border-border shadow-md p-4 flex gap-3">
                <div class="w-10 h-10 rounded-card bg-brand-soft flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-brand-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.34 3.94c.36-1.25 2.14-1.25 2.5 0l.72 2.5a1.3 1.3 0 0 0 1.25.91h2.62c1.3 0 1.84 1.67.79 2.43l-2.12 1.54a1.3 1.3 0 0 0-.47 1.45l.81 2.5c.4 1.24-1.02 2.27-2.07 1.5L12 15.28l-2.37 1.48c-1.05.77-2.47-.26-2.07-1.5l.81-2.5a1.3 1.3 0 0 0-.47-1.45L5.78 9.77c-1.05-.76-.51-2.43.79-2.43h2.62a1.3 1.3 0 0 0 1.25-.91l.72-2.5Z"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-[10px] tracking-[0.14em] uppercase text-muted font-semibold">CONVOCATORIA</p>
                    <h4 class="font-sans font-semibold text-sm text-fg mt-1 leading-tight">{{ $conv->title }}</h4>
                    <a href="/convocatorias/{{ $conv->slug }}" class="text-xs font-medium text-brand-primary hover:underline mt-2 inline-block">Ver detalles →</a>
                </div>
                <button @click="close()" class="text-muted hover:text-fg shrink-0" aria-label="Cerrar">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
    @endif

    {{-- BANNER --}}
    @if($conv->alert_mode === 'banner')
        <div x-show="show" x-transition class="bg-brand-primary text-on-primary">
            <div class="max-w-[1440px] mx-auto px-5 md:px-10 lg:px-14 py-2.5 flex items-center gap-4">
                <span class="pill bg-white/15 text-on-primary border border-white/20 shrink-0">CONVOCATORIA</span>
                <p class="text-sm flex-1 truncate">{{ $conv->title }}</p>
                <a href="/convocatorias/{{ $conv->slug }}" class="text-sm font-semibold whitespace-nowrap hover:underline">Ver detalles →</a>
                <button @click="close()" class="text-on-primary/80 hover:text-white shrink-0" aria-label="Cerrar">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
    @endif
</div>
