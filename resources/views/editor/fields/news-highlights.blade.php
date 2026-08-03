<div class="ve-fields">
    <div class="ve-section">
        <h3 class="ve-section-title">Encabezado</h3>
        <div class="ve-field">
            <label class="ve-label">Kicker</label>
            <input type="text" wire:model="editingBlockSettings.kicker" class="ve-input" maxlength="60">
        </div>
        <div class="ve-field">
            <label class="ve-label">Título</label>
            <input type="text" wire:model="editingBlockSettings.title" class="ve-input" maxlength="120">
        </div>
        <div class="ve-field">
            <label class="ve-label">Subtítulo</label>
            <textarea wire:model="editingBlockSettings.subtitle" class="ve-input" rows="2" maxlength="240"></textarea>
        </div>
    </div>

    <div class="ve-section">
        <h3 class="ve-section-title">Fuente y cantidad</h3>
        <div class="ve-grid-2">
            <div class="ve-field">
                <label class="ve-label">Qué mostrar</label>
                <select wire:model="editingBlockSettings.source" class="ve-input">
                    <option value="featured">Solo destacadas</option>
                    <option value="latest">Más recientes</option>
                </select>
            </div>
            <div class="ve-field">
                <label class="ve-label">Cantidad</label>
                <select wire:model="editingBlockSettings.limit" class="ve-input">
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="6">6</option>
                </select>
            </div>
        </div>
        <p class="ve-hint">
            Las noticias se gestionan en <a href="/admin/news" target="_blank" class="ve-link">Noticias</a>.
            Marca como "destacada en home" las que quieras aquí.
        </p>
    </div>

    <div class="ve-section">
        <h3 class="ve-section-title">Enlace al listado</h3>
        <div class="ve-field">
            <label class="ve-toggle">
                <input type="checkbox" wire:model.live="editingBlockSettings.show_view_all">
                <span>Mostrar "Ver todas las noticias"</span>
            </label>
        </div>
        <div class="ve-field">
            <label class="ve-label">Etiqueta del enlace</label>
            <input type="text" wire:model="editingBlockSettings.view_all_label" class="ve-input">
        </div>
    </div>
</div>
