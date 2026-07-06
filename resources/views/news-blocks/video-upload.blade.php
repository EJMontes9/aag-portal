@props(['data'])
@php
    if (empty($data['video'])) return;
    $videoUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($data['video']);
    $posterUrl = !empty($data['poster'])
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($data['poster'])
        : null;
@endphp
<figure>
    <video controls
           preload="metadata"
           @if($posterUrl) poster="{{ $posterUrl }}" @endif
           class="w-full rounded-card bg-black aspect-video">
        <source src="{{ $videoUrl }}">
        Tu navegador no soporta video HTML5.
    </video>
    @if(!empty($data['caption']))
        <figcaption class="mt-3 text-sm text-muted text-center italic leading-snug">
            {{ $data['caption'] }}
        </figcaption>
    @endif
</figure>
