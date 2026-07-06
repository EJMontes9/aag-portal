<div class="ve-fields">
    <div class="ve-section">
        <div class="ve-field">
            <label class="ve-label">Título de la sección <span style="font-weight:400;color:var(--ve-muted)">(opcional)</span></label>
            <input type="text" wire:model="editingBlockSettings.title" class="ve-input" placeholder="Ej: Cómo llegar">
        </div>
        <div class="ve-field">
            <label class="ve-label">Código embed de Google Maps <span style="color:var(--ve-danger)">*</span></label>
            <textarea wire:model="editingBlockSettings.embed_code"
                      class="ve-input ve-input--mono"
                      rows="5"
                      placeholder="<iframe src=&quot;https://www.google.com/maps/embed?pb=...&quot; width=&quot;600&quot; height=&quot;450&quot; ...></iframe>"></textarea>
            <p class="ve-hint">Google Maps → Compartir → Insertar un mapa → copia el código <code>&lt;iframe&gt;</code> completo.</p>
        </div>
        <div class="ve-grid-2">
            <div class="ve-field">
                <label class="ve-label">Altura del mapa</label>
                <select wire:model="editingBlockSettings.height" class="ve-input">
                    <option value="small">Pequeño (300px)</option>
                    <option value="medium">Mediano (450px)</option>
                    <option value="large">Grande (600px)</option>
                    <option value="full">Pantalla completa</option>
                </select>
            </div>
            <div class="ve-field">
                <label class="ve-label">Fondo</label>
                <select wire:model="editingBlockSettings.background" class="ve-input">
                    <option value="bg">Claro</option>
                    <option value="soft">Azul suave</option>
                    <option value="card">Blanco</option>
                </select>
            </div>
        </div>
    </div>
</div>
