<div class="ve-fields">
    <div class="ve-section">
        <div class="ve-field">
            <label class="ve-label">Título</label>
            <input type="text" wire:model="editingBlockSettings.title" class="ve-input">
        </div>
        <div class="ve-field">
            <label class="ve-label">Descripción</label>
            <textarea wire:model="editingBlockSettings.subtitle" class="ve-input" rows="3"></textarea>
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
        <div class="ve-grid-2">
            <div class="ve-field">
                <label class="ve-label">Estilo del bloque</label>
                <select wire:model="editingBlockSettings.background" class="ve-input">
                    <option value="navy">Navy (destacado)</option>
                    <option value="primary">Azul primario</option>
                    <option value="soft">Azul suave</option>
                    <option value="card">Blanco con borde</option>
                </select>
            </div>
            <div class="ve-field">
                <label class="ve-label">Alineación</label>
                <select wire:model="editingBlockSettings.align" class="ve-input">
                    <option value="left">Izquierda</option>
                    <option value="center">Centrado</option>
                </select>
            </div>
        </div>
    </div>
</div>
