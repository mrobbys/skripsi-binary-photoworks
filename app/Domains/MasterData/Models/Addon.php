<?php

namespace App\Domains\MasterData\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Model;
use App\Domains\Booking\Models\Booking;

#[Fillable('name', 'price', 'description', 'has_quantity', 'is_active')]
class Addon extends Model
{

    protected function casts(): array
    {
        return [
            'has_quantity' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function bookings(): BelongsToMany
    {
        return $this->belongsToMany(Booking::class, 'addon_booking')
            ->withPivot('price_at_purchase', 'quantity');
    }
}
