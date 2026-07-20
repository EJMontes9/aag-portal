@props(['data'])
@php
    $images = collect($data['images'] ?? [])->filter(fn ($i) => !empty($i['image']))->values();
    if ($images->isEmpty()) return;
    $cols = (int) ($data['columns'] ?? 3);
    $gridClass = match($cols) {
        2 => 'sm:grid-cols-2',
        4 => 'sm:grid-cols-2 lg:grid-cols-4',
        default => 'sm:grid-cols-2 lg:grid-cols-3',
    };
@endphp
<div x-data="{
        open: false,
        idx: 0,
        images: @js($images->map(fn ($i) => [
            'src' => \Illuminate\Support\Facades\Storage::disk('public')->url($i['image']),
            'alt' => $i['alt'] ?? '',
            'caption' => $i['caption'] ?? '',
        ])->values()->all()),
        show(i) { this.idx = i; this.open = true; document.body.style.overflow = 'hidden'; },
        close() { this.open = false; document.body.style.overflow = ''; },
        next() { this.idx = (this.idx + 1) % this.images.length; },
        prev() { this.idx = (this.idx - 1 + this.images.length) % this.images.length; },
     }"
     @keydown.escape.window="if (open) close()"
     @keydown.arrow-right.window="if (open) next()"
     @keydown.arrow-left.window="if (open) prev()">

    {{-- Grid de miniaturas --}}
    <div class="grid grid-cols-1 {{ $gridClass }} gap-3">
        @foreach($images as $i => $img)
            <button type="button"
                    @click="show({{ $i }})"
                    class="group block aspect-square overflow-hidden rounded-card bg-brand-soft/30">
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($img['image']) }}"
                     alt="{{ $img['alt'] ?? '' }}"
                     loading="lazy"
                     {{-- Sin escalado en hover: B no anima la miniatura --}}
                     class="w-full h-full object-cover">
            </button>
        @endforeach
    </div>

    {{-- Lightbox --}}
    <div x-show="open"
         x-cloak
         x-transition.opacity
         class="fixed inset-0 z-[100] bg-black/90 flex items-center justify-center"
         @click.self="close()">

        <button type="button"
                @click="close()"
                aria-label="Cerrar"
                class="absolute top-5 right-5 w-11 h-11 rounded-pill bg-white/15 hover:bg-white/30 text-white flex items-center justify-center transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        <button type="button"
                @click="prev()"
                aria-label="Imagen anterior"
                x-show="images.length > 1"
                class="absolute left-5 top-1/2 -translate-y-1/2 w-12 h-12 rounded-pill bg-white/15 hover:bg-white/30 text-white flex items-center justify-center transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
        </button>

        <button type="button"
                @click="next()"
                aria-label="Imagen siguiente"
                x-show="images.length > 1"
                class="absolute right-5 top-1/2 -translate-y-1/2 w-12 h-12 rounded-pill bg-white/15 hover:bg-white/30 text-white flex items-center justify-center transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
        </button>

        <div class="max-w-[90vw] max-h-[85vh] flex flex-col items-center" @click.stop>
            <img :src="images[idx]?.src" :alt="images[idx]?.alt" class="max-w-full max-h-[80vh] object-contain rounded-card">
            <p x-show="images[idx]?.caption" x-text="images[idx]?.caption"
               class="mt-3 text-white/85 text-sm text-center max-w-2xl"></p>
            <p class="mt-2 text-white/60 text-xs">
                <span x-text="idx + 1"></span> / <span x-text="images.length"></span>
            </p>
        </div>
    </div>
</div>
