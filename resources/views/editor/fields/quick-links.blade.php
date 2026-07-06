<div class="ve-fields">
    <div class="ve-section">
        <h3 class="ve-section-title">Encabezado</h3>
        <div class="ve-field">
            <label class="ve-label">Kicker</label>
            <input type="text" wire:model="editingBlockSettings.kicker" class="ve-input">
        </div>
        <div class="ve-field">
            <label class="ve-label">Titulo</label>
            <input type="text" wire:model="editingBlockSettings.title" class="ve-input">
        </div>
        <div class="ve-grid-2">
            <div class="ve-field">
                <label class="ve-label">Enlace "Ver todos"</label>
                <input type="text" wire:model="editingBlockSettings.link_all_label" class="ve-input">
            </div>
            <div class="ve-field">
                <label class="ve-label">URL "Ver todos"</label>
                <input type="text" wire:model="editingBlockSettings.link_all_url" class="ve-input">
            </div>
        </div>
    </div>

    <div class="ve-section">
        <h3 class="ve-section-title">Accesos</h3>
        @foreach(($editingBlockSettings['links'] ?? []) as $i => $link)
            <div class="ve-card-mini">
                <p class="ve-mini-title">Acceso #{{ $i + 1 }}</p>
                <div class="ve-grid-2">
                    <div class="ve-field">
                        <label class="ve-label">Icono</label>
                        <select wire:model="editingBlockSettings.links.{{ $i }}.icon" class="ve-input">
                            <option value="plane">Avion</option>
                            <option value="doc">Documento</option>
                            <option value="check">Check</option>
                            <option value="building">Edificio</option>
                            <option value="download">Descarga</option>
                            <option value="phone">Telefono</option>
                            <option value="envelope">Sobre</option>
                            <option value="user">Usuario</option>
                            <option value="globe">Globo</option>
                            <option value="search">Busqueda</option>
                        </select>
                    </div>
                    <div class="ve-field">
                        <label class="ve-label">Etiqueta</label>
                        <input type="text" wire:model="editingBlockSettings.links.{{ $i }}.label" class="ve-input">
                    </div>
                </div>
                <div class="ve-field">
                    <label class="ve-label">Descripcion</label>
                    <input type="text" wire:model="editingBlockSettings.links.{{ $i }}.description" class="ve-input">
                </div>
                <div class="ve-field">
                    <label class="ve-label">URL</label>
                    <input type="text" wire:model="editingBlockSettings.links.{{ $i }}.url" class="ve-input">
                </div>
            </div>
        @endforeach
        <p class="ve-hint">Para agregar/eliminar accesos usa el "Editor avanzado" desde la barra superior.</p>
    </div>
</div>
