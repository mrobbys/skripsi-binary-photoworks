<?php

namespace App\Http\Controllers\Reports;

use App\Domains\Payment\Enums\PaymentPurpose;
use App\Domains\Payment\Enums\PaymentStatus;
use App\Domains\Payment\Models\Payment;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Fluent;
use App\Support\Formatter;

use function Spatie\LaravelPdf\Support\pdf;

class PaymentReceiptController extends Controller
{
    public function __invoke(Payment $payment)
    {
        $payment->load([
            'booking.user',
            'booking.packageVariant',
            'booking.addons'
        ]);

        // bukti pembayaran berdasarkan pemilik booking / user
        if (Auth::id() !== $payment->booking->user_id) {
            abort(403, 'Unauthorized access to this payment receipt.');
        }

        // menentukan label untuk status kuitansi pembayaran
        $statusLabel = strtoupper($payment->status->label());
        if ($payment->status === PaymentStatus::SETTLEMENT) {
            $statusLabel = match ($payment->payment_purpose) {
                PaymentPurpose::DP => 'DP TERBAYAR (60%)',
                PaymentPurpose::PELUNASAN => 'LUNAS (PELUNASAN)',
                PaymentPurpose::LUNAS => 'LUNAS',
            };
        }

        // package name dan package variant
        $packageName = "{$payment->booking->packageVariant->package->name} - {$payment->booking->packageVariant->name}";

        // Hitung total seluruh pembayaran yang berstatus Lunas (Misal: Gabungan DP + Cashier)
        $totalPaid = $payment->booking->payments()->where('status', PaymentStatus::SETTLEMENT)->sum('amount');

        $formattedData = new Fluent(([
            'booking_code' => $payment->booking->booking_code,
            'order_id' => $payment->order_id,
            'status' => $statusLabel,
            'client_name' => $payment->booking->user->name,
            'client_email' => $payment->booking->user->email,
            'client_phone' => $payment->booking->user->phone,
            'package_name' => $packageName,
            'package_price' => Formatter::rupiah($payment->booking->packageVariant->price),
            'total_price' => Formatter::rupiah($payment->booking->total_price),
            'amount_paid' => Formatter::rupiah($totalPaid),
            'payment_type' => strtoupper($payment->payment_type ?? '-'),
            'pay_date' => Formatter::dateId($payment->pay_date ?? $payment->created_at, 'l, d F Y'),
            // total price - total paid
            'remaining' => Formatter::rupiah(
                $payment->booking->total_price - $totalPaid
            ),
            'payment_scheme' => $payment->booking->payment_scheme,
            'payment_purpose' => $payment->payment_purpose,
            'studio_whatsapp' => config('studio.whatsapp'),
        ]));


        $formattedAddons = $payment->booking->addons->map(fn($addon) => new Fluent([
            'name' => $addon->name,
            'quantity' => $addon->pivot->quantity,
            'price' => Formatter::rupiah($addon->pivot->price_at_purchase),
            // quantity * price
            'amount' => Formatter::rupiah(
                $addon->pivot->quantity * $addon->pivot->price_at_purchase
            ),
        ]));

        return pdf()
            ->view('pdfs.payment-receipt', compact('formattedData', 'formattedAddons'))
            ->format('a4')
            ->name("payment-{$payment->booking->booking_code}.pdf");
    }
}
