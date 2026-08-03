<div class="ve-fields">
    <div class="ve-section">
        <div class="ve-field">
            <label class="ve-label">Kicker</label>
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
        <div class="ve-grid-2">
            <div class="ve-field">
                <label class="ve-label">Botón · etiqueta</label>
                <input type="text" wire:model="editingBlockSettings.cta_label" class="ve-input">
            </div>
            <div class="ve-field">
                <label class="ve-label">Botón · URL externa</label>
                <input type="text" wire:model="editingBlockSettings.cta_url" class="ve-input">
            </div>
        </div>
        <div class="ve-field">
            <label class="ve-label">Nota junto al botón</label>
            <input type="text" wire:model="editingBlockSettings.cta_note" class="ve-input">
        </div>
    </div>
</div>
