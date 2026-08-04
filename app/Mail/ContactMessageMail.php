<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;

class ContactMessageMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $senderName,
        public readonly string $senderEmail,
        public readonly string $mailSubject,
        public readonly string $message,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Pesan Baru dari {$this->senderName} — {$this->mailSubject}",
            replyTo: [new Address($this->senderEmail, $this->senderName)],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact-message',
            with: [
                'appName' => config('studio.nama_studio', config('app.name', 'Binary Photoworks')),
                'senderName' => $this->senderName,
                'senderEmail' => $this->senderEmail,
                'subject' => $this->mailSubject,
                'pesan' => $this->message,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
