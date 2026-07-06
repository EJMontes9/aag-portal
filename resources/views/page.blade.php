@extends('layouts.app', ['title' => $page->meta_title ?: $page->title, 'description' => $page->meta_description, 'editablePage' => $page])

@section('content')
    {{-- Breadcrumbs solo en paginas internas, no en home --}}
    @if($page->key !== 'home')
        <div class="bg-bg border-b border-border">
            <div class="section-wrap !py-4">
                <x-layout.breadcrumbs :items="[
                    ['label' => $page->title, 'url' => null]
                ]" />
            </div>
        </div>
    @endif

    @foreach($page->activeBlocks as $block)
        @php
            $viewName = \App\Blocks\BlockRegistry::viewFor($block->type);
        @endphp
        @if($viewName && view()->exists($viewName))
            @include($viewName, ['block' => $block])
        @endif
    @endforeach
@endsection
