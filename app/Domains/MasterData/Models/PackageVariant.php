<?php

namespace App\Domains\MasterData\Models;

use App\Domains\Booking\Models\Booking;
use App\Domains\MasterData\Models\Background;
use App\Domains\MasterData\Models\Feature;
use App\Domains\MasterData\Models\Package;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable('package_id', 'name', 'price', 'duration', 'is_whatsapp_only', 'is_active')]
class PackageVariant extends Model
{

    protected function casts(): array
    {
        return [
            'is_whatsapp_only' => 'boolean',
            'is_active'        => 'boolean',
        ];
    }

    /**
     * Relasi many-to-one: Satu varian dimiliki oleh satu paket
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * Fasilitas spesifik hanya untuk varian ini
     */
    public function features(): MorphMany
    {
        return $this->morphMany(Feature::class, 'featureable');
    }

    /**
     * Relasi many-to-many: Satu varian dimiliki oleh banyak background
     */
    public function backgrounds(): BelongsToMany
    {
        return $this->belongsToMany(Background::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
