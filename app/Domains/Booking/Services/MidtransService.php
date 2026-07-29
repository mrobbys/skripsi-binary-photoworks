<?php

namespace App\Domains\Booking\Services;

use App\Domains\User\Models\User;
use Illuminate\Container\Attributes\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class MidtransService
{
    public function __construct(
        #[Config('midtrans.server_key')] private string $serverKey,
        #[Config('midtrans.base_url')] private string $baseUrl,
    ) {}

    public function getSnapToken(
        string $orderId,
        int $grossAmount,
        User $user,
        int $bookingId,
        string $bookingCode,
        string $packageName,
        string $variantName
    ): string {
        $itemName = trim("{$packageName} - {$variantName}");
        $itemName = Str::limit($itemName, 50, '...');
        $itemId = (string) "Id Booking-{$bookingId}";

        $itemDetails = [
            [
                'id' => $itemId,
                'price' => (int) $grossAmount,
                'quantity' => 1,
                'name' => $itemName,
            ]
        ];

        $payload = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'item_details' => $itemDetails,
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
            'callbacks' => [
                'finish' => route('frontdoor.booking.success', $bookingCode),
            ],
        ];

        $response = Http::withBasicAuth($this->serverKey, '')
            ->post($this->baseUrl, $payload)
            ->throw()
            ->json();

        return $response['token'];
    }
}
