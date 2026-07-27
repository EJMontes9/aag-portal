@props(['grupos'])
{{-- Fragmento del listado de documentos de UN mes. Lo pinta directamente el
     navegador (TransparencyController@documentos) cuando el visitante abre
     ese mes; no forma parte del HTML inicial de /transparencia. --}}
@if($grupos->isEmpty())
    <div class="border-t border-border px-4 py-3.5 text-[14px] text-muted">
        No hay documentos publicados para este mes.
    </div>
@else
    <div class="border-t border-border">
        @foreach($grupos as $grupo)
            @if($grupo['literal'])
                <p class="bg-bg px-4 py-2 border-b border-border font-sans text-[12px] font-bold text-brand-navy leading-snug">
                    {{ $grupo['literal'] }}
                </p>
            @endif

            <ul>
                @foreach($grupo['documents'] as $doc)
                    <li class="border-b border-border last:border-0">
                        <a href="{{ $doc['url'] }}"
                           target="_blank" rel="noopener"
                           @unless($doc['is_external']) download @endunless
                           class="group flex items-center gap-3 px-4 py-2.5 transition-colors hover:bg-brand-soft/40 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-brand-primary"
                           aria-label="{{ $doc['title'] }} ({{ strtoupper($doc['extension']) }})">

                            <span class="inline-flex w-7 h-7 shrink-0 items-center justify-center rounded-pill bg-brand-soft text-brand-navy" aria-hidden="true">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                                </svg>
                            </span>

                            <span class="flex-1 min-w-0">
                                <span class="block font-sans text-[14px] font-semibold text-fg leading-snug transition-colors group-hover:text-brand-primary">
                                    {{ $doc['title'] }}
                                </span>
                                <span class="block text-[12px] text-muted mt-0.5 num-tabular">
                                    {{ strtoupper($doc['extension']) }}
                                    @if($doc['size_human']) · {{ $doc['size_human'] }} @endif
                                </span>
                            </span>

                            <span class="shrink-0 inline-flex items-center justify-center w-8 h-8 rounded-pill bg-brand-navy text-on-navy transition-colors group-hover:bg-brand-primary" aria-hidden="true">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                                </svg>
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endforeach
    </div>
@endif
