@php
    $user     = auth()->user();
    $canEdit  = $user && $user->hasAnyRole(['super_admin', 'admin', 'editor', 'publisher']);
    $editorUrl = isset($editablePage)
        ? route('visual-editor', ['page' => $editablePage->id])
        : null;
    $advancedUrl = isset($editablePage)
        ? url('/admin/pages/' . $editablePage->id . '/edit')
        : url('/admin');
@endphp

@if($canEdit)
<div
    x-data="{ visible: !sessionStorage.getItem('editorToolbarHidden') }"
    x-show="visible"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-4"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-4"
    class="editor-toolbar"
    role="toolbar"
    aria-label="Barra de edición"
>
    {{-- Indicador de modo edición --}}
    <div class="editor-toolbar__mode">
        <span class="editor-toolbar__dot"></span>
        <span class="editor-toolbar__mode-text">Modo editor</span>
    </div>

    {{-- Separador --}}
    <div class="editor-toolbar__sep"></div>

    {{-- Nombre de la página actual --}}
    @if(isset($editablePage))
        <span class="editor-toolbar__page-name">
            <svg viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5 opacity-60">
                <path fill-rule="evenodd" d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z" clip-rule="evenodd"/>
            </svg>
            {{ $editablePage->title }}
        </span>
        <div class="editor-toolbar__sep"></div>
    @endif

    {{-- Acciones --}}
    <div class="editor-toolbar__actions">

        @if($editorUrl)
        {{-- Editor Visual --}}
        <a href="{{ $editorUrl }}" class="editor-toolbar__btn editor-toolbar__btn--primary">
            <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                      d="M11 5H6a2 2 0 00-2 2v7a2 2 0 002 2h7a2 2 0 002-2v-5m-6 4l8.5-8.5M15 3l2 2"/>
            </svg>
            Editar página
        </a>
        @endif

        {{-- Panel Admin --}}
        <a href="{{ url('/admin') }}" class="editor-toolbar__btn editor-toolbar__btn--ghost" target="_blank">
            <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                      d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Panel admin
        </a>

        {{-- Avatar + nombre --}}
        <div class="editor-toolbar__user">
            <div class="editor-toolbar__avatar">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <span class="editor-toolbar__username">{{ explode(' ', $user->name)[0] }}</span>
        </div>

        {{-- Cerrar / ocultar --}}
        <button
            @click="visible = false; sessionStorage.setItem('editorToolbarHidden', '1')"
            class="editor-toolbar__close"
            title="Ocultar barra (se restaura al recargar)"
        >
            <svg viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
        </button>
    </div>
</div>
@endif
