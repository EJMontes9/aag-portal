{{-- Paginación en lenguaje Propuesta B.

     Sustituye a la vista por defecto de Laravel, que trae rounded-md y
     shadow-sm: dos rasgos que delatan de inmediato el diseño anterior. Aquí
     los enlaces son rectángulos de 2px con borde marcado, la página activa va
     en navy sólido y no hay ninguna sombra. --}}
@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Paginación" class="flex items-center justify-between gap-4">
        {{-- Móvil: solo anterior / siguiente --}}
        <div class="flex flex-1 justify-between sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="btn-ghost opacity-40 cursor-default">{!! __('pagination.previous') !!}</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" wire:navigate class="btn-ghost">{!! __('pagination.previous') !!}</a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" wire:navigate class="btn-ghost">{!! __('pagination.next') !!}</a>
            @else
                <span class="btn-ghost opacity-40 cursor-default">{!! __('pagination.next') !!}</span>
            @endif
        </div>

        {{-- Escritorio: rango + números --}}
        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between gap-4">
            <p class="text-[11px] text-muted">
                {!! __('Mostrando') !!}
                <span class="font-bold text-brand-navy num-tabular">{{ $paginator->firstItem() }}</span>
                &ndash;
                <span class="font-bold text-brand-navy num-tabular">{{ $paginator->lastItem() }}</span>
                {!! __('de') !!}
                <span class="font-bold text-brand-navy num-tabular">{{ $paginator->total() }}</span>
            </p>

            <ol class="flex items-center gap-1.5">
                {{-- Anterior --}}
                <li>
                    @if ($paginator->onFirstPage())
                        <span aria-disabled="true" class="flex items-center justify-center w-9 h-9 rounded-pill border border-border bg-card text-border cursor-default" aria-label="{{ __('pagination.previous') }}">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" wire:navigate
                           class="flex items-center justify-center w-9 h-9 rounded-pill border border-border bg-card text-brand-navy transition-colors hover:border-brand-primary hover:text-brand-primary"
                           aria-label="{{ __('pagination.previous') }}">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
                        </a>
                    @endif
                </li>

                {{-- Números --}}
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <li><span aria-disabled="true" class="px-2 text-[11px] text-muted">{{ $element }}</span></li>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            <li>
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page" class="flex items-center justify-center min-w-9 h-9 px-2.5 rounded-pill bg-brand-navy text-on-navy text-xs font-bold num-tabular">{{ $page }}</span>
                                @else
                                    <a href="{{ $url }}" wire:navigate
                                       class="flex items-center justify-center min-w-9 h-9 px-2.5 rounded-pill border border-border bg-card text-brand-navy text-xs font-bold num-tabular transition-colors hover:border-brand-primary hover:text-brand-primary"
                                       aria-label="{{ __('Ir a la página :page', ['page' => $page]) }}">{{ $page }}</a>
                                @endif
                            </li>
                        @endforeach
                    @endif
                @endforeach

                {{-- Siguiente --}}
                <li>
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" rel="next" wire:navigate
                           class="flex items-center justify-center w-9 h-9 rounded-pill border border-border bg-card text-brand-navy transition-colors hover:border-brand-primary hover:text-brand-primary"
                           aria-label="{{ __('pagination.next') }}">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                        </a>
                    @else
                        <span aria-disabled="true" class="flex items-center justify-center w-9 h-9 rounded-pill border border-border bg-card text-border cursor-default" aria-label="{{ __('pagination.next') }}">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                        </span>
                    @endif
                </li>
            </ol>
        </div>
    </nav>
@endif
