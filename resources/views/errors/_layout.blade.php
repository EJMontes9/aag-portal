{{--
    Cuerpo compartido de las páginas de error que SÍ pueden usar el layout del
    sitio (404, 403, 419). Se renderizan con cabecera y pie completos a
    propósito: quien llega aquí necesita poder seguir navegando, y perder el
    menú justo cuando te has perdido es el peor momento para hacerlo.

    Los errores 500 y 503 NO usan esto: si lo que ha fallado es la base de
    datos, el layout —que lee settings() y los menús— reventaría también y
    Laravel acabaría mostrando su pantalla de error por defecto. Esos dos son
    páginas autónomas sin ninguna dependencia.

    Parámetros:
      $code      código grande (404, 403...)
      $titulo    titular
      $mensaje   explicación en una o dos frases
      $sugerencias  (opcional) array de motivos posibles
--}}
@extends('layouts.app', ['title' => $titulo])

@section('content')
    <x-ui.breadcrumb-bar :items="[['label' => 'Error ' . $code, 'url' => null]]" />

    <section class="bg-bg">
        <div class="section-wrap">
            <div class="max-w-5xl mx-auto card-surface overflow-hidden">

                {{-- Cabecera navy con el código, cerrada por el filete amarillo --}}
                <div class="bg-brand-navy rule-accent px-6 md:px-10 py-8 md:py-10">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-5 md:gap-8">
                        <span class="font-serif text-[64px] md:text-[80px] leading-none text-brand-accent num-tabular shrink-0">
                            {{ $code }}
                        </span>
                        <div class="min-w-0">
                            <h1 class="font-serif text-section-title text-on-navy">{{ $titulo }}</h1>
                            <p class="mt-2.5 text-[15px] text-on-navy/80 leading-relaxed max-w-[60ch]">
                                {{ $mensaje }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Motivos probables --}}
                @if(!empty($sugerencias))
                <div class="px-6 md:px-10 py-6 border-b border-border">
                    <p class="font-sans text-[11px] tracking-[0.14em] uppercase text-muted font-bold mb-3">
                        POSIBLES MOTIVOS
                    </p>
                    <ul class="grid sm:grid-cols-2 gap-x-8 gap-y-2">
                        @foreach($sugerencias as $s)
                        <li class="flex items-start gap-2.5 text-[14px] text-fg leading-snug">
                            <span class="w-1.5 h-1.5 bg-brand-accent shrink-0 mt-[0.45em]" aria-hidden="true"></span>
                            {{ $s }}
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif

                {{-- Buscador: la salida más útil cuando el contenido se ha movido --}}
                <div class="px-6 md:px-10 py-6 border-b border-border">
                    <form action="{{ route('news.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
                        <label for="error-q" class="sr-only">Buscar en el portal</label>
                        <input type="search" id="error-q" name="q" placeholder="Buscar en noticias y publicaciones"
                               class="flex-1 rounded-pill border border-border bg-card px-4 py-3 text-[15px] text-fg placeholder:text-muted focus:outline-none focus:ring-2 focus:ring-brand-primary focus:border-brand-primary">
                        <button type="submit" class="btn-primary shrink-0">Buscar</button>
                    </form>
                </div>

                {{-- Secciones principales --}}
                <div class="px-6 md:px-10 py-6">
                    <p class="font-sans text-[11px] tracking-[0.14em] uppercase text-muted font-bold mb-3">
                        SECCIONES DEL PORTAL
                    </p>
                    @php
                        $destinos = [
                            ['label' => 'Inicio',         'url' => url('/')],
                            ['label' => 'Noticias',       'url' => route('news.index')],
                            ['label' => 'Proyectos',      'url' => route('projects.index')],
                            ['label' => 'Convocatorias',  'url' => route('convocatorias.index')],
                            ['label' => 'Preguntas frecuentes', 'url' => route('faq.index')],
                        ];
                    @endphp
                    <ul class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($destinos as $d)
                        <li>
                            <a href="{{ $d['url'] }}"
                               class="group flex items-center justify-between gap-3 card-surface px-4 py-3 transition-colors hover:border-brand-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:ring-offset-2 focus-visible:ring-offset-bg">
                                <span class="text-[15px] font-semibold text-brand-navy transition-colors group-hover:text-brand-primary">
                                    {{ $d['label'] }}
                                </span>
                                <svg class="w-4 h-4 text-muted shrink-0 transition-colors group-hover:text-brand-primary"
                                     fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                                </svg>
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Pie: contacto, por si la persona sigue sin encontrar lo suyo --}}
                <div class="border-t border-border bg-bg px-6 md:px-10 py-5 flex flex-wrap items-center justify-between gap-4">
                    <p class="text-[14px] text-muted">
                        ¿No encuentras lo que buscas?
                        @if(settings('contact_email'))
                            Escríbenos a
                            <a href="mailto:{{ settings('contact_email') }}"
                               class="rounded-pill font-semibold text-brand-primary hover:text-brand-navy transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary">{{ settings('contact_email') }}</a>.
                        @endif
                    </p>
                    <a href="{{ url('/') }}" class="btn-ghost shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
                        </svg>
                        Volver al inicio
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection
