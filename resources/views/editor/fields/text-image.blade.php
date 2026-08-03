<div class="ve-fields">
    <div class="ve-section">
        <div class="ve-field">
            <label class="ve-label">Kicker (opcional)</label>
            <input type="text" wire:model="editingBlockSettings.kicker" class="ve-input">
        </div>
        <div class="ve-field">
            <label class="ve-label">Título</label>
            <input type="text" wire:model="editingBlockSettings.title" class="ve-input">
        </div>
        <div class="ve-field">
            <label class="ve-label">Texto</label>
            <textarea wire:model="editingBlockSettings.body" class="ve-input" rows="6"></textarea>
        </div>
        <div class="ve-grid-2">
            <div class="ve-field">
                <label class="ve-label">Lado de la imagen</label>
                <select wire:model="editingBlockSettings.image_side" class="ve-input">
                    <option value="right">Derecha</option>
                    <option value="left">Izquierda</option>
                </select>
            </div>
            <div class="ve-field">
                <label class="ve-label">Fondo</label>
                <select wire:model="editingBlockSettings.background" class="ve-input">
                    <option value="bg">Claro</option>
                    <option value="soft">Azul suave</option>
                    <option value="card">Blanco</option>
                </select>
            </div>
        </div>
        <div class="ve-grid-2">
            <div class="ve-field">
                <label class="ve-label">Botón · etiqueta</label>
                <input type="text" wire:model="editingBlockSettings.cta_label" class="ve-input">
            </div>
            <div class="ve-field">
                <label class="ve-label">Botón · URL</label>
                <input type="text" wire:model="editingBlockSettings.cta_url" class="ve-input">
            </div>
        </div>
    </div>

    {{-- ── Imagen ──────────────────────────────────────────────────────── --}}
    <div class="ve-section">
        <h3 class="ve-section-title">Imagen del bloque</h3>

        @if(!empty($editingBlockSettings['image']))
            {{-- Preview de imagen actual --}}
            <div class="ve-img-preview-wrap">
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($editingBlockSettings['image']) }}"
                     alt="Imagen actual"
                     class="ve-img-preview">
                <button type="button"
                        wire:click="clearBlockImage('image')"
                        class="ve-img-remove"
                        title="Quitar imagen">
                    <svg viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                    Quitar imagen
                </button>
            </div>
        @else
            {{-- Upload + Galería --}}
            <div x-data="{ uploading: false, progress: 0 }"
                 x-on:livewire-upload-start="uploading = true"
                 x-on:livewire-upload-finish="$wire.uploadBlockImage('image'); uploading = false; progress = 0"
                 x-on:livewire-upload-cancel="uploading = false"
                 x-on:livewire-upload-error="uploading = false"
                 x-on:livewire-upload-progress="progress = $event.detail.progress">

                <div class="ve-media-actions">
                    <label class="ve-btn ve-btn-ghost ve-media-upload-btn" style="cursor:pointer;">
                        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M3 16.5v1.75A.75.75 0 003.75 19h12.5a.75.75 0 00.75-.75V16.5M16 9l-4-4-4 4M12 4.5v9"/>
                        </svg>
                        Subir imagen
                        <input type="file"
                               accept="image/*"
                               wire:model="blockImage"
                               class="sr-only">
                    </label>

                    <button type="button"
                            class="ve-btn ve-btn-ghost"
                            @click="window.dispatchEvent(new CustomEvent('open-media-picker-js', { detail: { field: 'image', type: 'image' } }))">
                        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Desde galería
                    </button>
                </div>

                <p x-show="uploading" class="ve-hint" style="margin-top:6px;">
                    Subiendo y comprimiendo… <span x-text="progress + '%'"></span>
                </p>

                @error('blockImage')
                    <p class="ve-hint" style="color:var(--ve-danger); margin-top:4px;">{{ $message }}</p>
                @enderror

                <p class="ve-hint" style="margin-top:6px;">
                    Las imágenes se comprimen automáticamente a WebP (máx 1920px).
                    Si prefieres un mapa, deja la imagen vacía y pega el embed abajo.
                </p>
            </div>
        @endif
    </div>

    {{-- ── Mapa (alternativa a imagen) ─────────────────────────────────── --}}
    <div class="ve-section">
        <h3 class="ve-section-title">
            Mapa (alternativa a imagen)
            <span style="font-weight:400;font-size:11px;color:var(--ve-muted)"> — se usa si no hay imagen</span>
        </h3>
        <div class="ve-field">
            <textarea wire:model="editingBlockSettings.map_embed"
                      class="ve-input ve-input--mono"
                      rows="4"
                      placeholder="<iframe src=&quot;https://www.google.com/maps/embed?pb=...&quot; ...></iframe>"></textarea>
            <p class="ve-hint">Google Maps → Compartir → Insertar un mapa → copia el código iframe completo.</p>
        </div>
    </div>
</div>
