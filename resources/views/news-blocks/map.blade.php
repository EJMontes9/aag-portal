@props(['data'])
@php
    $raw = trim($data['embed_url'] ?? '');
    if (! $raw) return;

    // Si pegaron el iframe completo, extraemos el src
    if (preg_match('~<iframe[^>]+src="([^"]+)"~i', $raw, $m)) {
        $src = $m[1];
    } else {
        $src = $raw;
    }

    // Validacion basica de URL para evitar XSS
    if (! filter_var($src, FILTER_VALIDATE_URL) || ! str_starts_with($src, 'https://')) return;
    if (! str_contains($src, 'google.com/maps') && ! str_contains($src, 'openstreetmap')) return;

    $heightClass = match($data['height'] ?? 'md') {
        'sm' => 'h-[300px]',
        'lg' => 'h-[600px]',
        default => 'h-[450px]',
    };
@endphp
<figure>
    <div class="rounded-card overflow-hidden border border-border {{ $heightClass }}">
        <iframe src="{{ $src }}"
                title="{{ $data['caption'] ?? 'Mapa' }}"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                allowfullscreen
                class="w-full h-full border-0"></iframe>
    </div>
    @if(!empty($data['caption']))
        <figcaption class="mt-3 text-sm text-muted text-center italic leading-snug">
            {{ $data['caption'] }}
        </figcaption>
    @endif
</figure>
