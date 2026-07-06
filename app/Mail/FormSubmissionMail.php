<?php

namespace App\Mail;

use App\Models\FormSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FormSubmissionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly FormSubmission $submission
    ) {}

    public function envelope(): Envelope
    {
        $formName = $this->submission->form?->name ?? 'Formulario web';
        $siteName = config('app.name', 'Portal AAG');

        return new Envelope(
            subject: "[{$siteName}] Nueva respuesta: {$formName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.form-submission',
            with: [
                'submission' => $this->submission,
                'form'       => $this->submission->form()->with('fields')->first(),
                'siteName'   => config('app.name', 'Portal AAG'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
