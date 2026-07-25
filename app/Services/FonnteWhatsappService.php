<?php

namespace App\Services;

use Illuminate\Container\Attributes\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteWhatsappService
{
    public function __construct(
        #[Config('fonnte.token')] private string $token,
        #[Config('fonnte.enabled')] private bool $enabled = false,
    ) {}

    /**
     * Mengirim pesan ke nomor telepon.
     * @param string $phone
     * @param string $message
     */
    public function send(string $phone, string $message): bool
    {
        // Safety guard: Jika Fonnte dinonaktifkan (misal di lokal), simpan di log saja
        if (! $this->enabled) {
            Log::channel('whatsapp')->info('[DRY-RUN] WA Notification (Fonnte Disabled)', [
                'phone'   => $phone,
                'message' => $message,
            ]);

            return true;
        }

        // Normalisasi nomor
        $normalized = $this->normalizePhone($phone);

        $response = Http::withHeaders(['Authorization' => $this->token])
            ->post('https://api.fonnte.com/send', [
                'target' => $normalized,
                'message' => $message,
            ]);

        // Log jika gagal
        if (! $response->ok()) {
            Log::channel('whatsapp')->error('Fonnte send failed', [
                'phone' => $normalized,
                'response' => $response->body(),
            ]);

            return false;
        }

        return true;
    }

    /**
     * Normalisasi nomor telepon, menjadi nomor dengan awalan 62.
     */
    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        return str_starts_with($phone, '0') ? '62' . substr($phone, 1) : $phone;
    }
}
