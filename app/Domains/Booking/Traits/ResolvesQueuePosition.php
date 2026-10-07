<?php

namespace App\Domains\Booking\Traits;

use App\Domains\Booking\Enums\BookingStatus;
use App\Domains\Booking\Models\Booking;

trait ResolvesQueuePosition
{
    /**
     * Hitung urutan antrean untuk booking yang berstatus WAITING_LIST
     */
    public function calculateQueuePosition(Booking $booking): int
    {
        if ($booking->status !== BookingStatus::WAITING_LIST) {
            return 0;
        }

        return Booking::where('booking_date', $booking->booking_date->format('Y-m-d'))
            ->where('start_time', $booking->start_time->format('H:i'))
            ->where('status', BookingStatus::WAITING_LIST)
            ->where('id', '<=', $booking->id)
            ->count();
    }
}
