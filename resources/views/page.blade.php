@extends('layouts.app', ['title' => $page->meta_title ?: $page->title, 'description' => $page->meta_description, 'editablePage' => $page])

@section('content')
    {{-- Miga de pan solo en paginas internas, no en home.
         Usa el mismo componente de banda que el resto de paginas interiores
         para que todas compartan el estilo de la Propuesta B. --}}
    @if($page->key !== 'home')
        <x-ui.breadcrumb-bar :items="[
            ['label' => $page->title, 'url' => null]
        ]" />
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
