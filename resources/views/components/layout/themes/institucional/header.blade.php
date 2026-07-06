@php
    $topbarEnabled = (bool) settings('topbar_enabled', true);
    $headerMenu = \App\Models\Menu::byLocation('header');
    $topbarMenu = \App\Models\Menu::byLocation('topbar');
    $ctaEnabled = (bool) settings('header_cta_enabled', true);
    $showClock = (bool) settings('header_show_clock', true);
    $darkAllowed = (bool) settings('dark_mode_enabled', true);

    $socialLinks = array_filter([
        'facebook' => settings('social_facebook'),
        'twitter' => settings('social_twitter'),
        'instagram' => settings('social_instagram'),
        'youtube' => settings('social_youtube'),
    ]);

    // $headerMenu->items ya filtra parent_id IS NULL y precarga 'children'
    // gracias al eager load with('items.children') en Menu::byLocation().
    $topLevelItems = $headerMenu
        ? $headerMenu->items->where('is_active', true)->sortBy('sort_order')->values()
        : collect();

    $topbarItems = $topbarMenu
        ? $topbarMenu->items->where('is_active', true)->sortBy('sort_order')->values()
        : collect();

    $childrenOf = function ($parent) {
        return $parent->children
            ->where('is_active', true)
            ->sortBy('sort_order')
            ->values();
    };
@endphp

@if($topbarEnabled)
<div class="bg-brand-navy text-on-navy/80 text-[11px]">
    <div class="max-w-[1280px] mx-auto px-6 md:px-10 h-9 flex items-center justify-between gap-4">
        <div class="flex items-center gap-4 min-w-0">
            @if($showClock)
                <span x-data="gyeClock" class="font-mono num-tabular text-on-navy/70 hidden md:inline" aria-label="Hora en Guayaquil">
                    <span x-text="time"></span> GYE
                </span>
            @endif
            <span class="truncate">{{ settings('topbar_text') }}</span>
        </div>
        <div class="flex items-center gap-5 shrink-0">
            @if($topbarItems->isNotEmpty())
                <nav class="hidden md:flex items-center gap-5" aria-label="Enlaces secundarios">
                    @foreach($topbarItems as $i => $item)
                        @if($i > 0)
                            <span class="text-on-navy/30" aria-hidden="true">·</span>
                        @endif
                        <a href="{{ $item->url ?? '#' }}"
                           @if($item->target) target="{{ $item->target }}" @endif
                           class="tracking-[0.12em] uppercase text-[11px] font-medium hover:text-white transition-colors">
                            {{ $item->label }}
                        </a>
                    @endforeach
                </nav>
            @endif

            @if($socialLinks)
                @if($topbarItems->isNotEmpty())
                    <span class="hidden md:inline text-on-navy/30" aria-hidden="true">·</span>
                @endif
                <div class="hidden md:flex items-center gap-3">
                    @foreach($socialLinks as $net => $url)
                        <a href="{{ $url }}" target="_blank" rel="noopener" class="text-on-navy/70 hover:text-white transition-colors" aria-label="{{ ucfirst($net) }}">
                            <x-icon.social :name="$net" />
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endif

<header class="bg-card border-b border-border sticky top-0 z-40 backdrop-blur supports-[backdrop-filter]:bg-card/90"
        x-data="{ mobileOpen: false, openDropdown: null }"
        @click.outside="openDropdown = null">
    <div class="max-w-[1280px] mx-auto px-6 md:px-10 h-[68px] flex items-center gap-6">
        <x-ui.brand-mark />

        <nav class="hidden lg:flex items-center gap-1 ml-8 flex-1" aria-label="Navegacion principal">
            @foreach($topLevelItems as $item)
                @php $children = $childrenOf($item); @endphp

                @if($children->isEmpty())
                    {{-- Item simple --}}
                    <a href="{{ $item->url ?? '#' }}"
                       @if($item->target) target="{{ $item->target }}" @endif
                       class="nav-link">
                        {{ $item->label }}
                    </a>
                @else
                    {{-- Item con dropdown --}}
                    <div class="relative"
                         @mouseenter="openDropdown = {{ $item->id }}"
                         @mouseleave="openDropdown = null">
                        <button type="button"
                                @click="openDropdown = openDropdown === {{ $item->id }} ? null : {{ $item->id }}"
                                :aria-expanded="openDropdown === {{ $item->id }}"
                                class="nav-link inline-flex items-center gap-1">
                            {{ $item->label }}
                            <svg class="w-3 h-3 transition-transform duration-200"
                                 :class="openDropdown === {{ $item->id }} ? 'rotate-180' : ''"
                                 fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                            </svg>
                        </button>

                        <div x-show="openDropdown === {{ $item->id }}"
                             x-cloak
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0"
                             class="absolute left-0 top-full pt-2 min-w-[240px] z-50">
                            <div class="bg-card border border-border rounded-md shadow-lg overflow-hidden py-2">
                                @foreach($children as $child)
                                    <a href="{{ $child->url ?? '#' }}"
                                       @if($child->target) target="{{ $child->target }}" @endif
                                       class="block px-4 py-2.5 text-sm text-fg hover:bg-brand-soft/30 hover:text-brand-primary transition-colors">
                                        {{ $child->label }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </nav>

        <div class="flex items-center gap-3 ml-auto">
            <button type="button" class="w-9 h-9 flex items-center justify-center rounded-full text-muted hover:text-fg hover:bg-border/40 transition" aria-label="Buscar">
                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.2-5.2M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z"/></svg>
            </button>

            @if($darkAllowed)
                <button type="button" @click="$store.theme.toggle()" class="w-9 h-9 flex items-center justify-center rounded-full text-muted hover:text-fg hover:bg-border/40 transition" aria-label="Cambiar tema">
                    <svg class="w-[18px] h-[18px] dark:hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z"/></svg>
                    <svg class="w-[18px] h-[18px] hidden dark:block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2m0 14v2M5.64 5.64l1.42 1.42m9.9 9.9 1.4 1.4M3 12h2m14 0h2M5.64 18.36l1.42-1.42m9.9-9.9 1.4-1.4M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8Z"/></svg>
                </button>
            @endif

            @if($ctaEnabled)
                <a href="{{ settings('header_cta_url', '#') }}" class="hidden md:inline-flex btn-primary">
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

    {{-- Menu movil con submenus colapsables --}}
    <div x-show="mobileOpen" x-cloak x-transition class="lg:hidden border-t border-border bg-card max-h-[80vh] overflow-y-auto">
        <nav class="max-w-[1280px] mx-auto px-6 py-4 flex flex-col gap-1" x-data="{ mobileSection: null }">
            @foreach($topLevelItems as $item)
                @php $children = $childrenOf($item); @endphp
                @if($children->isEmpty())
                    <a href="{{ $item->url ?? '#' }}" class="py-2.5 text-sm font-medium text-fg hover:text-brand-primary uppercase tracking-wider">
                        {{ $item->label }}
                    </a>
                @else
                    <div class="border-b border-border/50 last:border-0">
                        <button type="button"
                                @click="mobileSection = mobileSection === {{ $item->id }} ? null : {{ $item->id }}"
                                class="w-full flex items-center justify-between py-2.5 text-sm font-medium text-fg uppercase tracking-wider">
                            <span>{{ $item->label }}</span>
                            <svg class="w-4 h-4 transition-transform"
                                 :class="mobileSection === {{ $item->id }} ? 'rotate-180' : ''"
                                 fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                            </svg>
                        </button>
                        <div x-show="mobileSection === {{ $item->id }}"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="pb-3 pl-3"
                             style="display: none;">
                            @foreach($children as $child)
                                <a href="{{ $child->url ?? '#' }}" class="block py-1.5 text-sm text-muted hover:text-brand-primary">
                                    {{ $child->label }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach

            @if($ctaEnabled)
                <a href="{{ settings('header_cta_url', '#') }}" class="mt-4 self-start btn-primary">
                    {{ settings('header_cta_label', 'Estado de vuelos') }}
                </a>
            @endif
        </nav>
    </div>
</header>
