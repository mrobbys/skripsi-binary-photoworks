<?php

namespace App\Jobs;

use App\Mail\ResetPasswordMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\Attributes\MaxExceptions;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

#[Tries(3)]
#[MaxExceptions(3)]
class SendResetPasswordEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param string $email
     * @param string $userName
     * @param string $resetUrl
     */
    public function __construct(
        private readonly string $email,
        private readonly string $userName,
        private readonly string $resetUrl,
    ) {}

    /**
     * Kirim email reset password di background
     *
     * @return void
     */
    public function handle(): void
    {
        Mail::to($this->email)->send(
            new ResetPasswordMail(
                resetUrl: $this->resetUrl,
                userName: $this->userName,
            )
        );
    }

    /**
     * Jika job gagal, log errornya
     * 
     * @return void
     */
    public function failed(\Throwable $exception): void
    {
        logger()->error('SendResetPasswordEmailJob failed', [
            'email' => $this->email,
            'error' => $exception->getMessage(),
        ]);
    }
}
