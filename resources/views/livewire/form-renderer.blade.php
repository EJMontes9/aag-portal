<div>
    {{-- ── ESTADO: ENVIADO CON ÉXITO ──────────────────────────────────────── --}}
    @if($submitted)
        <div
            class="rounded-2xl border border-green-200 bg-green-50 dark:bg-green-950/30 dark:border-green-800 p-8 text-center"
            x-data x-init="$el.scrollIntoView({ behavior: 'smooth', block: 'center' })"
        >
            <div class="flex items-center justify-center mb-4">
                <span class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-green-100 dark:bg-green-900">
                    <svg class="w-7 h-7 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </span>
            </div>
            <h3 class="text-xl font-semibold text-green-800 dark:text-green-300 mb-2">¡Mensaje enviado!</h3>
            <p class="text-green-700 dark:text-green-400">{{ $form->success_message }}</p>
        </div>

    {{-- ── FORMULARIO ───────────────────────────────────────────────────────── --}}
    @else
        <form
            wire:submit="submit"
            class="rounded-2xl border border-border bg-card shadow-sm overflow-hidden"
            novalidate
        >
            {{-- Campo honeypot: oculto visualmente, los bots lo llenan --}}
            <div aria-hidden="true" style="position:absolute;left:-9999px;opacity:0;pointer-events:none;tab-index:-1;">
                <label for="__website">Dejar en blanco</label>
                <input
                    id="__website"
                    type="text"
                    wire:model="honeypot"
                    name="website"
                    autocomplete="off"
                    tabindex="-1"
                >
            </div>

            <div class="p-6 md:p-8 space-y-6">

                {{-- Error de rate limit --}}
                @error('_rate')
                    <div class="rounded-lg bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800 p-4 text-sm text-red-700 dark:text-red-400">
                        {{ $message }}
                    </div>
                @enderror

                {{-- Campos dinámicos --}}
                @foreach($fields as $field)
                    @php
                        $key       = $field->field_key;
                        $inputId   = 'field_' . $key;
                        $hasError  = $errors->has("values.{$key}");
                        $baseInput = 'w-full rounded-lg border px-3.5 py-2.5 text-sm bg-bg text-fg placeholder-muted/60 transition focus:outline-none focus:ring-2 focus:ring-brand-primary/40 ' .
                                     ($hasError
                                        ? 'border-red-400 dark:border-red-600 focus:ring-red-300/40'
                                        : 'border-border focus:border-brand-primary/60');
                    @endphp

                    <div wire:key="field-{{ $key }}">
                        {{-- Label --}}
                        <label
                            for="{{ $inputId }}"
                            class="block text-sm font-medium text-fg mb-1.5"
                        >
                            {{ $field->label }}
                            @if($field->required)
                                <span class="text-red-500 ml-0.5" aria-hidden="true">*</span>
                            @endif
                        </label>

                        {{-- Campo según tipo --}}
                        @if($field->type === 'textarea')
                            <textarea
                                id="{{ $inputId }}"
                                wire:model.blur="values.{{ $key }}"
                                rows="4"
                                placeholder="{{ $field->placeholder }}"
                                @if($field->required) required @endif
                                @if($field->max_length) maxlength="{{ $field->max_length }}" @endif
                                class="{{ $baseInput }} resize-y"
                                aria-describedby="{{ $field->help_text ? $inputId.'_help' : '' }} {{ $hasError ? $inputId.'_error' : '' }}"
                            ></textarea>

                        @elseif($field->type === 'select')
                            <select
                                id="{{ $inputId }}"
                                wire:model.live="values.{{ $key }}"
                                @if($field->required) required @endif
                                class="{{ $baseInput }} cursor-pointer"
                                aria-describedby="{{ $hasError ? $inputId.'_error' : '' }}"
                            >
                                <option value="">{{ $field->placeholder ?: '— Selecciona una opción —' }}</option>
                                @foreach($field->options ?? [] as $opt)
                                    <option value="{{ $opt['value'] }}">{{ $opt['label'] }}</option>
                                @endforeach
                            </select>

                        @elseif($field->type === 'radio')
                            <div class="space-y-2 mt-1" role="radiogroup" aria-required="{{ $field->required ? 'true' : 'false' }}">
                                @foreach($field->options ?? [] as $opt)
                                    <label class="flex items-center gap-2.5 cursor-pointer group">
                                        <input
                                            type="radio"
                                            wire:model.live="values.{{ $key }}"
                                            value="{{ $opt['value'] }}"
                                            class="w-4 h-4 text-brand-primary border-border focus:ring-brand-primary/40 cursor-pointer"
                                            @if($field->required) required @endif
                                        >
                                        <span class="text-sm text-fg group-hover:text-brand-primary transition">{{ $opt['label'] }}</span>
                                    </label>
                                @endforeach
                            </div>

                        @elseif($field->type === 'checkbox')
                            <label class="flex items-start gap-3 cursor-pointer group mt-1">
                                <input
                                    type="checkbox"
                                    id="{{ $inputId }}"
                                    wire:model.live="values.{{ $key }}"
                                    class="mt-0.5 w-4 h-4 rounded text-brand-primary border-border focus:ring-brand-primary/40 cursor-pointer flex-shrink-0"
                                    @if($field->required) required @endif
                                >
                                <span class="text-sm text-fg leading-relaxed group-hover:text-brand-primary transition">
                                    {{ $field->placeholder ?: $field->label }}
                                </span>
                            </label>

                        @else
                            {{-- text | email | tel | number | date --}}
                            <input
                                type="{{ $field->type }}"
                                id="{{ $inputId }}"
                                wire:model.blur="values.{{ $key }}"
                                placeholder="{{ $field->placeholder }}"
                                @if($field->required)   required @endif
                                @if($field->min_length) minlength="{{ $field->min_length }}" @endif
                                @if($field->max_length) maxlength="{{ $field->max_length }}" @endif
                                class="{{ $baseInput }}"
                                autocomplete="{{ $field->type === 'email' ? 'email' : ($field->type === 'tel' ? 'tel' : 'on') }}"
                                aria-describedby="{{ $field->help_text ? $inputId.'_help' : '' }} {{ $hasError ? $inputId.'_error' : '' }}"
                            >
                        @endif

                        {{-- Texto de ayuda --}}
                        @if($field->help_text)
                            <p id="{{ $inputId }}_help" class="mt-1.5 text-xs text-muted">
                                {{ $field->help_text }}
                            </p>
                        @endif

                        {{-- Error de validación --}}
                        @error("values.{$key}")
                            <p id="{{ $inputId }}_error" class="mt-1.5 text-xs text-red-600 dark:text-red-400 flex items-center gap-1" role="alert">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                @endforeach

            </div>

            {{-- Footer con botón de envío --}}
            <div class="px-6 md:px-8 pb-6 md:pb-8 pt-2 flex items-center justify-between gap-4">
                <p class="text-xs text-muted">
                    <span class="text-red-500">*</span> Campos obligatorios
                </p>

                <button
                    type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-brand-primary px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-primary/90 focus:outline-none focus:ring-2 focus:ring-brand-primary/50 focus:ring-offset-2 transition disabled:opacity-60 disabled:cursor-not-allowed"
                    wire:loading.attr="disabled"
                    wire:target="submit"
                >
                    {{-- Spinner mientras carga --}}
                    <svg wire:loading wire:target="submit"
                         class="animate-spin h-4 w-4 text-white"
                         fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>

                    <span wire:loading.remove wire:target="submit">
                        {{ $form->submit_label }}
                    </span>
                    <span wire:loading wire:target="submit">
                        Enviando…
                    </span>
                </button>
            </div>

        </form>
    @endif
</div>
