<?php

namespace App\Domains\Booking\Services;

use App\Domains\Booking\DTOs\UpsellAddonData;
use App\Domains\Booking\Enums\BookingStatus;
use App\Domains\Booking\Models\Booking;
use App\Domains\MasterData\Models\Addon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class UpsellBookingAddonService
{
    /**
     * Tambah addon ke booking yang sudah ada (upsell di halaman detail)
     */
    public function execute(Booking $booking, UpsellAddonData $dto): Booking
    {
        $addon = Addon::findOrFail($dto->addon_id);
        $quantity = $addon->has_quantity ? $dto->quantity : 1;

        DB::transaction(function () use ($booking, $addon, $dto, $quantity) {
            $lockedBooking = Booking::where('id', $booking->id)->lockForUpdate()->first();

            if (in_array($lockedBooking->status, [BookingStatus::CANCELLED, BookingStatus::DONE])) {
                throw new RuntimeException('Layanan tambahan tidak dapat ditambahkan ke booking ini.');
            }

            // Attach atau update pivot (jika addon sama sudah ada, qty bertambah)
            $existingPivot = $lockedBooking->addons()->where('addon_id', $addon->id)->first();

            // Gunakan harga lama jika addon sudah pernah dibeli di booking ini, untuk konsistensi matematika saat removal
            $priceToCharge = $existingPivot ? $existingPivot->pivot->price_at_purchase : $addon->price;

            if ($existingPivot) {
                $lockedBooking->addons()->updateExistingPivot($addon->id, [
                    'quantity' => $existingPivot->pivot->quantity + $quantity,
                ]);
            } else {
                $lockedBooking->addons()->attach($addon->id, [
                    'quantity' => $quantity,
                    'price_at_purchase' => $priceToCharge,
                ]);
            }

            // Update total_price booking
            $addonSubtotal = $priceToCharge * $quantity;
            $lockedBooking->increment('total_price', $addonSubtotal);

            // Update status total_price di memory controller
            $booking->total_price = $lockedBooking->total_price;
        });

        return $booking;
    }
}
