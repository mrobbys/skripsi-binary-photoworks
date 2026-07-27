<?php

namespace App\Domains\Booking\Traits;

use App\Domains\MasterData\Models\Addon;
use App\Domains\MasterData\Models\PackageVariant;
use Illuminate\Support\Collection;

trait CalculatesBookingTotal
{
    /**
     * Hitung total harga booking.
     * Jika $preloadedAddons diberikan, query ke DB di-skip (hindari N+1).
     *
     * @param PackageVariant $variant
     * @param array $addons
     * @param Collection|null $preloadedAddons keyed by addon id
     * @return int
     */
    public function calculateTotal(PackageVariant $variant, array $addons, ?Collection $preloadedAddons = null): int
    {
        $base = $variant->price;
        $addonTotal = 0;

        if (!empty($addons)) {
            $addonIds = array_column($addons, 'addon_id');
            $addonModels = $preloadedAddons ?? Addon::whereIn('id', $addonIds)->get()->keyBy('id');

            foreach ($addons as $item) {
                $addon = $addonModels->get($item['addon_id']);
                if ($addon) {
                    $addonTotal += $addon->price * ($item['quantity'] ?? 1);
                }
            }
        }

        return $base + $addonTotal;
    }
}
