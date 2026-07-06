@props([
    'variant' => 'auto',
    'showText' => true,
    'tone' => 'light',
    'eager' => true, // false para el logo del footer: esta bajo el pliegue, no compite por LCP
])

@php
    $logoLight = setting_asset('site_logo');
    $logoDark = setting_asset('site_logo_dark');
    $siteName = settings('site_name', 'AAG');

    $titleColor = $tone === 'on-navy' ? 'text-on-navy' : 'text-fg';
    $subtitleColor = $tone === 'on-navy' ? 'text-on-navy/60' : 'text-muted';
    $markBg = $tone === 'on-navy' ? 'bg-white' : 'bg-brand-navy';
    $triangleColor = $tone === 'on-navy' ? 'rgb(var(--color-navy))' : 'rgb(var(--color-accent))';
@endphp

<a href="/" {{ $attributes->merge(['class' => 'flex items-center gap-3']) }}>
    @if($logoLight)
        <img src="{{ $logoLight }}" alt="{{ $siteName }}"
             loading="{{ $eager ? 'eager' : 'lazy' }}" decoding="async" @if($eager) fetchpriority="high" @endif
             class="h-11 w-auto @if($logoDark) dark:hidden @endif">
        @if($logoDark)
            <img src="{{ $logoDark }}" alt="{{ $siteName }}"
                 loading="{{ $eager ? 'eager' : 'lazy' }}" decoding="async"
                 class="h-11 w-auto hidden dark:block">
        @endif
    @else
        {{-- Logo fallback: triangulo AAG --}}
        <span class="inline-flex items-center justify-center w-10 h-10 rounded-md {{ $markBg }} shrink-0">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M12 3L21 20H3L12 3Z" fill="{{ $triangleColor }}" />
            </svg>
        </span>
    @endif

    @if($showText)
        <span class="flex flex-col leading-[1.15]">
            <span class="font-sans font-bold {{ $titleColor }} text-[14px]">Autoridad Aeroportuaria</span>
            <span class="font-sans text-[10px] tracking-[0.22em] uppercase {{ $subtitleColor }} font-semibold">DE GUAYAQUIL</span>
        </span>
    @endif
</a>
