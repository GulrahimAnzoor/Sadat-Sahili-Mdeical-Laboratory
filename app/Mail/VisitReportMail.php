<?php

namespace App\Mail;

use App\Models\Visit;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VisitReportMail extends Mailable
{
    use SerializesModels;

    public function __construct(public Visit $visit)
    {
        $this->visit->loadMissing(['patient', 'doctor', 'patientTests.test']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Lab report — :name', ['name' => $this->visit->patient->name]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.visit-report',
        );
    }
}
