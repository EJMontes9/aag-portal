{{--
    Header institucional AAG — Propuesta B.

    Cuatro franjas apiladas, que es el rasgo mas reconocible de B:
      1. Utilidades institucionales sobre AMARILLO  (32px)
      2. Contacto + redes + reloj sobre NAVY        (36px)
      3. Marca + CTA sobre BLANCO, filete amarillo  (90px)
      4. Navegacion principal a lo ancho sobre NAVY (48px)

    IMPORTANTE — La maqueta original de la Propuesta B trae en la franja 1 la
    leyenda "Gobierno del Encuentro · Republica del Ecuador". NO se replica a
    proposito: la AAG es una fundacion municipal (Alcaldia de Guayaquil) y no
    una entidad del Gobierno Nacional, asi que no puede exhibir su marca. Esa
    franja se usa aqui para los enlaces del menu "topbar", que es el uso que le
    da la maqueta en su lado derecho (AYUDA / MAPA DEL SITIO / TRANSPARENCIA).

    Las alturas fijas de B (32/36/90/48) se conservan en escritorio; por debajo
    de lg las franjas 1, 2 y 4 se ocultan o colapsan en el menu movil, porque
    las maquetas no definen responsive (no tienen una sola media query).
--}}
@php
    $topbarEnabled = (bool) settings('topbar_enabled', true);
    $headerMenu = \App\Models\Menu::byLocation('header');
    $topbarMenu = \App\Models\Menu::byLocation('topbar');
    $ctaEnabled = (bool) settings('header_cta_enabled', true);
    $darkAllowed = (bool) settings('dark_mode_enabled', true);
    $showClock = (bool) settings('header_show_clock', true);

    $socialLinks = array_filter([
        'facebook' => settings('social_facebook'),
        'twitter' => settings('social_twitter'),
        'instagram' => settings('social_instagram'),
        'youtube' => settings('social_youtube'),
    ]);

    $topLevelItems = $headerMenu
        ? $headerMenu->items->where('is_active', true)->sortBy('sort_order')->values()
        : collect();

    $topbarItems = $topbarMenu
        ? $topbarMenu->items->where('is_active', true)->sortBy('sort_order')->values()
        : collect();

    $childrenOf = fn ($parent) => $parent->children->where('is_active', true)->sortBy('sort_order')->values();

    $isActiveItem = function ($item) {
        $path = parse_url($item->url ?? '', PHP_URL_PATH);
        if (! $path || $path === '#') return false;
        return request()->is(trim($path, '/') === '' ? '/' : trim($path, '/'));
    };
@endphp

{{-- ── Franja 1: utilidades institucionales, sobre amarillo ──────────────── --}}
@if($topbarEnabled && $topbarItems->isNotEmpty())
<div class="hidden md:block bg-brand-accent text-on-accent">
    <div class="max-w-[1440px] mx-auto px-5 md:px-10 lg:px-14 h-8 flex items-center justify-end">
        <nav class="flex items-center gap-5" aria-label="Enlaces institucionales">
            @foreach($topbarItems as $item)
                <a href="{{ $item->url ?? '#' }}"
                   @if($item->target) target="{{ $item->target }}" @endif
                   class="text-[12px] font-bold uppercase tracking-[0.05em] text-on-accent/85 hover:text-on-accent transition-colors rounded-pill focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-navy">
                    {{ $item->label }}
                </a>
            @endforeach
        </nav>
    </div>
</div>
@endif

{{-- ── Franja 2: contacto, reloj y redes, sobre navy ─────────────────────── --}}
<div class="hidden md:block bg-brand-navy text-on-navy/85 text-[12px]">
    <div class="max-w-[1440px] mx-auto px-5 md:px-10 lg:px-14 h-9 flex items-center justify-between gap-4">
        <div class="flex items-center gap-5 min-w-0">
            @if(settings('contact_phone'))
                <span class="num-tabular truncate">{{ settings('contact_phone') }}</span>
            @endif
            @if(settings('contact_email'))
                <a href="mailto:{{ settings('contact_email') }}" class="hidden lg:inline hover:text-white transition-colors truncate">
                    {{ settings('contact_email') }}
                </a>
            @endif
            @if($showClock)
                <span x-data="gyeClock" class="num-tabular text-on-navy/70 hidden lg:inline" aria-label="Hora en Guayaquil">
                    <span x-text="time"></span>
                </span>
            @endif
        </div>
        @if($socialLinks)
            <div class="flex items-center gap-3 shrink-0">
                @foreach($socialLinks as $net => $url)
                    <a href="{{ $url }}" target="_blank" rel="noopener"
                       class="text-on-navy/70 hover:text-white transition-colors" aria-label="{{ ucfirst($net) }}">
                        <x-icon.social :name="$net" />
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>

{{-- Franjas 3 y 4 + menu movil comparten un unico scope Alpine --}}
<div x-data="{ mobileOpen: false, openDropdown: null }" @click.outside="openDropdown = null">

    {{-- ── Franja 3: marca + CTA, filete amarillo de 3px ─────────────────── --}}
    <div class="bg-card rule-accent">
        <div class="max-w-[1440px] mx-auto px-5 md:px-10 lg:px-14 h-[72px] md:h-[90px] flex items-center justify-between gap-6">
            <x-ui.brand-mark />

            <div class="flex items-center gap-3">
                @if($darkAllowed)
                    <button type="button" @click="$store.theme.toggle()"
                            class="hidden sm:flex w-9 h-9 items-center justify-center text-muted hover:text-fg hover:bg-border/40 transition"
                            aria-label="Cambiar tema">
                        <svg class="w-[18px] h-[18px] dark:hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z"/></svg>
                        <svg class="w-[18px] h-[18px] hidden dark:block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2m0 14v2M5.64 5.64l1.42 1.42m9.9 9.9 1.4 1.4M3 12h2m14 0h2M5.64 18.36l1.42-1.42m9.9-9.9 1.4-1.4M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8Z"/></svg>
                    </button>
                @endif

                {{-- CTA celeste en caja: el ".gov-cta" de la maqueta B --}}
                @if($ctaEnabled)
                    <a href="{{ settings('header_cta_url', '#') }}"
                       class="hidden md:block rounded-card bg-brand-primary text-on-primary px-6 py-2.5 text-center hover:opacity-90 transition-opacity">
                        <span class="block text-[12px] font-bold uppercase tracking-[0.07em]">
                            {{ settings('header_cta_label', 'Estado de vuelos') }}
                        </span>
                        @if(settings('header_cta_sublabel'))
                            <span class="block text-[11px] text-on-primary/90 mt-0.5">{{ settings('header_cta_sublabel') }}</span>
                        @endif
                    </a>
                @endif

                <button type="button" @click="mobileOpen = !mobileOpen" class="lg:hidden text-brand-navy" aria-label="Abrir menu">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path x-show="!mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        <path x-show="mobileOpen" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6l12 12M6 18L18 6"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- ── Franja 4: navegacion principal a lo ancho, sobre navy ─────────── --}}
    <nav class="hidden lg:block bg-brand-navy" aria-label="Navegacion principal">
        <div class="max-w-[1440px] mx-auto px-5 md:px-10 lg:px-14 flex">
            @foreach($topLevelItems as $item)
                @php $children = $childrenOf($item); $active = $isActiveItem($item); @endphp
                <div class="relative flex-1 flex"
                     @if($children->isNotEmpty()) @mouseenter="openDropdown = {{ $item->id }}" @mouseleave="openDropdown = null" @endif>
                    <a href="{{ $item->url ?? '#' }}"
                       @if($item->target) target="{{ $item->target }}" @endif
                       @if($active) aria-current="page" @endif
                       class="nav-link {{ $active ? 'nav-link-active' : '' }}">
                        {{ $item->label }}
                    </a>
                    @if($children->isNotEmpty())
                        <div x-show="openDropdown === {{ $item->id }}" x-cloak
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="absolute left-0 top-full min-w-[220px] z-50">
                            <div class="bg-card border border-border overflow-hidden py-1">
                                @foreach($children as $child)
                                    <a href="{{ $child->url ?? '#' }}"
                                       class="block px-4 py-2.5 text-[14px] font-semibold text-fg hover:bg-brand-soft hover:text-brand-primary transition-colors">
                                        {{ $child->label }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </nav>

    {{-- ── Menu movil ────────────────────────────────────────────────────── --}}
    <div x-show="mobileOpen" x-cloak x-transition class="lg:hidden border-t border-border bg-card max-h-[80vh] overflow-y-auto">
        <nav class="max-w-[1440px] mx-auto px-5 py-4 flex flex-col gap-1" x-data="{ mobileSection: null }">
            @foreach($topLevelItems as $item)
                @php $children = $childrenOf($item); @endphp
                @if($children->isEmpty())
                    <a href="{{ $item->url ?? '#' }}" class="py-2.5 text-sm font-bold text-brand-navy hover:text-brand-primary uppercase tracking-wider">
                        {{ $item->label }}
                    </a>
                @else
                    <div class="border-b border-border/50 last:border-0">
                        <button type="button" @click="mobileSection = mobileSection === {{ $item->id }} ? null : {{ $item->id }}"
                                class="w-full flex items-center justify-between py-2.5 text-sm font-bold text-brand-navy uppercase tracking-wider">
                            <span>{{ $item->label }}</span>
                            <svg class="w-4 h-4 transition-transform" :class="mobileSection === {{ $item->id }} ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                            </svg>
                        </button>
                        <div x-show="mobileSection === {{ $item->id }}" x-cloak class="pb-3 pl-3">
                            @foreach($children as $child)
                                <a href="{{ $child->url ?? '#' }}" class="block py-1.5 text-sm text-muted hover:text-brand-primary">{{ $child->label }}</a>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach

            {{-- En movil se recuperan los enlaces institucionales de la franja 1 --}}
            @if($topbarEnabled && $topbarItems->isNotEmpty())
                <div class="mt-3 pt-3 border-t border-border flex flex-wrap gap-x-4 gap-y-2">
                    @foreach($topbarItems as $item)
                        <a href="{{ $item->url ?? '#' }}" class="text-[12px] font-bold uppercase tracking-[0.05em] text-muted hover:text-brand-primary">
                            {{ $item->label }}
                        </a>
                    @endforeach
                </div>
            @endif

            @if($ctaEnabled)
                <a href="{{ settings('header_cta_url', '#') }}" class="btn-primary mt-4 self-start">
                    {{ settings('header_cta_label', 'Estado de vuelos') }}
                </a>
            @endif
        </nav>
    </div>
</div>
