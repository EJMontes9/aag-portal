<article class="group flex flex-col">
    <a href="{{ route('news.show', $item->slug) }}" class="block aspect-[16/10] bg-brand-soft/30 rounded-card overflow-hidden">
        @if($item->cover_url)
            <img src="{{ $item->cover_url }}"
                 alt="{{ $item->cover_image_alt ?: $item->title }}"
                 loading="lazy"
                 class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
        @else
            <div class="w-full h-full flex items-center justify-center text-brand-primary/40">
                <svg class="w-16 h-16" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                </svg>
            </div>
        @endif
    </a>
    <div class="mt-4 flex flex-col flex-1">
        @if($item->category)
            <span class="font-sans text-[10px] tracking-[0.18em] uppercase font-semibold"
                  style="color: {{ $item->category->color ?: 'rgb(var(--color-primary))' }};">
                {{ $item->category->name }}
            </span>
        @endif
        <h3 class="font-serif text-xl text-fg mt-2 leading-tight" style="font-weight:400;">
            <a href="{{ route('news.show', $item->slug) }}" class="hover:text-brand-primary transition-colors">
                {{ $item->title }}
            </a>
        </h3>
        @if($item->excerpt)
            <p class="mt-3 text-sm text-muted leading-[1.6] line-clamp-3">{{ $item->excerpt }}</p>
        @endif
        <div class="mt-4 flex items-center gap-3 text-xs text-muted">
            <time datetime="{{ $item->published_at?->toIso8601String() }}">
                {{ $item->published_at?->translatedFormat('d \\d\\e F, Y') }}
            </time>
            <span aria-hidden="true">·</span>
            <span>{{ $item->reading_time }} min de lectura</span>
        </div>
    </div>
</article>
