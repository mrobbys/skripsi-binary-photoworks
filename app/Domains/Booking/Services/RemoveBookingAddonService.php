<?php

namespace App\Domains\Booking\Services;

use App\Domains\Booking\Enums\BookingStatus;
use App\Domains\Booking\Models\Booking;
use App\Domains\MasterData\Models\Addon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RemoveBookingAddonService
{
    /**
     * Hapus addon dari booking (di halaman detail)
     */
    public function execute(Booking $booking, Addon $addon): Booking
    {
        DB::transaction(function () use ($booking, $addon) {
            $lockedBooking = Booking::where('id', $booking->id)->lockForUpdate()->first();

            if (in_array($lockedBooking->status, [BookingStatus::CANCELLED, BookingStatus::DONE])) {
                throw new RuntimeException('Layanan tambahan tidak dapat dihapus dari booking ini.');
            }

            $pivot = $lockedBooking->addons()->where('addon_id', $addon->id)->first();
            if (! $pivot) {
                throw new RuntimeException('Layanan tambahan tidak ditemukan pada pemesanan ini.');
            }

            $deductedAmount = $pivot->pivot->price_at_purchase * $pivot->pivot->quantity;
            $lockedBooking->decrement('total_price', $deductedAmount);
            $lockedBooking->addons()->detach($addon->id);

            // Sinkronkan update memory object
            $booking->total_price = $lockedBooking->total_price;
        });

        return $booking;
    }
}
