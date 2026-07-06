<div class="ve-root">

    {{-- BARRA SUPERIOR --}}
    <div class="ve-topbar">
        <div class="ve-topbar-left">
            <a href="/admin/pages" class="ve-back" title="Volver al panel">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <span class="ve-topbar-label">Editando</span>
                <h1 class="ve-topbar-title">{{ $page->title }}</h1>
            </div>
        </div>
        <div class="ve-topbar-right">
            <a href="{{ $page->key === 'home' ? url('/') : url('/'.$page->slug) }}" target="_blank" rel="noopener" class="ve-btn ve-btn-ghost" title="Ver pagina publicada">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 3h7v7m0-7L10 14M5 5h4M5 12v7h7"/></svg>
                Ver publico
            </a>
            <a href="/admin/pages/{{ $page->id }}/edit" class="ve-btn ve-btn-ghost">Editor avanzado</a>
        </div>
    </div>

    <div class="ve-canvas">
        @foreach($orderedBlocks as $idx => $block)
            @php
                $viewName = \App\Blocks\BlockRegistry::viewFor($block->type);
                $blockTypeClass = collect(\App\Blocks\BlockRegistry::types())
                    ->first(fn ($c) => $c::key() === $block->type);
                $blockTypeLabel = $blockTypeClass ? $blockTypeClass::label() : $block->type;
            @endphp
            <div class="ve-block @if(! $block->is_active) ve-block-hidden @endif"
                 data-block-id="{{ $block->id }}"
                 wire:key="block-{{ $block->id }}">

                <div class="ve-block-toolbar">
                    <span class="ve-block-label">
                        {{ $blockTypeLabel }}
                    </span>
                    <div class="ve-block-actions">
                        <button type="button" wire:click="moveUp({{ $block->id }})"
                                @disabled($idx === 0)
                                title="Subir" class="ve-iconbtn">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>
                        </button>
                        <button type="button" wire:click="moveDown({{ $block->id }})"
                                @disabled($idx === $orderedBlocks->count() - 1)
                                title="Bajar" class="ve-iconbtn">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <button type="button" wire:click="openBlock({{ $block->id }})"
                                title="Editar contenido" class="ve-iconbtn ve-iconbtn-primary">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125"/></svg>
                            Editar
                        </button>
                        <button type="button" wire:click="toggleVisibility({{ $block->id }})"
                                title="{{ $block->is_active ? 'Ocultar' : 'Mostrar' }}" class="ve-iconbtn">
                            @if($block->is_active)
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                            @else
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                            @endif
                        </button>
                        <button type="button"
                                wire:click="deleteBlock({{ $block->id }})"
                                wire:confirm="¿Eliminar este bloque? No se puede deshacer."
                                title="Eliminar" class="ve-iconbtn ve-iconbtn-danger">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2m2 0v13a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V6h12Z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 11v5M14 11v5"/>
                            </svg>
                        </button>
                    </div>
                </div>

                @if($block->is_active)
                    @php
                        try {
                            $rendered = ($viewName && view()->exists($viewName))
                                ? view($viewName, ['block' => $block, '_inEditor' => true])->render()
                                : '';
                        } catch (\Throwable $e) {
                            $rendered = '';
                        }
                        $isEmpty = trim($rendered) === '';
                    @endphp
                    @if($isEmpty)
                        <div class="ve-block-empty-placeholder">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                            <div>
                                <p class="ve-block-empty-title"><strong>{{ $blockTypeLabel }}</strong> sin contenido</p>
                                <p class="ve-block-empty-desc">Haz clic en <strong>Editar</strong> para configurar este bloque. No se mostrara en la pagina publica hasta que tenga contenido.</p>
                            </div>
                        </div>
                    @else
                        {!! $rendered !!}
                    @endif
                @else
                    <div class="ve-block-hidden-banner">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395"/></svg>
                        <span><strong>{{ $blockTypeLabel }}</strong> está oculto. No se ve en la pagina publica.</span>
                    </div>
                @endif
            </div>
        @endforeach

        <div class="ve-add-block-wrap"
             wire:key="add-block-wrap"
             x-data="{ open: false }"
             @click.outside="open = false"
             @block-added.window="
                 open = false;
                 $nextTick(() => {
                     const el = document.querySelector('[data-block-id=\'' + $event.detail.blockId + '\']');
                     if (el) {
                         el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                         el.classList.add('ve-block-highlight');
                         setTimeout(() => el.classList.remove('ve-block-highlight'), 1800);
                     }
                 });
             ">
            <button type="button" @click="open = !open" class="ve-add-block-btn">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Agregar bloque
            </button>
            <div x-show="open" x-transition x-cloak class="ve-add-block-menu">
                @foreach($this->blockTypes as $bt)
                    <button type="button"
                            wire:click="addBlock('{{ $bt['key'] }}')"
                            wire:loading.attr="disabled"
                            class="ve-add-block-option">
                        {{ $bt['label'] }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- PANEL LATERAL --}}
    <div class="ve-side-panel @if($panelOpen) ve-side-panel-open @endif">
        @if($panelOpen && $editingBlockType)
            @php
                $editorViewMap = [
                    'hero' => 'editor.fields.hero',
                    'banner_slider' => 'editor.fields.banner-slider',
                    'quick_links' => 'editor.fields.quick-links',
                    'news_highlights' => 'editor.fields.news-highlights',
                    'flights' => 'editor.fields.flights',
                    'convocatoria' => 'editor.fields.convocatoria',
                    'values' => 'editor.fields.values',
                    'video' => 'editor.fields.video',
                    'text_image' => 'editor.fields.text-image',
                    'cta' => 'editor.fields.cta',
                    'stats' => 'editor.fields.stats',
                    'faq_accordion' => 'editor.fields.faq-accordion',
                    'transparency_browser' => 'editor.fields.transparency-browser',
                    'map'  => 'editor.fields.map',
                    'form' => 'editor.fields.form',
                ];
                $editorView = $editorViewMap[$editingBlockType] ?? null;
                $blockLabelClass = collect(\App\Blocks\BlockRegistry::types())
                    ->first(fn ($c) => $c::key() === $editingBlockType);
                $blockLabel = $blockLabelClass ? $blockLabelClass::label() : $editingBlockType;
            @endphp
            <div class="ve-panel-header">
                <div>
                    <p class="ve-panel-kicker">EDITAR BLOQUE</p>
                    <h2 class="ve-panel-title">{{ $blockLabel }}</h2>
                </div>
                <button type="button" wire:click="closePanel" class="ve-iconbtn" title="Cerrar">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="ve-panel-body">
                @if($editorView && view()->exists($editorView))
                    @include($editorView)
                @else
                    <p class="ve-panel-empty">Editor visual aún no disponible para este tipo de bloque. Usa el editor avanzado.</p>
                @endif
            </div>
            <div class="ve-panel-footer">
                <button type="button" wire:click="closePanel" class="ve-btn ve-btn-ghost">Cancelar</button>
                <button type="button" wire:click="saveBlock" class="ve-btn ve-btn-primary">
                    <span wire:loading.remove wire:target="saveBlock">Guardar cambios</span>
                    <span wire:loading wire:target="saveBlock">Guardando...</span>
                </button>
            </div>
        @endif
    </div>

    {{-- TOAST con tipos: success / error / info --}}
    <div x-data="{
            show: false,
            type: 'success',
            message: '',
            display(type, message) {
                this.type = type;
                this.message = message;
                this.show = true;
                clearTimeout(this._t);
                this._t = setTimeout(() => this.show = false, type === 'error' ? 4000 : 2200);
            }
         }"
         x-on:editor-toast.window="display($event.detail.type || 'success', $event.detail.message)"
         x-on:block-saved.window="display('success', 'Cambios guardados')"
         x-show="show" x-cloak x-transition
         :class="{
            've-toast': true,
            've-toast-error': type === 'error',
            've-toast-info': type === 'info',
         }">
        <template x-if="type === 'success'">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
        </template>
        <template x-if="type === 'error'">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
        </template>
        <span x-text="message"></span>
    </div>
</div>

@script
<script>
    // Al agregar un bloque, scroll suave hacia el y destaque visual
    Livewire.on('block-added', (event) => {
        const id = Array.isArray(event) ? event[0]?.blockId : event?.blockId;
        if (!id) return;
        setTimeout(() => {
            const el = document.querySelector(`[data-block-id="${id}"]`);
            if (!el) return;
            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            el.classList.add('ve-block-highlight');
            setTimeout(() => el.classList.remove('ve-block-highlight'), 1800);
        }, 120);
    });
</script>
@endscript
