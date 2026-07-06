<div class="ve-fields">
    <div class="ve-section">

        {{-- ── Selector de convocatoria ──────────────────────────────────── --}}
        <div class="ve-field">
            <label class="ve-label">Convocatoria a mostrar</label>
            <select wire:model.live="editingBlockSettings.convocatoria_id" class="ve-input">
                <option value="">— Auto: la más reciente destacada en home —</option>
                @foreach(\App\Models\Convocatoria::where('status', 'vigente')->orderByDesc('created_at')->get() as $c)
                    <option value="{{ $c->id }}">
                        {{ $c->tipo === 'aviso' ? '📢' : '📋' }}
                        {{ $c->title }}
                        @if($c->tipo === 'proceso' && $c->closes_at)
                            · cierra {{ $c->closes_at->diffForHumans() }}
                        @endif
                    </option>
                @endforeach
            </select>
        </div>

        {{-- ── Info de la convocatoria seleccionada ─────────────────────── --}}
        @php
            $convId = $editingBlockSettings['convocatoria_id'] ?? null;
            $currentConv = $convId
                ? \App\Models\Convocatoria::find($convId)
                : \App\Models\Convocatoria::featured();
            $docCount = is_array($currentConv?->documentos) ? count($currentConv->documentos) : 0;
            if (!$docCount && $currentConv?->bases_pdf) $docCount = 1;
            $convTipo = $currentConv?->tipo ?? 'proceso';
        @endphp

        @if($currentConv)
        <div class="ve-hint" style="padding:10px 12px; background:var(--ve-input-bg); border-radius:8px; border:1px solid var(--ve-border); font-size:12px; line-height:1.6;">
            <div style="font-weight:600; color:var(--ve-primary); margin-bottom:3px;">
                {{ $convTipo === 'aviso' ? '📢 Aviso' : '📋 Proceso' }}: {{ $currentConv->title }}
            </div>
            <div style="color:var(--ve-muted);">
                @if($currentConv->closes_at) Cierre: <strong>{{ $currentConv->closes_at->diffForHumans() }}</strong> · @endif
                @if($docCount) <strong>{{ $docCount }} doc(s)</strong> · @endif
                <a href="/convocatorias/{{ $currentConv->slug }}" target="_blank" style="color:var(--ve-primary);">ver página ↗</a>
            </div>
        </div>
        @else
        <div class="ve-hint" style="padding:10px 12px; background:var(--ve-input-bg); border-radius:8px; border:1px solid var(--ve-border); font-size:12px; color:var(--ve-muted);">
            ℹ Sin convocatoria activa — se muestra el estado "próximamente".
        </div>
        @endif

        {{-- ══ SELECTOR DE DISEÑO — funciona igual que el Hero ══════════════ --}}
        <div class="ve-field" style="margin-top:16px;">
            <h3 class="ve-section-title">Diseño del bloque</h3>

            @if($convTipo === 'proceso')
            {{-- ─ Layouts para PROCESO ──────────────────────────────────── --}}
            <div class="ve-layout-grid">
                @foreach([
                    'split'   => [
                        'label' => 'Split',
                        'desc'  => 'Info + countdown',
                        'svg'   => '<svg viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:26px;height:26px;"><rect x="2" y="4" width="14" height="20" rx="2" fill="currentColor" opacity="0.15"/><rect x="2" y="4" width="14" height="20" rx="2" stroke="currentColor" stroke-width="1.5"/><rect x="18" y="4" width="8" height="9" rx="1.5" fill="currentColor" opacity="0.7"/><rect x="18" y="15" width="8" height="9" rx="1.5" fill="currentColor" opacity="0.3"/></svg>',
                    ],
                    'card'    => [
                        'label' => 'Tarjeta',
                        'desc'  => 'Header navy',
                        'svg'   => '<svg viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:26px;height:26px;"><rect x="2" y="4" width="24" height="20" rx="2" fill="currentColor" opacity="0.08" stroke="currentColor" stroke-width="1.5"/><rect x="2" y="4" width="24" height="8" rx="2" fill="currentColor" opacity="0.65"/><line x1="6" y1="16" x2="15" y2="16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><line x1="6" y1="20" x2="11" y2="20" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>',
                    ],
                    'minimal' => [
                        'label' => 'Minimal',
                        'desc'  => 'Texto limpio',
                        'svg'   => '<svg viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:26px;height:26px;"><rect x="4" y="4" width="3" height="20" rx="1.5" fill="currentColor" opacity="0.7"/><line x1="10" y1="9" x2="24" y2="9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><line x1="10" y1="14" x2="22" y2="14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><line x1="10" y1="19" x2="18" y2="19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>',
                    ],
                ] as $key => $opt)
                <label class="ve-layout-option {{ ($editingBlockSettings['layout_type'] ?? 'split') === $key ? 've-layout-option--active' : '' }}"
                       style="cursor:pointer;">
                    <input type="radio"
                           name="conv_proceso_layout"
                           value="{{ $key }}"
                           wire:model.live="editingBlockSettings.layout_type"
                           class="sr-only">
                    <span class="ve-layout-icon" style="color:var(--ve-primary);">{!! $opt['svg'] !!}</span>
                    <span class="ve-layout-label">{{ $opt['label'] }}</span>
                    <span class="ve-layout-desc">{{ $opt['desc'] }}</span>
                </label>
                @endforeach
            </div>

            @else
            {{-- ─ Layouts para AVISO ────────────────────────────────────── --}}
            <div class="ve-layout-grid">
                @foreach([
                    'poster'  => [
                        'label' => 'Póster',
                        'desc'  => 'Fondo navy',
                        'svg'   => '<svg viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:26px;height:26px;"><rect x="2" y="2" width="24" height="24" rx="2.5" fill="currentColor" opacity="0.65"/><circle cx="14" cy="10" r="3" fill="white" opacity="0.5"/><line x1="8" y1="16" x2="20" y2="16" stroke="white" stroke-width="2" stroke-linecap="round"/><line x1="10" y1="20" x2="18" y2="20" stroke="white" stroke-width="1.5" stroke-linecap="round"/></svg>',
                    ],
                    'banner'  => [
                        'label' => 'Banner',
                        'desc'  => 'Img + texto',
                        'svg'   => '<svg viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:26px;height:26px;"><rect x="2" y="4" width="24" height="20" rx="2" stroke="currentColor" stroke-width="1.5" fill="currentColor" opacity="0.05"/><rect x="2" y="4" width="10" height="20" rx="2" fill="currentColor" opacity="0.5"/><line x1="15" y1="10" x2="24" y2="10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><line x1="15" y1="15" x2="23" y2="15" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/><line x1="15" y1="19" x2="21" y2="19" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/></svg>',
                    ],
                    'minimal' => [
                        'label' => 'Minimal',
                        'desc'  => 'Solo texto',
                        'svg'   => '<svg viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:26px;height:26px;"><rect x="4" y="4" width="3" height="20" rx="1.5" fill="currentColor" opacity="0.7"/><line x1="10" y1="9" x2="24" y2="9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><line x1="10" y1="14" x2="22" y2="14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><line x1="10" y1="19" x2="18" y2="19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>',
                    ],
                ] as $key => $opt)
                <label class="ve-layout-option {{ ($editingBlockSettings['layout_type'] ?? 'poster') === $key ? 've-layout-option--active' : '' }}"
                       style="cursor:pointer;">
                    <input type="radio"
                           name="conv_aviso_layout"
                           value="{{ $key }}"
                           wire:model.live="editingBlockSettings.layout_type"
                           class="sr-only">
                    <span class="ve-layout-icon" style="color:var(--ve-primary);">{!! $opt['svg'] !!}</span>
                    <span class="ve-layout-label">{{ $opt['label'] }}</span>
                    <span class="ve-layout-desc">{{ $opt['desc'] }}</span>
                </label>
                @endforeach
            </div>
            @endif
        </div>

        {{-- ── Toggle ocultar cuando esté cerrada (solo proceso) ─────────── --}}
        @if($convTipo !== 'aviso')
        <div class="ve-field">
            <label class="ve-toggle">
                <input type="checkbox" wire:model.live="editingBlockSettings.hide_when_closed">
                <span>Ocultar cuando el proceso esté cerrado</span>
            </label>
            <p class="ve-hint">Si está activo muestra "Próximamente" al cerrar. Desactivado mantiene visible con badge "Cerrada".</p>
        </div>
        @endif

        {{-- ── Acceso directo ────────────────────────────────────────────── --}}
        <div style="display:flex; gap:8px; flex-wrap:wrap; margin-top:4px;">
            <a href="/admin/convocatorias/create" target="_blank"
               class="ve-btn ve-btn-ghost" style="font-size:12px; text-decoration:none;">
                <svg style="width:13px;height:13px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Nueva convocatoria
            </a>
            @if($currentConv)
            <a href="/admin/convocatorias/{{ $currentConv->id }}/edit" target="_blank"
               class="ve-btn ve-btn-ghost" style="font-size:12px; text-decoration:none;">
                <svg style="width:13px;height:13px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487z"/>
                </svg>
                Editar convocatoria
            </a>
            @endif
        </div>

    </div>
</div>
