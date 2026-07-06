<div class="ve-fields">
    <div class="ve-section">
        <h3 class="ve-section-title">Encabezado</h3>
        <div class="ve-field">
            <label class="ve-label">Kicker</label>
            <input type="text" wire:model="editingBlockSettings.kicker" class="ve-input" maxlength="60">
        </div>
        <div class="ve-field">
            <label class="ve-label">Titulo</label>
            <input type="text" wire:model="editingBlockSettings.title" class="ve-input" maxlength="120">
        </div>
        <div class="ve-field">
            <label class="ve-label">Subtitulo</label>
            <textarea wire:model="editingBlockSettings.subtitle" class="ve-input" rows="2" maxlength="240"></textarea>
        </div>
    </div>

    <div class="ve-section">
        <h3 class="ve-section-title">Fuente</h3>
        <div class="ve-grid-2">
            <div class="ve-field">
                <label class="ve-label">Que preguntas mostrar</label>
                <select wire:model.live="editingBlockSettings.source" class="ve-input">
                    <option value="featured">Destacadas</option>
                    <option value="category">De una categoria</option>
                    <option value="all">Todas (primeras N)</option>
                </select>
            </div>
            <div class="ve-field">
                <label class="ve-label">Cantidad maxima</label>
                <input type="number" min="1" max="20" wire:model="editingBlockSettings.limit" class="ve-input">
            </div>
        </div>

        @if(($editingBlockSettings['source'] ?? null) === 'category')
            <div class="ve-field">
                <label class="ve-label">Categoria</label>
                <select wire:model="editingBlockSettings.category_id" class="ve-input">
                    <option value="">Seleccionar...</option>
                    @foreach(\App\Models\FaqCategory::orderBy('sort_order')->get() as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <p class="ve-hint">Las preguntas se gestionan en <a href="/admin/faqs" target="_blank" class="ve-link">Preguntas frecuentes</a>.</p>
    </div>

    <div class="ve-section">
        <h3 class="ve-section-title">Enlace al listado</h3>
        <div class="ve-field">
            <label class="ve-toggle">
                <input type="checkbox" wire:model.live="editingBlockSettings.show_view_all">
                <span>Mostrar enlace a la pagina /faq</span>
            </label>
        </div>
        <div class="ve-field">
            <label class="ve-label">Etiqueta del enlace</label>
            <input type="text" wire:model="editingBlockSettings.view_all_label" class="ve-input">
        </div>
    </div>
</div>
