@props(['data'])
<div class="prose prose-lg max-w-none
            prose-headings:font-serif prose-headings:text-fg
            prose-p:text-fg/85 prose-p:leading-[1.75]
            prose-a:text-brand-primary prose-a:no-underline hover:prose-a:underline
            prose-strong:text-fg
            prose-img:rounded-card">
    {!! $data['content'] ?? '' !!}
</div>
