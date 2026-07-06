@props(['data'])
@php
    $text = trim($data['text'] ?? '');
    if (! $text) return;
@endphp
<blockquote class="border-l-4 border-brand-accent pl-6 md:pl-8 py-2">
    <p class="font-serif text-2xl md:text-3xl text-fg leading-[1.35] italic" style="font-weight:400;">
        &ldquo;{{ $text }}&rdquo;
    </p>
    @if(!empty($data['author']) || !empty($data['source']))
        <footer class="mt-4 text-sm text-muted">
            @if(!empty($data['author']))
                <strong class="text-fg font-medium not-italic">{{ $data['author'] }}</strong>
            @endif
            @if(!empty($data['source']))
                <span class="block mt-0.5">{{ $data['source'] }}</span>
            @endif
        </footer>
    @endif
</blockquote>
