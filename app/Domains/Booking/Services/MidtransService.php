<?php

namespace App\Domains\Booking\Services;

use App\Domains\Booking\Models\Booking;
use App\Domains\User\Models\User;
use Illuminate\Container\Attributes\Config;
use Illuminate\Support\Facades\Http;

class MidtransService
{
    public function __construct(
        #[Config('midtrans.server_key')] private string $serverKey,
        #[Config('midtrans.snap_url')] private string $snapUrl,
    ) {}

    public function getSnapToken(string $orderId, int $grossAmount, User $user, Booking $booking): string
    {
        $payload = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
            'callbacks' => [
                'finish' => route('frontdoor.booking.success', $orderId),
            ],
        ];

        $response = Http::withBasicAuth($this->serverKey, '')
            ->post("{$this->snapUrl}/transactions", $payload)
            ->throw()
            ->json();

        return $response['token'];
    }
}
