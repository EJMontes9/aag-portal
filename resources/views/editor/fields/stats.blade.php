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
        <div class="ve-field">
            <label class="ve-label">Fondo</label>
            <select wire:model="editingBlockSettings.background" class="ve-input">
                <option value="bg">Claro</option>
                <option value="soft">Azul suave</option>
                <option value="navy">Navy</option>
            </select>
        </div>
    </div>
    <div class="ve-section">
        <h3 class="ve-section-title">Estadisticas</h3>
        @foreach(($editingBlockSettings['items'] ?? []) as $i => $item)
            <div class="ve-grid-2 ve-mb-2">
                <div class="ve-field">
                    <label class="ve-label">Valor #{{ $i + 1 }}</label>
                    <input type="text" wire:model="editingBlockSettings.items.{{ $i }}.value" class="ve-input">
                </div>
                <div class="ve-field">
                    <label class="ve-label">Etiqueta</label>
                    <input type="text" wire:model="editingBlockSettings.items.{{ $i }}.label" class="ve-input">
                </div>
            </div>
        @endforeach
    </div>
</div>
