{{--
    Footer institucional AAG — Propuesta B.

    Banda navy con el logo arriba (fuera de la rejilla) y hasta 5 columnas
    iguales: Direccion + las que existan como Menu (footer_secondary,
    footer_services, footer_transparency, footer). Las columnas opcionales se
    ocultan solas si no hay un Menu con esa location -- no se inventan enlaces
    que no le correspondan a la AAG.

    IMPORTANTE: la AAG es una fundacion de la Municipalidad de Guayaquil
    (Alcaldia), NO es una entidad del Gobierno Nacional del Ecuador. Este
    footer no debe llevar ninguna mencion, marca ni enlace del Gobierno
    Nacional.

    Respecto a la maqueta: en B los <li> del footer son texto plano; aqui son
    enlaces reales, porque en un portal en produccion tienen que serlo.
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
                <h4 class="text-[11px] font-bold text-brand-accent uppercase tracking-[0.09em] mb-3">DIRECCIÓN</h4>
                <div class="space-y-2 text-xs text-on-navy/70">
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
                    <h4 class="text-[11px] font-bold text-brand-accent uppercase tracking-[0.09em] mb-3">{{ $col['label'] }}</h4>
                    <ul class="space-y-2 text-xs text-on-navy/70">
                        @foreach($col['menu']->items as $item)
                            @if($item->is_active)
                                <li>
                                    <a href="{{ $item->url ?? '#' }}"
                                       @if($item->target) target="{{ $item->target }}" rel="noopener" @endif
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
                <p class="text-[11px] font-bold text-brand-accent uppercase tracking-[0.09em]">SÍGUENOS</p>
                <div class="flex items-center gap-2.5">
                    @foreach($socialLinks as $net => $url)
                        {{-- Cuadrados, no circulos: en B no existe ninguna forma redondeada --}}
                        <a href="{{ $url }}" target="_blank" rel="noopener"
                           class="w-9 h-9 rounded-pill border border-white/15 flex items-center justify-center text-on-navy/80 hover:text-white hover:border-white/40 transition-colors"
                           aria-label="{{ ucfirst($net) }}">
                            <x-icon.social :name="$net" />
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="mt-6 pt-5 border-t border-white/15 flex flex-col md:flex-row justify-between gap-3 text-[10px] text-on-navy/50">
            <p>{{ settings('footer_copyright') }}</p>
            <div class="flex flex-wrap gap-5">
                <a href="/politica-privacidad" class="hover:text-white transition-colors">Política de privacidad</a>
                <a href="/terminos" class="hover:text-white transition-colors">Términos de uso</a>
                <a href="/sitemap" class="hover:text-white transition-colors">Mapa del sitio</a>
            </div>
        </div>
    </div>
</footer>
