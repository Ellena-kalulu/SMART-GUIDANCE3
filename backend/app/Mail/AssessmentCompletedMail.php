<?php

namespace App\Mail;

use App\Models\{AssessmentAttempt, User};
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\{Content, Envelope};
use Illuminate\Queue\SerializesModels;

class AssessmentCompletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $student,
        public AssessmentAttempt $attempt,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your Career Assessment is Complete — Smart Guidance');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.assessment-completed');
    }
}
