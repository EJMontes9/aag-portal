@php
    use Illuminate\Support\Facades\Storage;
@endphp
<div class="rounded-2xl border border-border bg-card overflow-hidden shadow-sm">
    {{-- Header de la sección --}}
    <div class="px-5 py-4 border-b border-border flex items-center justify-between"
         style="background:rgb(var(--color-soft)/0.25);">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center"
                 style="background:rgb(var(--color-navy));">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-bold text-fg">Documentos del proceso</p>
                <p class="text-xs text-muted">{{ $docTotal }} archivo{{ $docTotal !== 1 ? 's' : '' }} disponible{{ $docTotal !== 1 ? 's' : '' }}</p>
            </div>
        </div>
    </div>

    {{-- Lista de documentos --}}
    <div class="divide-y divide-border/60">

        {{-- Bases PDF (campo legacy) --}}
        @if($conv->bases_pdf)
        @php $info = \App\Models\Convocatoria::fileTypeInfo($conv->bases_pdf); @endphp
        <div class="flex items-center gap-3 px-5 py-4 hover:bg-brand-soft/10 transition-colors group">
            <span class="w-10 h-10 rounded-xl flex items-center justify-center text-[10px] font-bold border flex-shrink-0 {{ $info['bg'] }} {{ $info['text'] }}">
                {{ $info['label'] }}
            </span>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-fg truncate">Bases del proceso</p>
                <p class="text-xs text-muted">{{ strtoupper(pathinfo($conv->bases_pdf, PATHINFO_EXTENSION)) }}</p>
            </div>
            <a href="{{ Storage::disk('public')->url($conv->bases_pdf) }}"
               target="_blank"
               download
               class="flex-shrink-0 inline-flex items-center gap-1.5 text-xs font-bold px-3.5 py-2 rounded-lg transition-all duration-150 group-hover:shadow-sm"
               style="background:rgb(var(--color-navy));color:#fff;"
               onmouseover="this.style.background='rgb(var(--color-primary))'"
               onmouseout="this.style.background='rgb(var(--color-navy))'">
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
        <div class="flex items-center gap-3 px-5 py-4 hover:bg-brand-soft/10 transition-colors group">
            <span class="w-10 h-10 rounded-xl flex items-center justify-center text-[10px] font-bold border flex-shrink-0 {{ $info['bg'] }} {{ $info['text'] }}">
                {{ $info['label'] }}
            </span>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-fg truncate" title="{{ $nombre }}">{{ $nombre }}</p>
                <p class="text-xs text-muted">{{ $ext }}</p>
            </div>
            <a href="{{ $url }}"
               target="_blank"
               download
               class="flex-shrink-0 inline-flex items-center gap-1.5 text-xs font-bold px-3.5 py-2 rounded-lg transition-all duration-150 group-hover:shadow-sm"
               style="background:rgb(var(--color-navy));color:#fff;"
               onmouseover="this.style.background='rgb(var(--color-primary))'"
               onmouseout="this.style.background='rgb(var(--color-navy))'">
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
        <div class="px-5 py-8 text-center text-muted text-sm">
            <svg class="w-8 h-8 mx-auto mb-2 text-muted/40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25"/>
            </svg>
            Aún no hay documentos publicados.
        </div>
        @endif
    </div>
</div>
