<?php

namespace App\Domains\Booking\Http\Controllers\Frontdoor;

use App\Domains\Booking\Enums\BookingStatus;
use App\Domains\Payment\Enums\PaymentStatus;
use App\Domains\Payment\Models\Payment;
use App\Http\Controllers\Controller;
use App\Jobs\SendWhatsappNotificationJob;
use Illuminate\Container\Attributes\Config;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WebhookController extends Controller
{
    public function __construct(
        #[Config('midtrans.server_key')] private string $serverKey,
    ) {}

    public function handle(Request $request): Response
    {
        $payload = $request->all();

        // Verifikasi SHA512 signature
        $calculatedSig = hash(
            'sha512',
            $payload['order_id'].$payload['status_code'].$payload['gross_amount'].$this->serverKey
        );

        if ($calculatedSig !== $payload['signature_key']) {
            activity()->log('Webhook signature mismatch: '.($payload['order_id'] ?? 'unknown'));

            return response('Forbidden', 403);
        }

        $payment = Payment::where('order_id', $payload['order_id'])->firstOrFail();
        $booking = $payment->booking()->with('user')->firstOrFail();

        $status = $payload['transaction_status'];
        $fraudStatus = $payload['fraud_status'] ?? null;

        if (($status === 'capture' && $fraudStatus === 'accept') || $status === 'settlement') {
            $payment->update(['status' => PaymentStatus::SETTLEMENT, 'pay_date' => now()]);
            $booking->update(['status' => $payment->payment_purpose === 'dp' ? BookingStatus::DP_PAID : BookingStatus::SUCCESS]);
            SendWhatsappNotificationJob::dispatch(
                $booking->user->phone,
                $this->buildMessage($booking, $payment)
            );
        } elseif ($status === 'pending') {
            $payment->update(['status' => PaymentStatus::PENDING]);
            $booking->update(['status' => BookingStatus::PENDING]);
        } elseif (in_array($status, ['deny', 'cancel', 'expire'])) {
            $payment->update(['status' => PaymentStatus::CANCELLED]);
            $booking->update(['status' => BookingStatus::CANCELLED]);
        }

        return response('OK', 200);
    }

    private function buildMessage(object $booking, object $payment): string
    {
        $appUrl = config('app.url');
        $code = $booking->booking_code;

        if ($payment->payment_purpose === 'dp') {
            $amount = number_format($payment->amount, 0, ',', '.');

            return "Halo {$booking->user->name}, DP 60% sebesar Rp {$amount} untuk Kode Booking {$code} telah sah diterima.\n\nSisa 40% dilunasi di kasir studio. Lihat detail: {$appUrl}/dashboard";
        }

        $amount = number_format($payment->amount, 0, ',', '.');

        return "Halo {$booking->user->name}, pembayaran LUNAS PENUH Rp {$amount} untuk Kode Booking {$code} berhasil.\n\nJadwal terkunci aman. Sampai jumpa di studio!";
    }
}
