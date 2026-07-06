@props(['data'])
@php
    $url = trim($data['url'] ?? '');
    $embed = null;
    if ($url) {
        if (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/embed/)([\w-]+)~', $url, $m)) {
            $embed = 'https://www.youtube.com/embed/'.$m[1];
        } elseif (preg_match('~vimeo\.com/(\d+)~', $url, $m)) {
            $embed = 'https://player.vimeo.com/video/'.$m[1];
        }
    }
    if (! $embed) return;
@endphp
<figure>
    <div class="relative aspect-video rounded-card overflow-hidden bg-black">
        <iframe src="{{ $embed }}"
                title="{{ $data['caption'] ?? 'Video' }}"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen
                loading="lazy"
                referrerpolicy="strict-origin-when-cross-origin"
                class="absolute inset-0 w-full h-full"></iframe>
    </div>
    @if(!empty($data['caption']))
        <figcaption class="mt-3 text-sm text-muted text-center italic leading-snug">
            {{ $data['caption'] }}
        </figcaption>
    @endif
</figure>
