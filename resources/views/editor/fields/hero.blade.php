<div class="ve-fields">

    {{-- ── Selector de diseño ────────────────────────────────────────── --}}
    <div class="ve-section">
        <h3 class="ve-section-title">Diseño del hero</h3>
        <div class="ve-field">
            <div class="ve-layout-grid">
                @foreach([
                    'editorial' => ['label' => 'Editorial', 'desc' => 'Texto + tarjetas', 'icon' => '📰'],
                    'centered'  => ['label' => 'Centrado',  'desc' => 'Texto grande',     'icon' => '✦'],
                    'split'     => ['label' => 'Partido',   'desc' => 'Texto + imagen',   'icon' => '▥'],
                    'banner'    => ['label' => 'Banner',    'desc' => 'Fondo de imagen',  'icon' => '🖼'],
                ] as $key => $opt)
                <label class="ve-layout-option {{ ($editingBlockSettings['layout'] ?? 'editorial') === $key ? 've-layout-option--active' : '' }}">
                    <input type="radio" name="hero_layout" value="{{ $key }}"
                           wire:model.live="editingBlockSettings.layout" class="sr-only">
                    <span class="ve-layout-icon">{{ $opt['icon'] }}</span>
                    <span class="ve-layout-label">{{ $opt['label'] }}</span>
                    <span class="ve-layout-desc">{{ $opt['desc'] }}</span>
                </label>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── Encabezado ─────────────────────────────────────────────────── --}}
    <div class="ve-section">
        <h3 class="ve-section-title">Encabezado</h3>
        <div class="ve-grid-2">
            <div class="ve-field">
                <label class="ve-label">Pill de estado</label>
                <input type="text" wire:model="editingBlockSettings.pill" class="ve-input"
                       placeholder="Aeropuerto operando con normalidad">
            </div>
            <div class="ve-field">
                <label class="ve-label">Color del pill</label>
                <select wire:model="editingBlockSettings.pill_tone" class="ve-input">
                    <option value="success">🟢 Verde (operativo)</option>
                    <option value="warn">🟡 Ámbar (precaución)</option>
                    <option value="neutral">⚪ Neutral</option>
                    <option value="soft">🔵 Azul suave</option>
                </select>
            </div>
        </div>
        <div class="ve-field">
            <label class="ve-label">Titular <span style="color:var(--ve-muted);font-weight:400;">— usa *palabra* para cursiva</span></label>
            <textarea wire:model="editingBlockSettings.h1" class="ve-input" rows="3"></textarea>
        </div>
        <div class="ve-field">
            <label class="ve-label">Descripción</label>
            <textarea wire:model="editingBlockSettings.subtitle" class="ve-input" rows="3"></textarea>
        </div>
        <div class="ve-grid-2">
            <div class="ve-field">
                <label class="ve-label">Botón primario · etiqueta</label>
                <input type="text" wire:model="editingBlockSettings.cta1_label" class="ve-input">
            </div>
            <div class="ve-field">
                <label class="ve-label">Botón primario · URL</label>
                <input type="text" wire:model="editingBlockSettings.cta1_url" class="ve-input" placeholder="#">
            </div>
            <div class="ve-field">
                <label class="ve-label">Botón secundario · etiqueta</label>
                <input type="text" wire:model="editingBlockSettings.cta2_label" class="ve-input">
            </div>
            <div class="ve-field">
                <label class="ve-label">Botón secundario · URL</label>
                <input type="text" wire:model="editingBlockSettings.cta2_url" class="ve-input" placeholder="#">
            </div>
        </div>
    </div>

    {{-- ── Métricas ─────────────────────────────────────────────────── --}}
    <div class="ve-section">
        <h3 class="ve-section-title">Métricas</h3>
        @foreach(($editingBlockSettings['stats'] ?? []) as $i => $stat)
            <div class="ve-grid-2 ve-mb-2">
                <div class="ve-field">
                    <label class="ve-label">Valor #{{ $i + 1 }}</label>
                    <input type="text" wire:model="editingBlockSettings.stats.{{ $i }}.value" class="ve-input" placeholder="8.2M">
                </div>
                <div class="ve-field">
                    <label class="ve-label">Etiqueta</label>
                    <input type="text" wire:model="editingBlockSettings.stats.{{ $i }}.label" class="ve-input" placeholder="Pasajeros al año">
                </div>
            </div>
        @endforeach
    </div>

    {{-- ── Tarjetas (solo editorial) ───────────────────────────────── --}}
    @if(($editingBlockSettings['layout'] ?? 'editorial') === 'editorial')
    <div class="ve-section">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
            <h3 class="ve-section-title" style="margin:0;">Tarjetas laterales ({{ count($editingBlockSettings['cards'] ?? []) }}/3)</h3>
            @if(count($editingBlockSettings['cards'] ?? []) < 3)
            <button type="button"
                    wire:click="addRepeaterItem('cards', { variant: 'surface', kicker: '', title: '', image: null, meta: '', cta_label: '', cta_url: '' })"
                    class="ve-btn ve-btn-primary" style="padding:5px 10px;font-size:12px;">
                + Agregar tarjeta
            </button>
            @endif
        </div>
        <p class="ve-hint" style="margin-bottom:10px;">1 tarjeta de imagen grande (izquierda) + hasta 2 tarjetas apiladas (derecha).</p>

        @forelse(($editingBlockSettings['cards'] ?? []) as $i => $card)
        <div class="ve-card-mini" wire:key="hero-card-{{ $i }}">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                <p class="ve-mini-title" style="margin:0;">
                    Tarjeta #{{ $i + 1 }}
                    @if(($card['variant'] ?? '') === 'image') — 🖼 Imagen
                    @elseif(($card['variant'] ?? '') === 'primary') — 🔵 Azul
                    @else — ⬜ Blanca
                    @endif
                </p>
                <div style="display:flex;gap:4px;">
                    <button type="button" wire:click="moveRepeaterItem('cards',{{ $i }},-1)" @disabled($i===0) class="ve-iconbtn-mini">↑</button>
                    <button type="button" wire:click="moveRepeaterItem('cards',{{ $i }},1)" @disabled($i===count($editingBlockSettings['cards'])-1) class="ve-iconbtn-mini">↓</button>
                    <button type="button" wire:click="removeRepeaterItem('cards',{{ $i }})" wire:confirm="¿Eliminar esta tarjeta?" class="ve-iconbtn-mini ve-iconbtn-danger-mini">×</button>
                </div>
            </div>

            <div class="ve-field">
                <label class="ve-label">Tipo de tarjeta</label>
                <select wire:model.live="editingBlockSettings.cards.{{ $i }}.variant" class="ve-input">
                    <option value="image">🖼 Imagen grande (columna izquierda, span 2 filas)</option>
                    <option value="primary">🔵 Azul primaria (destacada)</option>
                    <option value="surface">⬜ Blanca / neutra</option>
                </select>
            </div>

            {{-- Imagen (solo tipo image) --}}
            @if(($card['variant'] ?? 'surface') === 'image')
            <div class="ve-field">
                <label class="ve-label">Imagen de fondo</label>
                @if(!empty($card['image']))
                    <div class="ve-img-preview-wrap">
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($card['image']) }}"
                             alt="Tarjeta {{ $i+1 }}" class="ve-img-preview">
                        <button type="button" wire:click="clearSlideImage('cards',{{ $i }},'image')"
                                class="ve-img-remove">✕ Quitar imagen</button>
                    </div>
                @else
                    <div x-data="{ uploading:false, progress:0 }"
                         x-on:livewire-upload-start="uploading=true"
                         x-on:livewire-upload-finish="$wire.uploadSlideImage('cards',{{ $i }},'image','hero-cards'); uploading=false; progress=0"
                         x-on:livewire-upload-error="uploading=false"
                         x-on:livewire-upload-progress="progress=$event.detail.progress">
                        <div class="ve-media-actions">
                            <label class="ve-btn ve-btn-ghost ve-media-upload-btn" style="cursor:pointer;">
                                <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 16.5v1.75A.75.75 0 003.75 19h12.5a.75.75 0 00.75-.75V16.5M16 9l-4-4-4 4M12 4.5v9"/>
                                </svg>
                                Subir imagen
                                <input type="file" accept="image/*" wire:model="slideImage"
                                       class="sr-only">
                            </label>
                            <button type="button" class="ve-btn ve-btn-ghost"
                                    @click="window.dispatchEvent(new CustomEvent('open-media-picker-js',{detail:{field:'cards.{{ $i }}.image',type:'image'}}))">
                                <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                Desde galería
                            </button>
                        </div>
                        <p x-show="uploading" class="ve-hint" style="margin-top:4px;">Subiendo… <span x-text="progress+'%'"></span></p>
                    </div>
                @endif
            </div>
            @endif

            <div class="ve-grid-2">
                <div class="ve-field">
                    <label class="ve-label">Kicker</label>
                    <input type="text" wire:model="editingBlockSettings.cards.{{ $i }}.kicker" class="ve-input" placeholder="CONVOCATORIA">
                </div>
                <div class="ve-field">
                    <label class="ve-label">Título</label>
                    <input type="text" wire:model="editingBlockSettings.cards.{{ $i }}.title" class="ve-input">
                </div>
                <div class="ve-field">
                    <label class="ve-label">Meta (fecha, categoría…)</label>
                    <input type="text" wire:model="editingBlockSettings.cards.{{ $i }}.meta" class="ve-input" placeholder="Hasta el 28 de abril">
                </div>
                <div class="ve-field">
                    <label class="ve-label">Enlace · etiqueta</label>
                    <input type="text" wire:model="editingBlockSettings.cards.{{ $i }}.cta_label" class="ve-input" placeholder="Ver detalles →">
                </div>
                <div class="ve-field" style="grid-column:span 2">
                    <label class="ve-label">Enlace · URL</label>
                    <input type="text" wire:model="editingBlockSettings.cards.{{ $i }}.cta_url" class="ve-input" placeholder="/convocatorias/slug">
                </div>
            </div>
        </div>
        @empty
            <div class="ve-empty-mini"><p class="ve-hint">No hay tarjetas. Haz clic en "+ Agregar tarjeta".</p></div>
        @endforelse
    </div>
    @endif

    {{-- ── Imagen lateral (solo split) ─────────────────────────────── --}}
    @if(($editingBlockSettings['layout'] ?? 'editorial') === 'split')
    <div class="ve-section">
        <h3 class="ve-section-title">Imagen lateral derecha</h3>
        @if(!empty($editingBlockSettings['side_image']))
            <div class="ve-img-preview-wrap">
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($editingBlockSettings['side_image']) }}"
                     alt="Imagen lateral" class="ve-img-preview">
                <button type="button" wire:click="clearBlockImage('side_image')" class="ve-img-remove">✕ Quitar</button>
            </div>
        @else
            <div class="ve-media-actions"
                 x-data="{ uploading:false }"
                 x-on:livewire-upload-start="uploading=true"
                 x-on:livewire-upload-finish="$wire.uploadBlockImage('side_image'); uploading=false"
                 x-on:livewire-upload-error="uploading=false">
                <label class="ve-btn ve-btn-ghost ve-media-upload-btn" style="cursor:pointer;">
                    <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 16.5v1.75A.75.75 0 003.75 19h12.5a.75.75 0 00.75-.75V16.5M16 9l-4-4-4 4M12 4.5v9"/>
                    </svg>
                    Subir imagen
                    <input type="file" accept="image/*" wire:model="blockImage" class="sr-only">
                </label>
                <button type="button" class="ve-btn ve-btn-ghost"
                        @click="window.dispatchEvent(new CustomEvent('open-media-picker-js',{detail:{field:'side_image',type:'image'}}))">
                    <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Desde galería
                </button>
            </div>
        @endif
    </div>
    @endif

    {{-- ── Fondo / imagen (banner) ──────────────────────────────────── --}}
    @if(($editingBlockSettings['layout'] ?? 'editorial') === 'banner')
    <div class="ve-section">
        <h3 class="ve-section-title">Imagen de fondo</h3>
        @if(!empty($editingBlockSettings['background_image']))
            <div class="ve-img-preview-wrap">
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($editingBlockSettings['background_image']) }}"
                     alt="Fondo banner" class="ve-img-preview">
                <button type="button" wire:click="clearBlockImage('background_image')" class="ve-img-remove">✕ Quitar</button>
            </div>
        @else
            <div x-data="{ uploading:false, uploadErr:'' }"
                 x-on:livewire-upload-start="uploading=true; uploadErr=''"
                 x-on:livewire-upload-finish="$wire.uploadBlockImage('background_image').then(() => { uploading=false })"
                 x-on:livewire-upload-error="uploading=false; uploadErr='Error al subir el archivo. Intenta con Desde galería.'">
                <div class="ve-media-actions">
                    <label class="ve-btn ve-btn-ghost ve-media-upload-btn" style="cursor:pointer;">
                        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 16.5v1.75A.75.75 0 003.75 19h12.5a.75.75 0 00.75-.75V16.5M16 9l-4-4-4 4M12 4.5v9"/>
                        </svg>
                        <span x-show="!uploading">Subir imagen de fondo</span>
                        <span x-show="uploading">Subiendo…</span>
                        <input type="file" accept="image/*" wire:model="blockImage" class="sr-only">
                    </label>
                    <button type="button" class="ve-btn ve-btn-ghost"
                            @click="window.dispatchEvent(new CustomEvent('open-media-picker-js',{detail:{field:'background_image',type:'image'}}))">
                        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Desde galería
                    </button>
                </div>
                <p x-show="uploadErr" x-text="uploadErr" style="color:#dc2626;font-size:12px;margin-top:6px;"></p>
                @error('blockImage')
                    <p class="ve-hint" style="color:#dc2626;margin-top:4px;">{{ $message }}</p>
                @enderror
            </div>
        @endif
        <div class="ve-grid-2" style="margin-top:12px;">
            <div class="ve-field">
                <label class="ve-label">Oscuridad del overlay</label>
                <select wire:model="editingBlockSettings.bg_overlay" class="ve-input">
                    <option value="light">Suave (20%)</option>
                    <option value="medium">Medio (50%)</option>
                    <option value="dark">Fuerte (70%)</option>
                </select>
            </div>
            <div class="ve-field">
                <label class="ve-label">Alineación del texto</label>
                <select wire:model="editingBlockSettings.text_align" class="ve-input">
                    <option value="center">Centrado</option>
                    <option value="left">Izquierda</option>
                </select>
            </div>
        </div>
    </div>
    @endif

    {{-- ── Fondo (centrado) ─────────────────────────────────────────── --}}
    @if(($editingBlockSettings['layout'] ?? 'editorial') === 'centered')
    <div class="ve-section">
        <h3 class="ve-section-title">Fondo y alineación</h3>
        <div class="ve-grid-2">
            <div class="ve-field">
                <label class="ve-label">Color de fondo</label>
                <select wire:model="editingBlockSettings.bg_color" class="ve-input">
                    <option value="light">Claro (bg)</option>
                    <option value="soft">Azul suave</option>
                    <option value="navy">Navy oscuro</option>
                    <option value="gradient">Degradado azul</option>
                </select>
            </div>
            <div class="ve-field">
                <label class="ve-label">Alineación del texto</label>
                <select wire:model="editingBlockSettings.text_align" class="ve-input">
                    <option value="center">Centrado</option>
                    <option value="left">Izquierda</option>
                </select>
            </div>
        </div>
    </div>
    @endif

</div>
