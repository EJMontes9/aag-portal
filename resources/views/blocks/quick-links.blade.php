@props(['block'])
@php
    $links = $block->get('links', []);
    if (empty($links)) return;

    $iconPaths = [
        'plane' => 'M10.5 2.25a.75.75 0 0 1 1.5 0v5.69l7.5 4.33v1.8l-7.5-2.25v4.87l2.25 1.5v1.31l-3-.75-3 .75v-1.31l2.25-1.5v-4.87l-7.5 2.25v-1.8l7.5-4.33V2.25Z',
        'doc' => 'M6 2.25A2.25 2.25 0 0 0 3.75 4.5v15A2.25 2.25 0 0 0 6 21.75h12A2.25 2.25 0 0 0 20.25 19.5V9l-6.75-6.75H6Zm7.5 0v6.75h6.75',
        'check' => 'M4.5 12.75l6 6 9-13.5',
        'building' => 'M3 21V7.5L12 3l9 4.5V21M3 21h18M7.5 9.75v2.25M7.5 15v2.25M12 9.75v2.25M12 15v2.25M16.5 9.75v2.25M16.5 15v2.25',
        'download' => 'M12 3v13.5m0 0l-4.5-4.5M12 16.5l4.5-4.5M4.5 19.5h15',
        'phone' => 'M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.35-.966-.85-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.37a12.035 12.035 0 0 1-7.143-7.143c-.172-.441-.005-.928.37-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z',
        'envelope' => 'M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75',
        'user' => 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a7.5 7.5 0 0 1 15 0',
        'globe' => 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18ZM12 3c2.485 0 4.5 4.03 4.5 9s-2.015 9-4.5 9m0-18c-2.485 0-4.5 4.03-4.5 9s2.015 9 4.5 9M3 12h18',
        'search' => 'M21 21l-5.2-5.2M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z',
    ];
@endphp

<section class="bg-brand-soft/25 border-t border-border">
    <div class="section-wrap">
        <div class="flex items-end justify-between gap-4 mb-10" data-aos="fade-up">
            <div>
                @if($block->get('kicker'))
                    <span class="font-sans text-[10px] tracking-[0.18em] uppercase text-muted font-semibold">{{ $block->get('kicker') }}</span>
                @endif
                @if($block->get('title'))
                    <h2 class="font-serif text-section-title text-fg mt-2">{{ $block->get('title') }}</h2>
                @endif
            </div>
            @if($block->get('link_all_label') && $block->get('link_all_url'))
                <a href="{{ $block->get('link_all_url') }}" class="text-sm font-medium text-brand-primary hover:underline whitespace-nowrap hidden md:inline group">
                    <span>{{ $block->get('link_all_label') }}</span>
                </a>
            @endif
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-{{ min(count($links), 6) }} gap-3">
            @foreach($links as $link)
                <a href="{{ $link['url'] ?? '#' }}" class="group block" data-stagger="quick-link" style="opacity:0;">
                    <div class="card-surface p-5 h-full transition-all duration-300 group-hover:-translate-y-1 group-hover:shadow-lg group-hover:border-brand-accent/40">
                        <div class="w-9 h-9 rounded-md bg-brand-soft/70 flex items-center justify-center mb-4 transition-all duration-300 group-hover:bg-brand-primary group-hover:scale-110 group-hover:rotate-3">
                            <svg class="w-[18px] h-[18px] text-brand-primary transition-colors duration-300 group-hover:text-white" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPaths[$link['icon'] ?? 'plane'] ?? $iconPaths['plane'] }}"/>
                            </svg>
                        </div>
                        <p class="font-sans font-semibold text-[13px] text-fg leading-snug transition-colors duration-300 group-hover:text-brand-primary">{{ $link['label'] ?? '' }}</p>
                        @if(!empty($link['description']))
                            <p class="text-xs text-muted mt-1 leading-relaxed">{{ $link['description'] }}</p>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>
