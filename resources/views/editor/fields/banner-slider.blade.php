<div class="ve-fields">
    <div class="ve-section">
        <h3 class="ve-section-title">Comportamiento</h3>
        <div class="ve-grid-2">
            <div class="ve-field">
                <label class="ve-label">Rotacion automatica</label>
                <label class="ve-toggle">
                    <input type="checkbox" wire:model.live="editingBlockSettings.autoplay">
                    <span>Activar autoplay</span>
                </label>
            </div>
            <div class="ve-field">
                <label class="ve-label">Intervalo (segundos)</label>
                <input type="number" min="2" max="30" wire:model="editingBlockSettings.interval" class="ve-input">
            </div>
            <div class="ve-field">
                <label class="ve-label">Indicadores</label>
                <label class="ve-toggle">
                    <input type="checkbox" wire:model.live="editingBlockSettings.show_indicators">
                    <span>Mostrar puntos</span>
                </label>
            </div>
            <div class="ve-field">
                <label class="ve-label">Flechas</label>
                <label class="ve-toggle">
                    <input type="checkbox" wire:model.live="editingBlockSettings.show_arrows">
                    <span>Mostrar flechas</span>
                </label>
            </div>
            <div class="ve-field" style="grid-column: span 2;">
                <label class="ve-label">Altura</label>
                <select wire:model="editingBlockSettings.height" class="ve-input">
                    <option value="small">Pequeño (50vh)</option>
                    <option value="medium">Mediano (70vh)</option>
                    <option value="large">Grande (85vh)</option>
                    <option value="full">Pantalla completa (100vh)</option>
                </select>
            </div>
        </div>
    </div>

    <div class="ve-section">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
            <h3 class="ve-section-title" style="margin:0;">Slides ({{ count($editingBlockSettings['slides'] ?? []) }})</h3>
            <button type="button"
                    wire:click="addRepeaterItem('slides', { title: 'Nuevo slide', subtitle: '', cta_label: '', cta_url: '', image: null, overlay: 'medium', align: 'left' })"
                    class="ve-btn ve-btn-primary"
                    style="padding:6px 12px; font-size:12px;">
                + Agregar slide
            </button>
        </div>

        @forelse(($editingBlockSettings['slides'] ?? []) as $i => $slide)
            <div class="ve-card-mini" wire:key="slide-{{ $i }}">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                    <p class="ve-mini-title" style="margin:0;">Slide #{{ $i + 1 }}</p>
                    <div style="display:flex; gap:4px;">
                        <button type="button"
                                wire:click="moveRepeaterItem('slides', {{ $i }}, -1)"
                                @disabled($i === 0)
                                title="Subir"
                                class="ve-iconbtn-mini">↑</button>
                        <button type="button"
                                wire:click="moveRepeaterItem('slides', {{ $i }}, 1)"
                                @disabled($i === count($editingBlockSettings['slides']) - 1)
                                title="Bajar"
                                class="ve-iconbtn-mini">↓</button>
                        <button type="button"
                                wire:click="removeRepeaterItem('slides', {{ $i }})"
                                wire:confirm="¿Eliminar este slide?"
                                title="Eliminar"
                                class="ve-iconbtn-mini ve-iconbtn-danger-mini">×</button>
                    </div>
                </div>

                {{-- Imagen --}}
                <div class="ve-field">
                    <label class="ve-label">Imagen de fondo</label>
                    @if(!empty($slide['image']))
                        <div class="ve-image-preview">
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($slide['image']) }}"
                                 alt="Slide {{ $i + 1 }}"
                                 style="max-width:100%; max-height:120px; border-radius:6px; display:block;">
                            <button type="button"
                                    wire:click="clearSlideImage('slides', {{ $i }})"
                                    class="ve-btn ve-btn-ghost"
                                    style="margin-top:6px; padding:4px 10px; font-size:11px;">
                                Quitar imagen
                            </button>
                        </div>
                    @else
                        <div wire:key="upload-slide-{{ $i }}"
                             x-data="{ uploading: false, progress: 0 }"
                             x-on:livewire-upload-start="uploading = true"
                             x-on:livewire-upload-finish="$wire.uploadSlideImage('slides', {{ $i }}); uploading = false; progress = 0"
                             x-on:livewire-upload-cancel="uploading = false"
                             x-on:livewire-upload-error="uploading = false; progress = 0"
                             x-on:livewire-upload-progress="progress = $event.detail.progress">
                            <input type="file"
                                   accept="image/*"
                                   wire:model="slideImage"
                                   class="ve-input"
                                   style="padding:6px;">
                            <p x-show="uploading" class="ve-hint" style="margin-top:4px;">
                                Subiendo... <span x-text="progress + '%'"></span>
                            </p>
                            @error('slideImage')
                                <p class="ve-hint" style="color:#dc2626; margin-top:4px;">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif
                </div>

                <div class="ve-field">
                    <label class="ve-label">Titulo</label>
                    <input type="text"
                           wire:model="editingBlockSettings.slides.{{ $i }}.title"
                           class="ve-input"
                           maxlength="120">
                </div>

                <div class="ve-field">
                    <label class="ve-label">Subtitulo</label>
                    <textarea wire:model="editingBlockSettings.slides.{{ $i }}.subtitle"
                              class="ve-input"
                              rows="2"
                              maxlength="240"></textarea>
                </div>

                <div class="ve-grid-2">
                    <div class="ve-field">
                        <label class="ve-label">Boton - etiqueta</label>
                        <input type="text"
                               wire:model="editingBlockSettings.slides.{{ $i }}.cta_label"
                               class="ve-input">
                    </div>
                    <div class="ve-field">
                        <label class="ve-label">Boton - URL</label>
                        <input type="text"
                               wire:model="editingBlockSettings.slides.{{ $i }}.cta_url"
                               class="ve-input">
                    </div>
                </div>

                <div class="ve-grid-2">
                    <div class="ve-field">
                        <label class="ve-label">Overlay</label>
                        <select wire:model="editingBlockSettings.slides.{{ $i }}.overlay" class="ve-input">
                            <option value="none">Sin overlay</option>
                            <option value="light">Suave (30%)</option>
                            <option value="medium">Medio (50%)</option>
                            <option value="strong">Fuerte (70%)</option>
                        </select>
                    </div>
                    <div class="ve-field">
                        <label class="ve-label">Alineacion</label>
                        <select wire:model="editingBlockSettings.slides.{{ $i }}.align" class="ve-input">
                            <option value="left">Izquierda</option>
                            <option value="center">Centro</option>
                            <option value="right">Derecha</option>
                        </select>
                    </div>
                </div>
            </div>
        @empty
            <div class="ve-empty-mini">
                <p class="ve-hint">No hay slides. Haz clic en "Agregar slide" para empezar.</p>
            </div>
        @endforelse
    </div>
</div>
