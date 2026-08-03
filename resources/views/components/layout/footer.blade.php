{{--
    Footer institucional AAG — Propuesta B.

    Banda navy con el logo arriba (fuera de la rejilla) y hasta 5 columnas
    iguales: Dirección + las que existan como Menu (footer_secondary,
    footer_services, footer_transparency, footer). Las columnas opcionales se
    ocultan solas si no hay un Menu con esa location -- no se inventan enlaces
    que no le correspondan a la AAG.

    IMPORTANTE: la AAG es una fundación de la Municipalidad de Guayaquil
    (Alcaldía), NO es una entidad del Gobierno Nacional del Ecuador. Este
    footer no debe llevar ninguna mención, marca ni enlace del Gobierno
    Nacional.

    Respecto a la maqueta: en B los <li> del footer son texto plano; aquí son
    enlaces reales, porque en un portal en producción tienen que serlo.
--}}
@php
    $enlacesMenu = \App\Models\Menu::byLocation('footer');
    $institucionMenu = \App\Models\Menu::byLocation('footer_secondary');
    $serviciosMenu = \App\Models\Menu::byLocation('footer_services');
    $transparenciaMenu = \App\Models\Menu::byLocation('footer_transparency');

    $socialLinks = array_filter([
        'facebook' => settings('social_facebook'),
        'twitter' => settings('social_twitter'),
        'instagram' => settings('social_instagram'),
        'youtube' => settings('social_youtube'),
        'linkedin' => settings('social_linkedin'),
    ]);

    $columns = array_filter([
        $institucionMenu ? ['label' => 'INSTITUCIÓN', 'menu' => $institucionMenu] : null,
        $serviciosMenu ? ['label' => 'SERVICIOS', 'menu' => $serviciosMenu] : null,
        $transparenciaMenu ? ['label' => 'TRANSPARENCIA', 'menu' => $transparenciaMenu] : null,
        $enlacesMenu ? ['label' => 'ENLACES', 'menu' => $enlacesMenu] : null,
    ]);

    $about = settings('footer_about');
@endphp

<footer class="bg-brand-navy text-on-navy">
    <div class="max-w-[1440px] mx-auto px-5 md:px-10 lg:px-14 pt-12 pb-5">
        <x-ui.brand-mark tone="on-navy" :eager="false" />

        <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-8 lg:gap-10">
            <div>
                <h4 class="text-[12px] font-bold text-brand-accent uppercase tracking-[0.09em] mb-3.5">DIRECCIÓN</h4>
                <div class="space-y-2.5 text-[14px] text-on-navy/75">
                    @if(settings('contact_address'))<p class="leading-relaxed">{{ settings('contact_address') }}</p>@endif
                    @if(settings('contact_phone'))<p class="num-tabular">{{ settings('contact_phone') }}</p>@endif
                    @if(settings('contact_email'))
                        <a href="mailto:{{ settings('contact_email') }}" class="block hover:text-white transition-colors">{{ settings('contact_email') }}</a>
                    @endif
                    @if($about)
                        <p class="leading-relaxed pt-1">{{ $about }}</p>
                    @endif
                </div>
            </div>

            @foreach($columns as $col)
                <div>
                    <h4 class="text-[12px] font-bold text-brand-accent uppercase tracking-[0.09em] mb-3.5">{{ $col['label'] }}</h4>
                    <ul class="space-y-2.5 text-[14px] text-on-navy/75">
                        @foreach($col['menu']->items as $item)
                            @if($item->is_active)
                                <li>
                                    <a href="{{ $item->url ?? '#' }}"
                                       @if($item->target) target="{{ $item->target }}" rel="noopener" @endif
                                       @if(is_internal_link($item->url ?? null, $item->target ?? null)) wire:navigate @endif
                                       class="hover:text-white transition-colors">{{ $item->label }}</a>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>

        @if($socialLinks)
            <div class="mt-10 pt-6 border-t border-white/15 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <p class="text-[12px] font-bold text-brand-accent uppercase tracking-[0.09em]">SÍGUENOS</p>
                <div class="flex items-center gap-2.5">
                    @foreach($socialLinks as $net => $url)
                        {{-- Cuadrados, no círculos: en B no existe ninguna forma redondeada --}}
                        <a href="{{ $url }}" target="_blank" rel="noopener"
                           class="w-9 h-9 rounded-pill border border-white/15 flex items-center justify-center text-on-navy/80 hover:text-white hover:border-white/40 transition-colors"
                           aria-label="{{ ucfirst($net) }}">
                            <x-icon.social :name="$net" />
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- 12px y opacidad 65%: a 10px sobre /50 el aviso legal quedaba casi
             ilegible, y es justo el texto que la gente busca a propósito. --}}
        <div class="mt-6 pt-5 border-t border-white/15 flex flex-col md:flex-row justify-between gap-3 text-[12px] text-on-navy/65">
            <p>{{ settings('footer_copyright') }}</p>
            <div class="flex flex-wrap gap-5">
                <a href="/politica-privacidad" wire:navigate class="rounded-pill hover:text-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-accent">Política de privacidad</a>
                <a href="/terminos" wire:navigate class="rounded-pill hover:text-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-accent">Términos de uso</a>
                {{-- El mapa del sitio se publica en /sitemap.xml (SitemapController).
                     Este enlace apuntaba a /sitemap, que no existe como ruta y devolvía
                     404. En producción quedó cubierto además con una redirección 301.
                     Sin wire:navigate a propósito: es XML, no una página Blade -- Livewire
                     espera reemplazar un documento HTML, y aquí rompería el swap. --}}
                <a href="/sitemap.xml" class="rounded-pill hover:text-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-accent">Mapa del sitio</a>
            </div>
        </div>
    </div>
</footer>
