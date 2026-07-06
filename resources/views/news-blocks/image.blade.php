@props(['data'])
@php
    if (empty($data['image'])) return;
    $src = \Illuminate\Support\Facades\Storage::disk('public')->url($data['image']);
    $width = $data['width'] ?? 'content';
    $wrapperClass = match($width) {
        'wide' => 'max-w-5xl mx-auto',
        'full' => 'w-screen relative left-1/2 right-1/2 -ml-[50vw] -mr-[50vw]',
        default => '',
    };
@endphp
<figure class="{{ $wrapperClass }}">
    <img src="{{ $src }}"
         alt="{{ $data['alt'] ?? '' }}"
         loading="lazy"
         class="w-full h-auto {{ $width === 'full' ? '' : 'rounded-card' }}">
    @if(!empty($data['caption']))
        <figcaption class="mt-3 text-sm text-muted text-center italic leading-snug">
            {{ $data['caption'] }}
        </figcaption>
    @endif
</figure>
