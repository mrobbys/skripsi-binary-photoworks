<?php

namespace App\Domains\Booking\Services;

use App\Domains\Booking\Models\Booking;
use App\Domains\Payment\Enums\PaymentPurpose;
use App\Domains\Payment\Enums\PaymentStatus;
use App\Domains\Payment\Models\Payment;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RefundBookingOverpaymentService
{
    public function execute(Booking $booking): void
    {
        DB::transaction(function () use ($booking) {
            $lockedBooking = Booking::where('id', $booking->id)->lockForUpdate()->first();

            $totalPaidRaw = $lockedBooking->payments()
                ->where('status', PaymentStatus::SETTLEMENT)
                ->where('payment_purpose', '!=', PaymentPurpose::REFUND)
                ->sum('amount');

            $totalRefunded = $lockedBooking->payments()
                ->where('status', PaymentStatus::SETTLEMENT)
                ->where('payment_purpose', PaymentPurpose::REFUND)
                ->sum('amount');

            $overpayment = $totalPaidRaw - $lockedBooking->total_price - $totalRefunded;

            if ($overpayment <= 0) {
                throw new RuntimeException('Tidak ada kelebihan yang perlu direfund.');
            }

            $orderId = $lockedBooking->booking_code . '-RFD';
            if (Payment::where('order_id', $orderId)->exists()) {
                $orderId = $lockedBooking->booking_code . '-RFD-' . now()->timestamp;
            }

            Payment::create([
                'booking_id' => $lockedBooking->id,
                'order_id' => $orderId,
                'amount' => $overpayment,
                'status' => PaymentStatus::SETTLEMENT,
                'payment_purpose' => PaymentPurpose::REFUND,
                'payment_type' => 'manual',
                'pay_date' => now(),
            ]);
        });
    }
}
