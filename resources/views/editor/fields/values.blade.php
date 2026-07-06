<div class="ve-fields">
    <div class="ve-section">
        <h3 class="ve-section-title">Encabezado</h3>
        <div class="ve-field">
            <label class="ve-label">Kicker</label>
            <input type="text" wire:model="editingBlockSettings.kicker" class="ve-input">
        </div>
        <div class="ve-field">
            <label class="ve-label">Titulo (usa *cursivas*)</label>
            <textarea wire:model="editingBlockSettings.title" class="ve-input" rows="2"></textarea>
        </div>
        <div class="ve-field">
            <label class="ve-label">Descripcion</label>
            <textarea wire:model="editingBlockSettings.subtitle" class="ve-input" rows="3"></textarea>
        </div>
    </div>

    <div class="ve-section">
        <h3 class="ve-section-title">Valores</h3>
        @foreach(($editingBlockSettings['items'] ?? []) as $i => $item)
            <div class="ve-card-mini">
                <p class="ve-mini-title">Valor #{{ $i + 1 }}</p>
                <div class="ve-grid-3">
                    <div class="ve-field">
                        <label class="ve-label">Numero</label>
                        <input type="text" wire:model="editingBlockSettings.items.{{ $i }}.number" class="ve-input" maxlength="4">
                    </div>
                    <div class="ve-field" style="grid-column: span 2;">
                        <label class="ve-label">Titulo</label>
                        <input type="text" wire:model="editingBlockSettings.items.{{ $i }}.title" class="ve-input">
                    </div>
                </div>
                <div class="ve-field">
                    <label class="ve-label">Descripcion</label>
                    <textarea wire:model="editingBlockSettings.items.{{ $i }}.description" class="ve-input" rows="2"></textarea>
                </div>
            </div>
        @endforeach
    </div>
</div>
