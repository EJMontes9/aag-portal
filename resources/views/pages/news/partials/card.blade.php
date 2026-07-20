{{-- Card estilo Propuesta B: imagen · tag · título bold navy · fecha DD.MM.YYYY --}}
<article class="group flex flex-col bg-card border border-border overflow-hidden transition-shadow duration-300 hover:shadow-md"
         style="border-radius: var(--radius-card);">
    <a href="{{ route('news.show', $item->slug) }}" class="block aspect-[16/10] overflow-hidden">
        @if($item->cover_url)
            <img src="{{ $item->cover_url }}"
                 alt="{{ $item->cover_image_alt ?: $item->title }}"
                 loading="lazy"
                 class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
        @else
            <div class="w-full h-full bg-brand-soft/30 flex items-center justify-center text-brand-primary/30">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 1 1.5 1.5Z"/>
                </svg>
            </div>
        @endif
    </a>
    <div class="p-4 flex flex-col flex-1">
        @if($item->category)
            <span class="font-sans text-[10px] tracking-[0.12em] uppercase font-bold mb-2"
                  style="color: {{ $item->category->color ?: 'rgb(var(--color-primary))' }};">
                {{ $item->category->name }}
            </span>
        @endif
        <h3 class="font-sans font-bold text-[14px] text-brand-navy leading-snug flex-1">
            <a href="{{ route('news.show', $item->slug) }}" class="hover:text-brand-primary transition-colors">
                {{ $item->title }}
            </a>
        </h3>
        <div class="mt-3 text-[11px] text-muted">
            <time datetime="{{ $item->published_at?->toIso8601String() }}">
                {{ $item->published_at?->format('d.m.Y') }}
            </time>
            <span aria-hidden="true"> · </span>
            <span>Por {{ $item->author?->name ?? 'AAG' }}</span>
        </div>
    </div>
</article>
