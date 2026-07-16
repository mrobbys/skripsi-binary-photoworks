<?php

namespace App\Domains\Booking\Models;

use App\Domains\Booking\Enums\BookingSource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Domains\User\Models\User;
use App\Domains\MasterData\Models\PackageVariant;
use App\Domains\MasterData\Models\Background;
use App\Domains\MasterData\Models\Addon;
use App\Domains\Payment\Models\Payment;

use App\Domains\Booking\Enums\BookingStatus;
use App\Domains\Booking\Enums\PaymentScheme;

#[Guarded(['id'])]
class Booking extends Model
{
    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
            'start_time' => 'datetime:H:i',
            'end_time' => 'datetime:H:i',
            'payment_scheme' => PaymentScheme::class,
            'status' => BookingStatus::class,
            'source' => BookingSource::class
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function packageVariant(): BelongsTo
    {
        return $this->belongsTo(PackageVariant::class);
    }

    public function background(): BelongsTo
    {
        return $this->belongsTo(Background::class);
    }

    public function addons(): BelongsToMany
    {
        return $this->belongsToMany(Addon::class, 'addon_booking')
            ->withPivot('price_at_purchase', 'quantity');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
