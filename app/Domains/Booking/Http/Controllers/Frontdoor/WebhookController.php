<?php

namespace App\Domains\Booking\Http\Controllers\Frontdoor;

use App\Domains\Booking\Enums\BookingStatus;
use App\Domains\Booking\Models\Booking;
use App\Domains\Payment\Enums\PaymentPurpose;
use App\Domains\Payment\Enums\PaymentStatus;
use App\Domains\Payment\Models\Payment;
use App\Http\Controllers\Controller;
use App\Jobs\SendWhatsappNotificationJob;
use Illuminate\Container\Attributes\Config;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Support\Formatter;
use Carbon\Carbon;

class WebhookController extends Controller
{
    public function __construct(
        #[Config('midtrans.server_key')] private string $serverKey,
    ) {}

    /**
     * Handle Midtrans Webhook
     * @param Request $request
     */
    public function handle(Request $request): Response
    {
        $payload = $request->all();

        // Verifikasi SHA512 signature
        $calculatedSig = hash(
            'sha512',
            $payload['order_id'] . $payload['status_code'] . $payload['gross_amount'] . $this->serverKey
        );

        if ($calculatedSig !== $payload['signature_key']) {
            activity()->log('Webhook signature mismatch: ' . ($payload['order_id'] ?? 'unknown'));

            return response('Forbidden', 403);
        }

        $payment = Payment::where('order_id', $payload['order_id'])->firstOrFail();
        $booking = $payment->booking()->with('user')->firstOrFail();

        // jika payment sudah lunas, kembalikan response
        if ($payment->status === PaymentStatus::SETTLEMENT) {
            return response('OK', 200);
        }

        $status = $payload['transaction_status'];
        $fraudStatus = $payload['fraud_status'] ?? null;
        $paymentType = $payload['payment_type'] ?? null;

        // jangan proses, jika booking tersebut sudah di cancel
        if ($booking->status === BookingStatus::CANCELLED) {
            return response('OK', 200);
        }

        // update status jika payment berhasil
        if (($status === 'capture' && $fraudStatus === 'accept') || $status === 'settlement') {
            // update payment
            $payment->update([
                'status' => PaymentStatus::SETTLEMENT,
                'pay_date' => now(),
                'payment_type' => $paymentType
            ]);
            // update booking
            $booking->update(['status' => $payment->payment_purpose === PaymentPurpose::DP ? BookingStatus::DP_PAID : BookingStatus::SUCCESS]);
            // kirim notifikasi whatsapp fonnte
            SendWhatsappNotificationJob::dispatch(
                $booking->user->phone,
                $this->buildMessage($booking, $payment)
            );
        } elseif ($status === 'pending') {
            // update payment, jika status pending
            $payment->update([
                'status' => PaymentStatus::PENDING,
                'payment_type' => $paymentType,
                'snap_token_expiry' => isset($payload['expiry_time']) ? Carbon::parse($payload['expiry_time']) : $payment->snap_token_expiry
            ]);
            // hanya update ke pending jika status saat ini bukan success / dp_paid
            if ($booking->status === BookingStatus::PENDING) {
                // update booking
                $booking->update(['status' => BookingStatus::PENDING]);
            }
        } elseif (in_array($status, ['deny', 'cancel', 'expire'])) {
            // update payment jika status deny, cancel, expire
            $payment->update([
                'status' => PaymentStatus::CANCELLED,
                'payment_type' => $paymentType
            ]);

            // jika status saat ini pending, update status ke cancel
            if ($booking->status === BookingStatus::PENDING) {
                // update booking
                $booking->update(['status' => BookingStatus::CANCELLED]);
            }
        }
        return response('OK', 200);
    }

    /**
     * Function membuat pesan untuk notifikasi whatsapp fonnte
     * @param Booking $booking
     * @param Payment $payment
     */
    private function buildMessage(Booking $booking, Payment $payment): string
    {
        $code = $booking->booking_code;
        $date = Formatter::dateId($booking->booking_date, 'l, d F Y');
        $time = Formatter::timeRange($booking->start_time, $booking->end_time);
        $amount = Formatter::rupiah($payment->amount);

        $receiptUrl = route('payments.receipt', ['payment' => $payment->order_id]);
        $dashboardUrl = route('frontdoor.dashboard.index');

        if ($payment->payment_purpose === PaymentPurpose::DP) {
            return <<<TEXT
Halo {$booking->user->name}, pembayaran Uang Muka (DP 60%) Anda sebesar {$amount} untuk Kode Booking {$code} telah kami terima.

Unduh Bukti Pembayaran DP Anda melalui tautan berikut:
{$receiptUrl}

Lihat detail jadwal reservasi Anda pada tautan Dasbor Klien berikut:
{$dashboardUrl}

Sisa tagihan 40% dapat dilunasi di meja kasir saat hari pelaksanaan sesi foto. Terima kasih!
TEXT;
        }

        return <<<TEXT
Halo {$booking->user->name}, terima kasih! Pembayaran Anda telah berhasil kami terima.

Kode Booking : {$code}
Status : LUNAS (100% Terbayar)
Total Bayar : {$amount}
Tanggal Sesi : {$date}
Waktu Sesi : {$time}

Unduh Bukti Pembayaran Anda melalui tautan berikut:
{$receiptUrl}

Pantau status jadwal dan detail reservasi Anda langsung di halaman Dashboard:
{$dashboardUrl}
TEXT;
    }
}
