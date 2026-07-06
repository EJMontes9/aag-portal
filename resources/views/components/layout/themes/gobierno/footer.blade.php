{{--
    Tema "Gobierno": footer de hasta 5 columnas fijas (Direccion, Institucion,
    Servicios, Transparencia, Enlaces). Las columnas "Servicios" y "Transparencia"
    son opcionales: se ocultan solas si no existe un Menu con esa location
    (footer_services / footer_transparency) -- no se inventan enlaces externos
    que no le correspondan a la AAG (ver naturaleza juridica: fundacion municipal,
    no gobierno nacional).
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
@endphp

<footer class="bg-brand-navy text-on-navy mt-16">
    <div class="max-w-[1280px] mx-auto px-6 md:px-10 py-14">
        <x-ui.brand-mark tone="on-navy" :eager="false" />

        <div class="mt-10 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-10">
            <div>
                <h4 class="text-[11px] font-bold text-brand-accent uppercase tracking-[0.1em] mb-4">DIRECCIÓN</h4>
                <div class="space-y-2.5 text-sm text-on-navy/80">
                    @if(settings('contact_address'))<p class="leading-relaxed">{{ settings('contact_address') }}</p>@endif
                    @if(settings('contact_phone'))<p class="font-mono num-tabular">{{ settings('contact_phone') }}</p>@endif
                    @if(settings('contact_email'))<a href="mailto:{{ settings('contact_email') }}" class="block hover:text-white">{{ settings('contact_email') }}</a>@endif
                </div>
            </div>

            @foreach($columns as $col)
                <div>
                    <h4 class="text-[11px] font-bold text-brand-accent uppercase tracking-[0.1em] mb-4">{{ $col['label'] }}</h4>
                    <ul class="space-y-2.5 text-sm text-on-navy/80">
                        @foreach($col['menu']->items as $item)
                            @if($item->is_active)
                                <li><a href="{{ $item->url ?? '#' }}" @if($item->target) target="{{ $item->target }}" rel="noopener" @endif class="hover:text-white transition">{{ $item->label }}</a></li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>

        @if($socialLinks)
            <div class="mt-12 pt-8 border-t border-white/10 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <p class="text-[11px] font-bold text-brand-accent uppercase tracking-[0.1em]">SÍGUENOS</p>
                <div class="flex items-center gap-3">
                    @foreach($socialLinks as $net => $url)
                        <a href="{{ $url }}" target="_blank" rel="noopener"
                           class="w-9 h-9 border border-white/15 flex items-center justify-center text-on-navy/80 hover:text-white hover:border-white/40 transition"
                           aria-label="{{ ucfirst($net) }}">
                            <x-icon.social :name="$net" />
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="mt-8 pt-6 border-t border-white/10 flex flex-col md:flex-row justify-between gap-3 text-xs text-on-navy/60">
            <p>{{ settings('footer_copyright') }}</p>
            <div class="flex gap-5">
                <a href="/politica-privacidad" class="hover:text-white">Política de privacidad</a>
                <a href="/terminos" class="hover:text-white">Términos de uso</a>
                <a href="/sitemap" class="hover:text-white">Mapa del sitio</a>
            </div>
        </div>
    </div>
</footer>
