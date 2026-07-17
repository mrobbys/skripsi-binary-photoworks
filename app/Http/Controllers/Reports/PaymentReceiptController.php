<?php

namespace App\Http\Controllers\Reports;

use App\Domains\Booking\Models\Booking;
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
    public function __invoke(Booking $booking)
    {
        $booking->load([
            'user',
            'packageVariant',
            'addons',
            'payments'
        ]);

        // bukti pembayaran berdasarkan pemilik booking / user
        // TODO : sesuaikan lagi untuk pengkondisian, siapa saja yang memiliki hak akses
        $isOwned = Auth::id() === $booking->user_id;
        $isSuperadmin = Auth::user()->hasRole('superadmin');
        
        if (!$isOwned && !$isSuperadmin) {
            abort(403, 'Anda tidak memiliki izin untuk mengakses bukti pembayaran ini.');
        }

        // Filter pembayaran yang sukses (Settlement)
        $settledPayments = $booking->payments->where('status', PaymentStatus::SETTLEMENT);

        // Cari payment utama: prioritaskan Pelunasan / Lunas, baru DP, atau fallback ke data pertama
        $mainPayment = $settledPayments->whereIn('payment_purpose', [PaymentPurpose::PELUNASAN, PaymentPurpose::LUNAS])->first()
            ?? $settledPayments->where('payment_purpose', PaymentPurpose::DP)->first()
            ?? $booking->payments->first();

        if (!$mainPayment) {
            abort(404, 'Data pembayaran tidak ditemukan.');
        }

        // menentukan label untuk status kuitansi pembayaran
        $statusLabel = strtoupper($mainPayment->status->label());
        if ($mainPayment->status === PaymentStatus::SETTLEMENT) {
            $statusLabel = match ($mainPayment->payment_purpose) {
                PaymentPurpose::DP => 'DP TERBAYAR (60%)',
                PaymentPurpose::PELUNASAN => 'LUNAS (PELUNASAN)',
                PaymentPurpose::LUNAS => 'LUNAS',
            };
        }

        // package name dan package variant
        $packageName = "{$booking->packageVariant->package->name} - {$booking->packageVariant->name}";

        // Hitung total seluruh pembayaran yang berstatus Lunas
        $totalPaid = $settledPayments->sum('amount');

        // Format daftar riwayat pembayaran untuk ditampilkan di PDF
        $paymentsList = $settledPayments->map(fn($p) => new Fluent([
            'date' => Formatter::dateId($p->pay_date ?? $p->created_at, 'd M Y, H:i'),
            'purpose' => match ($p->payment_purpose) {
                PaymentPurpose::DP => 'Uang Muka (DP 60%)',
                PaymentPurpose::PELUNASAN => 'Pelunasan (40%)',
                PaymentPurpose::LUNAS => 'Lunas (100%)',
                default => strtoupper($p->payment_purpose->value ?? $p->payment_purpose),
            },
            'method' => strtoupper($p->payment_type ?? 'MANUAL'),
            'amount' => Formatter::rupiah($p->amount),
        ]));

        $formattedData = new Fluent(([
            'booking_code' => $booking->booking_code,
            'order_id' => $mainPayment->order_id,
            'status' => $statusLabel,
            'client_name' => $booking->user->name,
            'client_email' => $booking->user->email,
            'client_phone' => $booking->user->phone,
            'package_name' => $packageName,
            'package_price' => Formatter::rupiah($booking->packageVariant->price),
            'total_price' => Formatter::rupiah($booking->total_price),
            'amount_paid' => Formatter::rupiah($totalPaid),
            'payment_type' => strtoupper($mainPayment->payment_type ?? '-'),
            'pay_date' => Formatter::dateId($mainPayment->pay_date ?? $booking->created_at, 'l, d F Y'),
            // total price - total paid
            'remaining' => Formatter::rupiah(
                $booking->total_price - $totalPaid
            ),
            'payment_scheme' => $booking->payment_scheme,
            'payment_purpose' => $mainPayment->payment_purpose,
            'studio_whatsapp' => config('studio.whatsapp'),
        ]));


        $formattedAddons = $booking->addons->map(fn($addon) => new Fluent([
            'name' => $addon->name,
            'quantity' => $addon->pivot->quantity,
            'price' => Formatter::rupiah($addon->pivot->price_at_purchase),
            // quantity * price
            'amount' => Formatter::rupiah(
                $addon->pivot->quantity * $addon->pivot->price_at_purchase
            ),
        ]));

        return pdf()
            ->view('pdfs.payment-receipt', compact('formattedData', 'formattedAddons', 'paymentsList'))
            ->format('a4')
            ->name("payment-{$booking->booking_code}.pdf");
    }
}
