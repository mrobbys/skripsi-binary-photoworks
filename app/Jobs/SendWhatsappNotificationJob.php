<?php

namespace App\Jobs;

use App\Services\FonnteWhatsappService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\Timeout;
use Illuminate\Queue\Attributes\Tries;

#[Tries(3)]
#[Timeout(60)]
class SendWhatsappNotificationJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $phone,
        public readonly string $message,
    ) {}

    public function handle(FonnteWhatsappService $service): void
    {
        $service->send($this->phone, $this->message);
    }
}
