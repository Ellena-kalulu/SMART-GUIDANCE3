<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\{Content, Envelope};
use Illuminate\Queue\SerializesModels;

class RecommendationReadyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $student,
        public int $careerCount,
        public int $uniCount,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your Career Recommendations Are Ready — Smart Guidance');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.recommendation-ready');
    }
}
