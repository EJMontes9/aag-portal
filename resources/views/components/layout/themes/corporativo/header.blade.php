{{--
    Tema "Corporativo": header en 4 franjas (utilidades, contacto, marca, navegacion
    a lo ancho en mayusculas), inspirado en la Propuesta B. Usa los MISMOS datos
    y los MISMOS tokens de color (brand-navy/primary/accent/soft) que el tema
    institucional -- el color sigue siendo una configuracion global, el tema
    solo cambia como se ordenan y se estilizan esos mismos colores.
    La AAG es una fundacion municipal (Alcaldia de Guayaquil), NO es una entidad
    del Gobierno Nacional del Ecuador: este header no debe llevar ninguna marca,
    escudo, franja ni mencion del Gobierno Nacional.
--}}
@php
    $topbarEnabled = (bool) settings('topbar_enabled', true);
    $headerMenu = \App\Models\Menu::byLocation('header');
    $topbarMenu = \App\Models\Menu::byLocation('topbar');
    $ctaEnabled = (bool) settings('header_cta_enabled', true);
    $darkAllowed = (bool) settings('dark_mode_enabled', true);

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

{{-- Franja 1: utilidades institucionales sobre fondo amarillo acento (Propuesta B) --}}
@if($topbarEnabled && $topbarItems->isNotEmpty())
<div class="bg-brand-accent text-on-accent text-[11px]">
    <div class="max-w-[1280px] mx-auto px-6 md:px-10 h-8 flex items-center justify-end gap-5">
        <nav class="flex items-center gap-5" aria-label="Enlaces institucionales">
            @foreach($topbarItems as $i => $item)
                @if($i > 0)<span class="opacity-40" aria-hidden="true">·</span>@endif
                <a href="{{ $item->url ?? '#' }}"
                   @if($item->target) target="{{ $item->target }}" @endif
                   class="tracking-[0.1em] uppercase text-[10px] font-bold text-on-accent/80 hover:text-on-accent transition-colors">
                    {{ $item->label }}
                </a>
            @endforeach
        </nav>
    </div>
</div>
@endif

{{-- Franja 2: contacto directo, sobre fondo navy --}}
<div class="bg-brand-navy text-on-navy/85 text-[11px]">
    <div class="max-w-[1280px] mx-auto px-6 md:px-10 h-9 flex items-center justify-between gap-4">
        <div class="flex items-center gap-5 min-w-0">
            @if(settings('contact_phone'))
                <span class="hidden sm:inline font-mono num-tabular truncate">{{ settings('contact_phone') }}</span>
            @endif
            @if(settings('contact_email'))
                <a href="mailto:{{ settings('contact_email') }}" class="hidden md:inline hover:text-white truncate">{{ settings('contact_email') }}</a>
            @endif
        </div>
        @if($socialLinks)
            <div class="flex items-center gap-3 shrink-0">
                @foreach($socialLinks as $net => $url)
                    <a href="{{ $url }}" target="_blank" rel="noopener" class="text-on-navy/70 hover:text-white transition-colors" aria-label="{{ ucfirst($net) }}">
                        <x-icon.social :name="$net" />
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>

{{-- Franjas 3 y 4 + menu movil comparten un unico scope Alpine --}}
<div x-data="{ mobileOpen: false, openDropdown: null }" @click.outside="openDropdown = null">

    {{-- Franja 3: marca institucional + CTA --}}
    <div class="bg-card border-b-4 border-brand-accent sticky top-0 z-40">
        <div class="max-w-[1280px] mx-auto px-6 md:px-10 h-[84px] flex items-center justify-between gap-6">
            <x-ui.brand-mark />

            <div class="flex items-center gap-3">
                @if($darkAllowed)
                    <button type="button" @click="$store.theme.toggle()" class="hidden sm:flex w-9 h-9 items-center justify-center text-muted hover:text-fg hover:bg-border/40 transition" aria-label="Cambiar tema">
                        <svg class="w-[18px] h-[18px] dark:hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z"/></svg>
                        <svg class="w-[18px] h-[18px] hidden dark:block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2m0 14v2M5.64 5.64l1.42 1.42m9.9 9.9 1.4 1.4M3 12h2m14 0h2M5.64 18.36l1.42-1.42m9.9-9.9 1.4-1.4M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8Z"/></svg>
                    </button>
                @endif
                @if($ctaEnabled)
                    <a href="{{ settings('header_cta_url', '#') }}"
                       class="hidden md:inline-flex items-center bg-brand-primary text-on-primary px-5 py-2.5 text-[11px] font-bold uppercase tracking-[0.08em] hover:bg-brand-accent hover:text-on-accent transition-colors">
                        {{ settings('header_cta_label', 'Estado de vuelos') }}
                    </a>
                @endif
                <button type="button" @click="mobileOpen = !mobileOpen" class="lg:hidden text-fg" aria-label="Abrir menu">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path x-show="!mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        <path x-show="mobileOpen" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6l12 12M6 18L18 6"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Franja 4: navegacion principal, a lo ancho, en mayusculas --}}
    <nav class="hidden lg:block bg-brand-navy" aria-label="Navegacion principal">
        <div class="max-w-[1280px] mx-auto flex">
            @foreach($topLevelItems as $item)
                @php $children = $childrenOf($item); $active = $isActiveItem($item); @endphp
                <div class="relative flex-1" @if($children->isNotEmpty()) @mouseenter="openDropdown = {{ $item->id }}" @mouseleave="openDropdown = null" @endif>
                    <a href="{{ $item->url ?? '#' }}"
                       @if($item->target) target="{{ $item->target }}" @endif
                       class="flex items-center justify-center text-center px-4 h-11 text-[11px] font-bold uppercase tracking-[0.06em] text-on-navy transition-colors border-b-[3px]
                              {{ $active ? 'bg-black/15 border-brand-accent' : 'border-transparent hover:bg-white/10' }}">
                        {{ $item->label }}
                    </a>
                    @if($children->isNotEmpty())
                        <div x-show="openDropdown === {{ $item->id }}" x-cloak
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="absolute left-0 top-full min-w-[220px] z-50">
                            <div class="bg-card border border-border shadow-lg overflow-hidden py-2">
                                @foreach($children as $child)
                                    <a href="{{ $child->url ?? '#' }}" class="block px-4 py-2.5 text-xs font-medium text-fg hover:bg-brand-soft/30 hover:text-brand-primary transition-colors">
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

    {{-- Menu movil --}}
    <div x-show="mobileOpen" x-cloak x-transition class="lg:hidden border-t border-border bg-card max-h-[80vh] overflow-y-auto">
        <nav class="max-w-[1280px] mx-auto px-6 py-4 flex flex-col gap-1" x-data="{ mobileSection: null }">
            @foreach($topLevelItems as $item)
                @php $children = $childrenOf($item); @endphp
                @if($children->isEmpty())
                    <a href="{{ $item->url ?? '#' }}" class="py-2.5 text-sm font-bold text-fg hover:text-brand-primary uppercase tracking-wider">{{ $item->label }}</a>
                @else
                    <div class="border-b border-border/50 last:border-0">
                        <button type="button" @click="mobileSection = mobileSection === {{ $item->id }} ? null : {{ $item->id }}"
                                class="w-full flex items-center justify-between py-2.5 text-sm font-bold text-fg uppercase tracking-wider">
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
            @if($ctaEnabled)
                <a href="{{ settings('header_cta_url', '#') }}" class="mt-4 self-start bg-brand-primary text-on-primary px-5 py-2.5 text-[11px] font-bold uppercase tracking-[0.08em]">
                    {{ settings('header_cta_label', 'Estado de vuelos') }}
                </a>
            @endif
        </nav>
    </div>
</div>
