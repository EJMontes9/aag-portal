<div class="ve-fields">
    <div class="ve-section">
        <div class="ve-field">
            <label class="ve-label">Kicker</label>
            <input type="text" wire:model="editingBlockSettings.kicker" class="ve-input">
        </div>
        <div class="ve-field">
            <label class="ve-label">Titulo</label>
            <input type="text" wire:model="editingBlockSettings.title" class="ve-input">
        </div>
        <div class="ve-field">
            <label class="ve-label">Descripcion</label>
            <textarea wire:model="editingBlockSettings.subtitle" class="ve-input" rows="3"></textarea>
        </div>
        <div class="ve-grid-2">
            <div class="ve-field">
                <label class="ve-label">Boton · etiqueta</label>
                <input type="text" wire:model="editingBlockSettings.cta_label" class="ve-input">
            </div>
            <div class="ve-field">
                <label class="ve-label">Boton · URL externa</label>
                <input type="text" wire:model="editingBlockSettings.cta_url" class="ve-input">
            </div>
        </div>
        <div class="ve-field">
            <label class="ve-label">Nota junto al boton</label>
            <input type="text" wire:model="editingBlockSettings.cta_note" class="ve-input">
        </div>
    </div>
</div>
