<div class="ve-fields">
    <div class="ve-section">
        <h3 class="ve-section-title">Seccion</h3>
        <div class="ve-field">
            <label class="ve-label">Que seccion mostrar</label>
            <select wire:model="editingBlockSettings.section" class="ve-input">
                <option value="lotaip">LOTAIP / Transparencia</option>
                <option value="rendicion">Rendicion de cuentas</option>
            </select>
            <p class="ve-hint">El bloque mostrara solo los años y meses asignados a esta seccion.</p>
        </div>
    </div>

    <div class="ve-section">
        <h3 class="ve-section-title">Encabezado</h3>
        <div class="ve-field">
            <label class="ve-label">Kicker</label>
            <input type="text" wire:model="editingBlockSettings.kicker" class="ve-input" maxlength="60">
        </div>
        <div class="ve-field">
            <label class="ve-label">Titulo</label>
            <input type="text" wire:model="editingBlockSettings.title" class="ve-input" maxlength="160">
        </div>
        <div class="ve-field">
            <label class="ve-label">Texto introductorio</label>
            <textarea wire:model="editingBlockSettings.intro" class="ve-input" rows="6" maxlength="1000"></textarea>
            <p class="ve-hint">Aparece arriba del navegador. Soporta saltos de linea.</p>
        </div>
    </div>

    <div class="ve-section">
        <p class="ve-hint">
            La estructura de años, meses y documentos se gestiona en
            <a href="/admin/lotaip-years" target="_blank" class="ve-link">Años</a>,
            <a href="/admin/lotaip-months" target="_blank" class="ve-link">Meses</a> y
            <a href="/admin/lotaip-documents" target="_blank" class="ve-link">Documentos</a>.
        </p>
    </div>
</div>
