<div class="ve-fields">
    <div class="ve-section">

        {{-- Selector de formulario + acciones --}}
        <div class="ve-field">
            <label class="ve-label">Formulario</label>
            <select wire:model="editingBlockSettings.form_id" class="ve-input">
                <option value="">— Seleccionar formulario —</option>
                @foreach(\App\Models\Form::where('is_active', true)->orderBy('name')->get() as $f)
                    <option value="{{ $f->id }}">{{ $f->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Accesos directos al admin de formularios --}}
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <a href="/admin/forms/create"
               target="_blank"
               class="ve-btn ve-btn-ghost"
               style="font-size:12px;text-decoration:none;">
                <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Crear formulario
            </a>
            @if(!empty($editingBlockSettings['form_id']))
                <a href="/admin/forms/{{ $editingBlockSettings['form_id'] }}/edit"
                   target="_blank"
                   class="ve-btn ve-btn-ghost"
                   style="font-size:12px;text-decoration:none;">
                    <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487z"/>
                    </svg>
                    Editar formulario
                </a>
            @endif
        </div>

        <div class="ve-field">
            <label class="ve-label">Título de la sección (opcional)</label>
            <input type="text" wire:model="editingBlockSettings.section_title" class="ve-input"
                   placeholder="Ej: Escríbenos">
        </div>

        <div class="ve-field">
            <label class="ve-label">Descripción (opcional)</label>
            <textarea wire:model="editingBlockSettings.section_description" class="ve-input" rows="3"
                      placeholder="Breve texto introductorio sobre el formulario…"></textarea>
        </div>

        <div class="ve-field">
            <label class="ve-label">Diseño</label>
            <select wire:model="editingBlockSettings.layout" class="ve-input">
                <option value="centered">Centrado</option>
                <option value="full">Ancho completo</option>
                <option value="split">Dos columnas (info + form)</option>
            </select>
        </div>

    </div>
</div>
