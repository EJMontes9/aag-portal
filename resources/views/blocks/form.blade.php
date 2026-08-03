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

    // Clases de campo compartidas por input/textarea/select del preview. Formas de
    // B: borde marcado de 1px, radio 2px y CERO sombra -- el foco se marca con el
    // borde celeste, no con un halo.
    // El valor que el ciudadano teclea tiene que leerse más que su etiqueta: 14px
    // y algo más de alto de campo (py-2.5) para no dejarlo pegado al borde.
    $fieldClass = 'w-full rounded-pill border border-border bg-card px-3 py-2.5 text-[14px] text-fg placeholder-muted/70';
@endphp

{{-- ══ PREVIEW EN EDITOR VISUAL ════════════════════════════════════════════ --}}
@if($_preview)
@php
    $form   = $formId ? \App\Models\Form::with('activeFields')->find($formId) : null;
    $fields = $form ? $form->activeFields : collect();
@endphp

<section class="bg-bg" @if($bgColor) style="background-color:{{ $bgColor }};" @endif>
    <div class="section-wrap">

        {{-- Cabecera dentro de la MISMA columna que el formulario: si se dejara
             fuera, con layout 'centered' el título quedaría a la izquierda de la
             página y la caja centrada, sin eje común. En B el texto se alinea a
             la izquierda de su columna, no al centro. --}}
        <div class="{{ $wrapClass }}">

            @if($title || $desc)
                <header class="mb-8">
                    @if($title)
                        <h2 class="font-serif text-section-title text-brand-navy">{{ $title }}</h2>
                    @endif
                    @if($desc)
                        {{-- La descripción suele explicar qué se envía y a quién:
                             es texto de lectura, no un pie de campo. --}}
                        <p class="mt-3 text-[15px] text-muted leading-relaxed">{{ $desc }}</p>
                    @endif
                </header>
            @endif

            @if(!$form)
                {{-- Sin formulario configurado. Es chrome de editor, pero se pinta
                     con los tokens del sitio para no introducir una paleta ajena. --}}
                <div class="rounded-card border border-dashed border-brand-primary bg-brand-soft/40 p-8 text-center">
                    <svg class="w-8 h-8 text-brand-primary mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                    </svg>
                    <p class="font-sans text-[12px] font-bold uppercase tracking-[0.14em] text-brand-navy mb-1.5">Formulario sin configurar</p>
                    <p class="text-[13px] text-muted">Haz clic en <strong class="text-brand-navy">Editar</strong> para seleccionar un formulario.</p>
                </div>
            @else
                {{-- Preview fiel al form-renderer --}}
                <div class="card-surface overflow-hidden" style="pointer-events:none;">
                    <div class="p-5 md:p-7 space-y-5">
                        @foreach($fields as $field)
                            @php $key = $field->field_key; @endphp
                            <div>
                                <label class="block font-sans text-[12px] font-bold uppercase tracking-[0.08em] text-brand-navy mb-2">
                                    {{ $field->label }}
                                    @if($field->required)
                                        <span class="text-[#B3261E] ml-0.5">*</span>
                                    @endif
                                </label>

                                @if($field->type === 'textarea')
                                    <textarea
                                        rows="4"
                                        placeholder="{{ $field->placeholder }}"
                                        disabled
                                        class="{{ $fieldClass }} resize-y"
                                    ></textarea>

                                @elseif($field->type === 'select')
                                    <select disabled class="{{ $fieldClass }} cursor-pointer">
                                        <option>{{ $field->placeholder ?: '— Selecciona una opción —' }}</option>
                                        @foreach($field->options ?? [] as $opt)
                                            <option>{{ $opt['label'] }}</option>
                                        @endforeach
                                    </select>

                                @elseif($field->type === 'radio')
                                    <div class="space-y-1.5 mt-1">
                                        @foreach($field->options ?? [] as $opt)
                                            <label class="flex items-center gap-2.5">
                                                <input type="radio" disabled class="w-4 h-4 text-brand-primary border-border">
                                                <span class="text-[14px] text-fg">{{ $opt['label'] }}</span>
                                            </label>
                                        @endforeach
                                    </div>

                                @elseif($field->type === 'checkbox')
                                    <label class="flex items-start gap-2.5 mt-1">
                                        {{-- rounded-none: el plugin @tailwindcss/forms redondea la casilla
                                             por defecto y B no admite esquinas redondeadas. --}}
                                        <input type="checkbox" disabled class="mt-0.5 w-4 h-4 rounded-none text-brand-primary border-border flex-shrink-0">
                                        <span class="text-[14px] text-fg leading-relaxed">{{ $field->placeholder ?: $field->label }}</span>
                                    </label>

                                @else
                                    <input
                                        type="{{ $field->type }}"
                                        placeholder="{{ $field->placeholder }}"
                                        disabled
                                        class="{{ $fieldClass }}"
                                    >
                                @endif

                                @if($field->help_text)
                                    <p class="mt-1.5 text-[12px] text-muted leading-snug">{{ $field->help_text }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    {{-- Footer igual al form-renderer, separado por filete gris --}}
                    {{-- flex-wrap: a 360px la nota y el botón no caben en línea. --}}
                    <div class="px-5 md:px-7 py-4 border-t border-border flex flex-wrap items-center justify-between gap-3">
                        <p class="text-[12px] text-muted">
                            <span class="text-[#B3261E]">*</span> Campos obligatorios
                        </p>
                        <button type="button" disabled class="btn-primary opacity-90">
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
<section class="bg-bg" @if($bgColor) style="background-color:{{ $bgColor }};" @endif>
    <div class="section-wrap">
        <div class="{{ $wrapClass }}">
            @if($title || $desc)
                <header class="mb-8">
                    @if($title)
                        <h2 class="font-serif text-section-title text-brand-navy">{{ $title }}</h2>
                    @endif
                    @if($desc)
                        {{-- La descripción suele explicar qué se envía y a quién:
                             es texto de lectura, no un pie de campo. --}}
                        <p class="mt-3 text-[15px] text-muted leading-relaxed">{{ $desc }}</p>
                    @endif
                </header>
            @endif
            @livewire('form-renderer', ['formId' => (int) $formId], key('form-'.$formId))
        </div>
    </div>
</section>
@endif
