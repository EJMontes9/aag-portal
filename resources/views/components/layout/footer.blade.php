@php
    $footerMenu = \App\Models\Menu::byLocation('footer');
    $institutionsMenu = \App\Models\Menu::byLocation('footer_secondary');
    $socialLinks = array_filter([
        'facebook' => settings('social_facebook'),
        'twitter' => settings('social_twitter'),
        'instagram' => settings('social_instagram'),
        'youtube' => settings('social_youtube'),
        'linkedin' => settings('social_linkedin'),
    ]);
@endphp

<footer class="bg-brand-navy text-on-navy mt-16">
    <div class="max-w-[1280px] mx-auto px-6 md:px-10 py-14">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-10">
            <div class="lg:col-span-2">
                <x-ui.brand-mark tone="on-navy" />
                <p class="mt-6 text-sm text-on-navy/75 max-w-sm leading-[1.65]">
                    {{ settings('footer_about') }}
                </p>
            </div>

            <div>
                <h4 class="kicker text-on-navy/70 mb-4 font-sans">CONTACTANOS</h4>
                <div class="space-y-3 text-sm text-on-navy/80">
                    @if(settings('contact_address'))
                        <p class="leading-relaxed">{{ settings('contact_address') }}</p>
                    @endif
                    @if(settings('contact_phone'))
                        <p class="font-mono num-tabular">{{ settings('contact_phone') }}</p>
                    @endif
                    @if(settings('contact_email'))
                        <a href="mailto:{{ settings('contact_email') }}" class="hover:text-white">{{ settings('contact_email') }}</a>
                    @endif
                </div>
            </div>

            @if($footerMenu)
            <div>
                <h4 class="kicker text-on-navy/70 mb-4 font-sans">ENLACES</h4>
                <ul class="space-y-2.5 text-sm text-on-navy/80">
                    @foreach($footerMenu->items as $item)
                        @if($item->is_active)
                            <li><a href="{{ $item->url ?? '#' }}" class="hover:text-white transition">{{ $item->label }}</a></li>
                        @endif
                    @endforeach
                </ul>
            </div>
            @endif

            @if($institutionsMenu)
            <div>
                <h4 class="kicker text-on-navy/70 mb-4 font-sans">INSTITUCIONES</h4>
                <ul class="space-y-2.5 text-sm text-on-navy/80">
                    @foreach($institutionsMenu->items as $item)
                        @if($item->is_active)
                            <li><a href="{{ $item->url ?? '#' }}" @if($item->target) target="{{ $item->target }}" rel="noopener" @endif class="hover:text-white transition">{{ $item->label }}</a></li>
                        @endif
                    @endforeach
                </ul>
            </div>
            @endif
        </div>

        @if($socialLinks)
            <div class="mt-12 pt-8 border-t border-white/10 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <p class="kicker text-on-navy/70 font-sans">SIGUENOS</p>
                <div class="flex items-center gap-3">
                    @foreach($socialLinks as $net => $url)
                        <a href="{{ $url }}" target="_blank" rel="noopener"
                           class="w-9 h-9 rounded-full border border-white/15 flex items-center justify-center text-on-navy/80 hover:text-white hover:border-white/40 transition"
                           aria-label="{{ ucfirst($net) }}">
                            <x-icon.social :name="$net" />
                        </a>
                    @endforeach
                </div>
                <p class="text-sm text-on-navy/70 max-w-xs">Mantente informado en nuestras redes sociales.</p>
            </div>
        @endif

        <div class="mt-8 pt-6 border-t border-white/10 flex flex-col md:flex-row justify-between gap-3 text-xs text-on-navy/60">
            <p>{{ settings('footer_copyright') }}</p>
            <div class="flex gap-5">
                <a href="/politica-privacidad" class="hover:text-white">Politica de privacidad</a>
                <a href="/terminos" class="hover:text-white">Terminos de uso</a>
                <a href="/sitemap" class="hover:text-white">Mapa del sitio</a>
            </div>
        </div>
    </div>
</footer>
