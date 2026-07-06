@php
    $formId   = $block->settings['form_id']             ?? null;
    $title    = $block->settings['section_title']       ?? null;
    $desc     = $block->settings['section_description'] ?? null;
    $layout   = $block->settings['layout']              ?? 'centered';
    $bgColor  = $block->settings['bg_color']            ?? null;
    $_preview = $_inEditor ?? false;

    $wrapClass = match($layout) {
        'full'  => 'w-full',
        'split' => 'max-w-5xl mx-auto',
        default => 'max-w-2xl mx-auto',
    };
@endphp

{{-- ══ PREVIEW EN EDITOR VISUAL ════════════════════════════════════════════ --}}
@if($_preview)
@php
    $form   = $formId ? \App\Models\Form::with('activeFields')->find($formId) : null;
    $fields = $form ? $form->activeFields : collect();
@endphp

<section @if($bgColor) style="background-color:{{ $bgColor }};" @endif>
    <div class="section-wrap">

        {{-- Cabecera de sección --}}
        @if($title || $desc)
            <div class="text-center mb-10">
                @if($title)
                    <h2 class="text-3xl md:text-4xl font-serif font-semibold text-fg mb-3">{{ $title }}</h2>
                @endif
                @if($desc)
                    <p class="text-muted text-lg max-w-xl mx-auto">{{ $desc }}</p>
                @endif
            </div>
        @endif

        <div class="{{ $wrapClass }}">

            @if(!$form)
                {{-- Sin formulario configurado --}}
                <div class="rounded-2xl border-2 border-dashed border-indigo-300 bg-indigo-50 p-10 text-center">
                    <svg class="w-10 h-10 text-indigo-400 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                    </svg>
                    <p class="text-sm font-semibold text-indigo-700 mb-1">Formulario sin configurar</p>
                    <p class="text-xs text-indigo-500">Haz clic en <strong>Editar</strong> para seleccionar un formulario.</p>
                </div>
            @else
                {{-- Preview fiel al form-renderer --}}
                <div class="rounded-2xl border border-border bg-card shadow-sm overflow-hidden" style="pointer-events:none;">
                    <div class="p-6 md:p-8 space-y-6">
                        @foreach($fields as $field)
                            @php $key = $field->field_key; @endphp
                            <div>
                                <label class="block text-sm font-medium text-fg mb-1.5">
                                    {{ $field->label }}
                                    @if($field->required)
                                        <span class="text-red-500 ml-0.5">*</span>
                                    @endif
                                </label>

                                @if($field->type === 'textarea')
                                    <textarea
                                        rows="4"
                                        placeholder="{{ $field->placeholder }}"
                                        disabled
                                        class="w-full rounded-lg border border-border px-3.5 py-2.5 text-sm bg-bg text-fg placeholder-muted/60 resize-y"
                                    ></textarea>

                                @elseif($field->type === 'select')
                                    <select disabled class="w-full rounded-lg border border-border px-3.5 py-2.5 text-sm bg-bg text-fg cursor-pointer">
                                        <option>{{ $field->placeholder ?: '— Selecciona una opción —' }}</option>
                                        @foreach($field->options ?? [] as $opt)
                                            <option>{{ $opt['label'] }}</option>
                                        @endforeach
                                    </select>

                                @elseif($field->type === 'radio')
                                    <div class="space-y-2 mt-1">
                                        @foreach($field->options ?? [] as $opt)
                                            <label class="flex items-center gap-2.5">
                                                <input type="radio" disabled class="w-4 h-4 text-brand-primary border-border">
                                                <span class="text-sm text-fg">{{ $opt['label'] }}</span>
                                            </label>
                                        @endforeach
                                    </div>

                                @elseif($field->type === 'checkbox')
                                    <label class="flex items-start gap-3 mt-1">
                                        <input type="checkbox" disabled class="mt-0.5 w-4 h-4 rounded border-border flex-shrink-0">
                                        <span class="text-sm text-fg leading-relaxed">{{ $field->placeholder ?: $field->label }}</span>
                                    </label>

                                @else
                                    <input
                                        type="{{ $field->type }}"
                                        placeholder="{{ $field->placeholder }}"
                                        disabled
                                        class="w-full rounded-lg border border-border px-3.5 py-2.5 text-sm bg-bg text-fg placeholder-muted/60"
                                    >
                                @endif

                                @if($field->help_text)
                                    <p class="mt-1.5 text-xs text-muted">{{ $field->help_text }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    {{-- Footer igual al form-renderer --}}
                    <div class="px-6 md:px-8 pb-6 md:pb-8 pt-2 flex items-center justify-between gap-4">
                        <p class="text-xs text-muted">
                            <span class="text-red-500">*</span> Campos obligatorios
                        </p>
                        <button type="button" disabled
                            class="inline-flex items-center gap-2 rounded-lg bg-brand-primary px-6 py-2.5 text-sm font-semibold text-white shadow-sm opacity-90">
                            {{ $form->submit_label ?: 'Enviar' }}
                        </button>
                    </div>
                </div>
            @endif

        </div>
    </div>
</section>

{{-- ══ PÁGINA PÚBLICA ═══════════════════════════════════════════════════════ --}}
@elseif($formId)
<section @if($bgColor) style="background-color:{{ $bgColor }};" @endif>
    <div class="section-wrap">
        @if($title || $desc)
            <div class="text-center mb-10">
                @if($title)
                    <h2 class="text-3xl md:text-4xl font-serif font-semibold text-fg mb-3">{{ $title }}</h2>
                @endif
                @if($desc)
                    <p class="text-muted text-lg max-w-xl mx-auto">{{ $desc }}</p>
                @endif
            </div>
        @endif
        <div class="{{ $wrapClass }}">
            @livewire('form-renderer', ['formId' => (int) $formId], key('form-'.$formId))
        </div>
    </div>
</section>
@endif
