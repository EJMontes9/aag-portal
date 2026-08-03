<?php

namespace App\Livewire;

use App\Mail\FormSubmissionMail;
use App\Models\Form;
use App\Models\FormField;
use App\Models\FormSubmission;
use App\Rules\CorreoSeguro;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class FormRenderer extends Component
{
    // ─── Props públicas ───────────────────────────────────────────────────────

    public int    $formId;
    public array  $values   = [];
    public string $honeypot = '';   // campo trampa anti-bots (nombre engañoso en blade)
    public bool   $submitted = false;

    // ─── Propiedades computadas (no se serializan) ───────────────────────────

    protected ?Form $formModel = null;

    // ─── Lifecycle ────────────────────────────────────────────────────────────

    public function mount(int $formId): void
    {
        $this->formId = $formId;

        $form = $this->getForm();
        abort_unless($form && $form->is_active, 404);

        // Inicializar valores en blanco para cada campo activo
        foreach ($form->activeFields as $field) {
            $this->values[$field->field_key] = $field->type === 'checkbox' ? false : '';
        }
    }

    // ─── Submit ───────────────────────────────────────────────────────────────

    public function submit(): void
    {
        // 1. Trampa anti-bots: si el campo honeypot tiene valor, un bot lo llenó
        if ($this->honeypot !== '') {
            $this->submitted = true; // finge éxito
            return;
        }

        // 2. Rate limiting: máx 5 envíos por IP en 10 minutos
        $key = 'form-submit:' . request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('_rate', 'Has enviado demasiados mensajes. Espera unos minutos e intenta de nuevo.');
            return;
        }
        RateLimiter::hit($key, 600); // 10 min

        // 3. Validar
        $this->validate(
            $this->buildRules(),
            $this->buildMessages()
        );

        $form = $this->getForm();

        // 4. Guardar en BD si el formulario tiene store_submissions activo
        $submission = null;
        if ($form->store_submissions) {
            $submission = FormSubmission::create([
                'form_id'    => $this->formId,
                'data'       => $this->values,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }

        // 5. Notificar por email
        //    Se envía siempre que haya correos configurados, independiente de store_submissions.
        //    Si no se guardó en BD, se crea un objeto temporal (sin ID) solo para el mail.
        $emails = array_filter(
            $form->notify_emails ?? [],
            fn ($e) => filter_var($e, FILTER_VALIDATE_EMAIL)
        );

        if (! empty($emails)) {
            $mailSubmission = $submission ?? new FormSubmission([
                'form_id'    => $this->formId,
                'data'       => $this->values,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
            ]);
            // Relacionar el form para que el mail pueda usar $submission->form
            $mailSubmission->setRelation('form', $form);

            foreach ($emails as $email) {
                try {
                    Mail::to($email)->send(new FormSubmissionMail($mailSubmission));

                    // Registrar en el log de actividad
                    activity()
                        ->withProperties([
                            'to'          => $email,
                            'form'        => $form->name,
                            'form_id'     => $form->id,
                            'submission_id' => $submission?->id,
                            'ip'          => request()->ip(),
                        ])
                        ->event('mail_sent')
                        ->log("Notificación enviada a {$email} — {$form->name}");

                } catch (\Throwable $e) {
                    \Log::warning("FormSubmissionMail failed to {$email}: " . $e->getMessage());

                    activity()
                        ->withProperties([
                            'to'    => $email,
                            'error' => $e->getMessage(),
                            'form'  => $form->name,
                        ])
                        ->event('mail_failed')
                        ->log("Error al enviar notificación a {$email} — {$form->name}");
                }
            }
        }

        $this->submitted = true;
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    protected function getForm(): ?Form
    {
        if (! $this->formModel) {
            $this->formModel = Form::with('activeFields')->find($this->formId);
        }
        return $this->formModel;
    }

    protected function buildRules(): array
    {
        $rules = ['honeypot' => 'size:0']; // debe estar vacío

        foreach ($this->getForm()->activeFields as $field) {
            $fieldRules = [];

            // Obligatorio / nullable
            $fieldRules[] = $field->required ? 'required' : 'nullable';

            // Tipo
            match ($field->type) {
                // Igual que en el boletín: 'email:rfc,dns' valida el formato y
                // que el dominio exista; CorreoSeguro cierra el CRLF. Los dos.
                'email'    => array_push($fieldRules, 'email:rfc,dns', new CorreoSeguro),
                'tel'      => $fieldRules[] = 'regex:/^[+\d\s\-().]{6,25}$/',
                'number'   => $fieldRules[] = 'numeric',
                'date'     => $fieldRules[] = 'date',
                'checkbox' => $fieldRules[] = 'boolean',
                'select',
                'radio'    => $fieldRules[] = 'string',
                default    => $fieldRules[] = 'string',
            };

            // Longitudes para texto libre
            if (in_array($field->type, ['text', 'textarea', 'email', 'tel'])) {
                if ($field->min_length) {
                    $fieldRules[] = "min:{$field->min_length}";
                }
                $max = $field->max_length ?? ($field->type === 'textarea' ? 5000 : 500);
                $fieldRules[] = "max:{$max}";
            }

            // Opciones válidas para select / radio
            if (in_array($field->type, ['select', 'radio']) && ! empty($field->options)) {
                $valid = implode(',', array_column($field->options, 'value'));
                $fieldRules[] = "in:{$valid}";
            }

            $rules["values.{$field->field_key}"] = $fieldRules;
        }

        return $rules;
    }

    protected function buildMessages(): array
    {
        $messages = [];

        foreach ($this->getForm()->activeFields as $field) {
            $key = "values.{$field->field_key}";
            $messages["{$key}.required"]     = "El campo \"{$field->label}\" es obligatorio.";
            $messages["{$key}.email"]        = "Ingresa un correo electrónico válido en \"{$field->label}\".";
            $messages["{$key}.regex"]        = "El campo \"{$field->label}\" tiene un formato inválido.";
            $messages["{$key}.numeric"]      = "El campo \"{$field->label}\" debe ser un número.";
            $messages["{$key}.date"]         = "El campo \"{$field->label}\" debe ser una fecha válida.";
            $messages["{$key}.min"]          = "El campo \"{$field->label}\" debe tener al menos :min caracteres.";
            $messages["{$key}.max"]          = "El campo \"{$field->label}\" no puede superar :max caracteres.";
            $messages["{$key}.in"]           = "La opción seleccionada en \"{$field->label}\" no es válida.";
        }

        return $messages;
    }

    // ─── Render ───────────────────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.form-renderer', [
            'form'   => $this->getForm(),
            'fields' => $this->getForm()?->activeFields ?? collect(),
        ]);
    }
}
