<div class="ve-fields">

    {{-- ── Contenido ─────────────────────────────────────────────────────── --}}
    <div class="ve-section">
        <h3 class="ve-section-title">Contenido</h3>
        <div class="ve-field">
            <label class="ve-label">Kicker (opcional)</label>
            <input type="text" wire:model="editingBlockSettings.kicker" class="ve-input">
        </div>
        <div class="ve-field">
            <label class="ve-label">Título</label>
            <input type="text" wire:model="editingBlockSettings.title" class="ve-input">
        </div>
        <div class="ve-field">
            <label class="ve-label">Descripción</label>
            <textarea wire:model="editingBlockSettings.subtitle" class="ve-input" rows="3"></textarea>
        </div>
        <div class="ve-field">
            <label class="ve-label">URL del video</label>
            <input type="url" wire:model="editingBlockSettings.video_url" class="ve-input"
                   placeholder="https://youtu.be/... o https://vimeo.com/...">
            <p class="ve-hint">Pega la URL de YouTube o Vimeo.</p>
        </div>
        <div class="ve-field">
            <label class="ve-label">Fondo de la sección</label>
            <select wire:model="editingBlockSettings.background" class="ve-input">
                <option value="bg">Normal (claro)</option>
                <option value="soft">Azul suave</option>
                <option value="navy">Navy (oscuro)</option>
            </select>
        </div>
    </div>

    {{-- ── Inicio en tiempo específico ────────────────────────────────────── --}}
    <div class="ve-section">
        <h3 class="ve-section-title">Iniciar desde…</h3>
        <p class="ve-hint" style="margin-bottom:10px;">Deja en 0 para iniciar desde el principio.</p>
        <div class="ve-grid-2">
            <div class="ve-field">
                <label class="ve-label">Minutos</label>
                <input type="number" min="0"
                       wire:model.live="editingBlockSettings.start_min"
                       class="ve-input" placeholder="0">
            </div>
            <div class="ve-field">
                <label class="ve-label">Segundos <span style="color:var(--ve-muted)">(0–59)</span></label>
                <input type="number" min="0" max="59"
                       wire:model.live="editingBlockSettings.start_sec"
                       class="ve-input" placeholder="0">
            </div>
        </div>
        @php
            $min   = (int)($editingBlockSettings['start_min'] ?? 0);
            $sec   = (int)($editingBlockSettings['start_sec'] ?? 0);
            $total = $min * 60 + $sec;
        @endphp
        @if($total > 0)
            <p class="ve-hint" style="color:var(--ve-primary); margin-top:6px;">
                ▶ El video empezará en {{ $min > 0 ? "{$min}m " : '' }}{{ $sec > 0 ? "{$sec}s" : '' }} ({{ $total }} segundos desde el inicio)
            </p>
        @endif
    </div>

    {{-- ── Comportamiento del reproductor ─────────────────────────────────── --}}
    <div class="ve-section">
        <h3 class="ve-section-title">Comportamiento del reproductor</h3>

        <div class="ve-field">
            <label class="ve-toggle">
                <input type="checkbox"
                       wire:model.live="editingBlockSettings.autoplay"
                       x-on:change="if ($event.target.checked) { $wire.set('editingBlockSettings.mute', true) }">
                <span>Reproducción automática al cargar la página</span>
            </label>
            <p class="ve-hint">Al activar, el video se silencia automáticamente (requisito de los navegadores).</p>
        </div>

        <div class="ve-field">
            <label class="ve-toggle">
                <input type="checkbox" wire:model="editingBlockSettings.mute">
                <span>Silenciado al inicio</span>
            </label>
            <p class="ve-hint">El visitante puede activar el sonido con el botón del reproductor.</p>
        </div>

        <div class="ve-field">
            <label class="ve-toggle">
                <input type="checkbox" wire:model="editingBlockSettings.loop">
                <span>Repetir en bucle</span>
            </label>
            <p class="ve-hint">El video vuelve a empezar automáticamente cuando termina.</p>
        </div>

        <div class="ve-field">
            <label class="ve-toggle">
                <input type="checkbox" wire:model="editingBlockSettings.controls">
                <span>Mostrar barra de controles del reproductor</span>
            </label>
            <p class="ve-hint">Si lo desactivas, el visitante no podrá pausar ni ajustar el volumen.</p>
        </div>
    </div>

    {{-- ── Opciones específicas de YouTube ─────────────────────────────────── --}}
    <div class="ve-section">
        <h3 class="ve-section-title">Opciones de YouTube</h3>

        <div class="ve-field">
            <label class="ve-toggle">
                <input type="checkbox" wire:model="editingBlockSettings.modestbranding">
                <span>Ocultar logo de YouTube en la barra de controles</span>
            </label>
        </div>

        <div class="ve-field">
            <label class="ve-toggle">
                <input type="checkbox" wire:model="editingBlockSettings.rel">
                <span>Mostrar videos relacionados al terminar</span>
            </label>
            <p class="ve-hint">Desactívalo para evitar que los visitantes sean llevados a otros videos al terminar.</p>
        </div>
    </div>

</div>
