<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param string $resetUrl
     * @param string $userName
     */
    public function __construct(
        public readonly string $resetUrl,
        public readonly string $userName,
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Permintaan Atur Ulang Kata Sandi - ' . config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.auth.reset-password',
            with: [
                'resetUrl' => $this->resetUrl,
                'userName' => $this->userName,
                'appName' => config('app.name'),
                'expiresIn' => config('auth.passwords.users.expire', 60)
            ]
        );
    }
}
