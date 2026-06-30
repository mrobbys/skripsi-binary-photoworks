<?php

namespace App\Services;

use Illuminate\Container\Attributes\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteWhatsappService
{
    public function __construct(
        #[Config('fonnte.token')] private string $token,
    ) {}

    public function send(string $phone, string $message): bool
    {
        $normalized = $this->normalizePhone($phone);

        $response = Http::withHeaders(['Authorization' => $this->token])
            ->post('https://api.fonnte.com/send', [
                'target' => $normalized,
                'message' => $message,
            ]);

        if (! $response->ok()) {
            Log::channel('whatsapp')->error('Fonnte send failed', [
                'phone' => $normalized,
                'response' => $response->body(),
            ]);

            return false;
        }

        return true;
    }

    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        return str_starts_with($phone, '0') ? '62'.substr($phone, 1) : $phone;
    }
}
