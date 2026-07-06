{{-- MediaPicker — modal global montado en el layout del editor --}}
<div
    x-data
    x-on:open-media-picker-js.window="$wire.openPicker($event.detail.field, $event.detail.type ?? 'image')"
>
    @if($open)
    {{-- Backdrop --}}
    <div class="mp-backdrop" wire:click="close" x-on:keydown.escape.window="$wire.close()"></div>

    {{-- Modal --}}
    <div class="mp-modal" role="dialog" aria-modal="true" @click.stop>

        {{-- Header --}}
        <div class="mp-header">
            <div class="mp-header-left">
                <svg class="mp-header-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <h2 class="mp-title">Galería de medios</h2>
            </div>
            <button wire:click="close" class="mp-close" title="Cerrar">
                <svg viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        </div>

        {{-- Toolbar --}}
        <div class="mp-toolbar">
            <div class="mp-search-wrap">
                <svg class="mp-search-icon" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
                </svg>
                <input type="search"
                       wire:model.live.debounce.300ms="search"
                       class="mp-search"
                       placeholder="Buscar archivos…">
            </div>

            <div class="mp-type-tabs">
                <button wire:click="$set('filterType', 'image')"
                        class="mp-tab {{ $filterType === 'image' ? 'mp-tab--active' : '' }}">
                    Imágenes
                </button>
                <button wire:click="$set('filterType', 'video')"
                        class="mp-tab {{ $filterType === 'video' ? 'mp-tab--active' : '' }}">
                    Videos
                </button>
                <button wire:click="$set('filterType', 'document')"
                        class="mp-tab {{ $filterType === 'document' ? 'mp-tab--active' : '' }}">
                    Documentos
                </button>
            </div>
        </div>

        {{-- Body: grid + upload zone --}}
        <div class="mp-body">

            {{-- Upload zone --}}
            <div class="mp-upload-zone"
                 x-data="{ dragging: false }"
                 x-on:dragover.prevent="dragging = true"
                 x-on:dragleave.prevent="dragging = false"
                 x-on:drop.prevent="dragging = false; $wire.uploadFile = $event.dataTransfer.files[0]">

                <label class="mp-upload-label" :class="{ 'mp-upload--drag': dragging }">
                    <input type="file"
                           class="sr-only"
                           wire:model="uploadFile"
                           accept="image/*,video/*,application/pdf">
                    <svg class="w-7 h-7 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M12 16v-8m0 0l-3 3m3-3l3 3M20 16.5A3.5 3.5 0 0016.5 13h-1.086a5 5 0 10-9.828 0H4.5A3.5 3.5 0 001 16.5"/>
                    </svg>
                    <span class="mp-upload-text">
                        <strong>Arrastra o haz clic</strong> para subir una imagen
                    </span>
                    <span class="mp-upload-hint">JPG, PNG, WebP, PDF — máx 20 MB. Se comprime a WebP.</span>
                </label>

                @if($uploadFile)
                    <div class="mp-upload-preview">
                        <span>{{ $uploadFile->getClientOriginalName() }}</span>
                        <button type="button"
                                wire:click="uploadMedia"
                                wire:loading.attr="disabled"
                                class="ve-btn ve-btn-primary mp-upload-btn">
                            <span wire:loading.remove wire:target="uploadMedia">Subir y usar</span>
                            <span wire:loading wire:target="uploadMedia">Subiendo…</span>
                        </button>
                    </div>
                @endif

                @error('uploadFile')
                    <p class="ve-hint" style="color:var(--ve-danger)">{{ $message }}</p>
                @enderror
            </div>

            {{-- Grid de medios --}}
            @if($mediaItems->isEmpty())
                <div class="mp-empty">
                    <svg class="w-10 h-10 text-muted/50" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-muted text-sm mt-2">
                        @if($search) No se encontraron resultados para "{{ $search }}"
                        @else No hay archivos en esta categoría. Sube uno arriba.
                        @endif
                    </p>
                </div>
            @else
                <div class="mp-grid">
                    @foreach($mediaItems as $item)
                        <button type="button"
                                wire:click="selectMedia({{ $item->id }})"
                                class="mp-item"
                                title="{{ $item->name }}">
                            @if($item->type === 'image')
                                <img src="{{ $item->url }}"
                                     alt="{{ $item->alt_text ?: $item->name }}"
                                     class="mp-item-thumb"
                                     loading="lazy">
                            @elseif($item->type === 'video')
                                <div class="mp-item-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-8 h-8">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                            @else
                                <div class="mp-item-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-8 h-8">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            @endif
                            <span class="mp-item-name">{{ Str::limit($item->name, 22) }}</span>
                            <span class="mp-item-size">{{ $item->size_formatted }}</span>
                        </button>
                    @endforeach
                </div>

                {{-- Paginación --}}
                @if($mediaItems->hasPages())
                    <div class="mp-pagination">
                        <button wire:click="previousPage"
                                @disabled(! $mediaItems->onFirstPage())
                                class="ve-btn ve-btn-ghost mp-page-btn">
                            ← Anterior
                        </button>
                        <span class="ve-hint">{{ $mediaItems->currentPage() }} / {{ $mediaItems->lastPage() }}</span>
                        <button wire:click="nextPage"
                                @disabled(! $mediaItems->hasMorePages())
                                class="ve-btn ve-btn-ghost mp-page-btn">
                            Siguiente →
                        </button>
                    </div>
                @endif
            @endif
        </div>

    </div>
    @endif
</div>
