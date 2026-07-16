<?php

namespace App\Domains\Booking\Traits;

use App\Domains\MasterData\Models\Addon;
use App\Domains\MasterData\Models\PackageVariant;

trait CalculatesBookingTotal
{
    /**
     * Hitung total harga booking
     * @param PackageVariant $variant
     * @param array $addons
     * @return int
     */
    public function calculateTotal(PackageVariant $variant, array $addons): int
    {
        $base = $variant->price;
        $addonTotal = 0;

        if (!empty($addons)) {
            $addonIds = array_column($addons, 'addon_id');
            $addonModels = Addon::whereIn('id', $addonIds)->get()->keyBy('id');

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
