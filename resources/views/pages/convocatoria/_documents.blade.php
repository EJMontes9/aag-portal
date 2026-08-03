@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Js;
@endphp
{{-- Caja de documentos del proceso.

     NOTA: se usa solo $info['label'] (PDF, Word, XLS...) y NO $info['bg'] /
     $info['text']. Esas dos claves devuelven clases Tailwind escritas dentro de
     App\Models\Convocatoria::fileTypeInfo(), y app/Models NO está en el "content"
     de tailwind.config.js: nunca se compilan y el badge sale sin estilo. Además
     la paleta pastel por extensión (rojo/azul/verde/morado) no pertenece a la
     Propuesta B, donde el badge va en navy sobre el tinte celeste. --}}

{{-- El x-data envuelve la tarjeta en vez de ir sobre ella porque el modal es
     hermano de la caja: dentro heredaría su overflow-hidden. x-id genera ids
     únicos, necesario porque esta vista se incluye dos veces en la ficha (una
     para mobile y otra para el sidebar de desktop). --}}
<div x-data="pdfPreview()" x-id="['pdf-titulo']">
<div class="card-surface overflow-hidden">
    {{-- Header de la sección --}}
    <div class="px-4 py-3.5 border-b border-border bg-brand-soft/25">
        <p class="text-[15px] font-bold uppercase tracking-[0.04em] text-brand-navy">Documentos del proceso</p>
        <p class="mt-0.5 text-[12px] text-muted num-tabular">{{ $docTotal }} archivo{{ $docTotal !== 1 ? 's' : '' }} disponible{{ $docTotal !== 1 ? 's' : '' }}</p>
    </div>

    {{-- Lista de documentos --}}
    <div class="divide-y divide-border">

        {{-- Bases PDF (campo legacy) --}}
        @if($conv->bases_pdf)
        @php
            $info      = \App\Models\Convocatoria::fileTypeInfo($conv->bases_pdf);
            $basesUrl  = Storage::disk('public')->url($conv->bases_pdf);
            // Solo el PDF se puede previsualizar: el visor nativo del navegador
            // no abre Word, Excel ni ZIP, así que para el resto se deja únicamente
            // la descarga en vez de un botón que abriría un iframe en blanco.
            $basesEsPdf = strtolower(pathinfo($conv->bases_pdf, PATHINFO_EXTENSION)) === 'pdf';
        @endphp
        {{-- flex-wrap + min-w en el bloque de texto: a 360px el badge, el nombre y
             el botón no caben en una línea, así que el botón baja a la segunda
             en vez de aplastar el nombre del archivo a tres caracteres. --}}
        <div class="flex flex-wrap items-center gap-3 px-4 py-3.5 transition-colors hover:bg-brand-soft/15">
            <span class="w-10 h-10 shrink-0 flex items-center justify-center rounded-pill border border-border bg-brand-soft text-[11px] font-bold uppercase text-brand-navy">
                {{ $info['label'] }}
            </span>
            <div class="flex-1 min-w-[7rem]">
                <p class="text-[15px] font-semibold text-fg truncate">Bases del proceso</p>
                <p class="mt-0.5 text-[12px] uppercase tracking-[0.06em] text-muted">{{ strtoupper(pathinfo($conv->bases_pdf, PATHINFO_EXTENSION)) }}</p>
            </div>
            <div class="shrink-0 w-full sm:w-auto flex items-center gap-2">
                @if($basesEsPdf)
                <button type="button"
                        @click="ver({{ Js::from($basesUrl) }}, {{ Js::from('Bases del proceso') }})"
                        aria-label="Previsualizar bases del proceso"
                        class="flex-1 sm:flex-none inline-flex items-center justify-center gap-1.5 rounded-pill border border-border bg-card px-3.5 py-2.5 text-[12px] font-bold uppercase tracking-[0.07em] text-brand-navy transition-colors hover:border-brand-primary hover:text-brand-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:ring-offset-2 focus-visible:ring-offset-card">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                    </svg>
                    Ver
                </button>
                @endif
                <a href="{{ $basesUrl }}"
                   target="_blank"
                   download
                   aria-label="Descargar bases del proceso"
                   class="flex-1 sm:flex-none inline-flex items-center justify-center gap-1.5 rounded-pill bg-brand-navy px-3.5 py-2.5 text-[12px] font-bold uppercase tracking-[0.07em] text-on-navy transition-colors hover:bg-brand-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:ring-offset-2 focus-visible:ring-offset-card">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                    </svg>
                    Descargar
                </a>
            </div>
        </div>
        @endif

        {{-- Documentos del repeater --}}
        @foreach($documentos as $doc)
        @php
            $archivo = $doc['archivo'] ?? $doc['path'] ?? '';
            $nombre  = $doc['nombre'] ?? basename($archivo);
            $info    = \App\Models\Convocatoria::fileTypeInfo($archivo);
            $url     = $archivo ? Storage::disk('public')->url($archivo) : '#';
            $ext     = strtoupper(pathinfo($archivo, PATHINFO_EXTENSION));
            $esPdf   = $ext === 'PDF';
        @endphp
        @if($archivo)
        <div class="flex flex-wrap items-center gap-3 px-4 py-3.5 transition-colors hover:bg-brand-soft/15">
            <span class="w-10 h-10 shrink-0 flex items-center justify-center rounded-pill border border-border bg-brand-soft text-[11px] font-bold uppercase text-brand-navy">
                {{ $info['label'] }}
            </span>
            <div class="flex-1 min-w-[7rem]">
                <p class="text-[15px] font-semibold text-fg truncate" title="{{ $nombre }}">{{ $nombre }}</p>
                <p class="mt-0.5 text-[12px] uppercase tracking-[0.06em] text-muted">{{ $ext }}</p>
            </div>
            <div class="shrink-0 w-full sm:w-auto flex items-center gap-2">
                @if($esPdf)
                <button type="button"
                        @click="ver({{ Js::from($url) }}, {{ Js::from($nombre) }})"
                        aria-label="Previsualizar {{ $nombre }}"
                        class="flex-1 sm:flex-none inline-flex items-center justify-center gap-1.5 rounded-pill border border-border bg-card px-3.5 py-2.5 text-[12px] font-bold uppercase tracking-[0.07em] text-brand-navy transition-colors hover:border-brand-primary hover:text-brand-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:ring-offset-2 focus-visible:ring-offset-card">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                    </svg>
                    Ver
                </button>
                @endif
                <a href="{{ $url }}"
                   target="_blank"
                   download
                   aria-label="Descargar {{ $nombre }}"
                   class="flex-1 sm:flex-none inline-flex items-center justify-center gap-1.5 rounded-pill bg-brand-navy px-3.5 py-2.5 text-[12px] font-bold uppercase tracking-[0.07em] text-on-navy transition-colors hover:bg-brand-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:ring-offset-2 focus-visible:ring-offset-card">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                    </svg>
                    Descargar
                </a>
            </div>
        </div>
        @endif
        @endforeach

        {{-- Estado vacío --}}
        @if($docTotal === 0)
        <div class="px-5 py-9 text-center text-muted">
            <svg class="w-8 h-8 mx-auto mb-3 text-muted/40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25"/>
            </svg>
            <p class="text-[15px] font-semibold text-fg">Aún no hay documentos publicados</p>
            <p class="mt-1.5 text-[14px] leading-[1.55]">Las bases y anexos se publicarán en esta misma ficha.</p>
        </div>
        @endif
    </div>
</div>

{{-- VISOR DE PDF ---------------------------------------------------------
     Sigue el patrón del modal de components/alerts/convocatoria-alert:
     role/aria-modal/aria-labelledby, cierre con Escape y con clic fuera.
     Añade además el atrapado de foco (@keydown.tab), que allí no hacía falta
     porque aquel modal tiene dos botones y este contiene un iframe. --}}
<div x-show="abierto"
     x-cloak
     x-transition.opacity
     class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4"
     @keydown.escape.window="cerrar()">
    {{-- Misma excepción justificada a la regla de "cero sombras" que el modal
         de alerta: flota sobre el backdrop y necesita despegarse del fondo. --}}
    <div x-show="abierto"
         x-transition
         x-ref="panel"
         role="dialog"
         aria-modal="true"
         :aria-labelledby="$id('pdf-titulo')"
         @keydown.tab="atrapar($event)"
         @click.outside="cerrar()"
         class="bg-card rounded-card border border-border w-full max-w-4xl h-[85vh] flex flex-col shadow-lg">

        <div class="flex items-center justify-between gap-4 px-5 py-3.5 border-b border-border bg-brand-soft/25 shrink-0">
            <div class="min-w-0">
                <p class="text-[12px] font-bold uppercase tracking-[0.07em] text-muted">Previsualización</p>
                <h3 :id="$id('pdf-titulo')" class="font-serif text-[18px] leading-[1.25] text-brand-navy truncate" x-text="nombre"></h3>
            </div>
            <button type="button" @click="cerrar()" aria-label="Cerrar previsualización"
                    class="shrink-0 rounded-pill text-muted hover:text-fg transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:ring-offset-2 focus-visible:ring-offset-card">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- El iframe se pinta solo con el modal abierto (x-if via x-show del
             padre no bastaría: el navegador descargaría el PDF igualmente al
             cargar la página). El src se enlaza para que solo apunte al
             documento elegido. --}}
        <div class="flex-1 min-h-0 bg-bg">
            <template x-if="abierto && url">
                <iframe :src="url" :title="'Documento: ' + nombre" class="w-full h-full border-0"></iframe>
            </template>
        </div>

        {{-- La descarga se repite dentro del visor: quien abre el PDF para
             ojearlo suele querer guardarlo sin volver a la lista. --}}
        <div class="flex items-center justify-end gap-3 px-5 py-3.5 border-t border-border shrink-0">
            <a :href="url" target="_blank" download
               class="inline-flex items-center gap-1.5 rounded-pill bg-brand-navy px-3.5 py-2.5 text-[12px] font-bold uppercase tracking-[0.07em] text-on-navy transition-colors hover:bg-brand-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:ring-offset-2 focus-visible:ring-offset-card">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                </svg>
                Descargar
            </a>
            <button type="button" @click="cerrar()" class="btn-ghost">Cerrar</button>
        </div>
    </div>
</div>
</div>
