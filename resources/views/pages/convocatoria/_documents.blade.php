@php
    use Illuminate\Support\Facades\Storage;
@endphp
{{-- Caja de documentos del proceso.

     NOTA: se usa solo $info['label'] (PDF, Word, XLS...) y NO $info['bg'] /
     $info['text']. Esas dos claves devuelven clases Tailwind escritas dentro de
     App\Models\Convocatoria::fileTypeInfo(), y app/Models NO esta en el "content"
     de tailwind.config.js: nunca se compilan y el badge sale sin estilo. Ademas
     la paleta pastel por extension (rojo/azul/verde/morado) no pertenece a la
     Propuesta B, donde el badge va en navy sobre el tinte celeste. --}}
<div class="card-surface overflow-hidden">
    {{-- Header de la sección --}}
    <div class="px-4 py-3 border-b border-border bg-brand-soft/25">
        <p class="text-[13px] font-bold text-brand-navy">Documentos del proceso</p>
        <p class="text-[11px] text-muted">{{ $docTotal }} archivo{{ $docTotal !== 1 ? 's' : '' }} disponible{{ $docTotal !== 1 ? 's' : '' }}</p>
    </div>

    {{-- Lista de documentos --}}
    <div class="divide-y divide-border">

        {{-- Bases PDF (campo legacy) --}}
        @if($conv->bases_pdf)
        @php $info = \App\Models\Convocatoria::fileTypeInfo($conv->bases_pdf); @endphp
        <div class="flex items-center gap-3 px-4 py-3 transition-colors hover:bg-brand-soft/15">
            <span class="w-10 h-10 shrink-0 flex items-center justify-center rounded-pill border border-border bg-brand-soft text-[10px] font-bold uppercase text-brand-navy">
                {{ $info['label'] }}
            </span>
            <div class="flex-1 min-w-0">
                <p class="text-[13px] font-semibold text-fg truncate">Bases del proceso</p>
                <p class="text-[11px] text-muted">{{ strtoupper(pathinfo($conv->bases_pdf, PATHINFO_EXTENSION)) }}</p>
            </div>
            <a href="{{ Storage::disk('public')->url($conv->bases_pdf) }}"
               target="_blank"
               download
               class="shrink-0 inline-flex items-center gap-1.5 rounded-pill bg-brand-navy px-3 py-2 text-[11px] font-bold uppercase tracking-[0.07em] text-on-navy transition-colors hover:bg-brand-primary">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                </svg>
                Descargar
            </a>
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
        @endphp
        @if($archivo)
        <div class="flex items-center gap-3 px-4 py-3 transition-colors hover:bg-brand-soft/15">
            <span class="w-10 h-10 shrink-0 flex items-center justify-center rounded-pill border border-border bg-brand-soft text-[10px] font-bold uppercase text-brand-navy">
                {{ $info['label'] }}
            </span>
            <div class="flex-1 min-w-0">
                <p class="text-[13px] font-semibold text-fg truncate" title="{{ $nombre }}">{{ $nombre }}</p>
                <p class="text-[11px] text-muted">{{ $ext }}</p>
            </div>
            <a href="{{ $url }}"
               target="_blank"
               download
               class="shrink-0 inline-flex items-center gap-1.5 rounded-pill bg-brand-navy px-3 py-2 text-[11px] font-bold uppercase tracking-[0.07em] text-on-navy transition-colors hover:bg-brand-primary">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                </svg>
                Descargar
            </a>
        </div>
        @endif
        @endforeach

        {{-- Estado vacío --}}
        @if($docTotal === 0)
        <div class="px-4 py-8 text-center text-[13px] text-muted">
            <svg class="w-8 h-8 mx-auto mb-2 text-muted/40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25"/>
            </svg>
            Aún no hay documentos publicados.
        </div>
        @endif
    </div>
</div>
