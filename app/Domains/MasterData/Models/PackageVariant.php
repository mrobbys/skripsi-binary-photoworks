<?php

namespace App\Domains\MasterData\Models;

use App\Domains\Booking\Models\Booking;
use App\Domains\MasterData\Models\Feature;
use App\Domains\MasterData\Models\Package;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable('package_id', 'name', 'price', 'duration', 'is_whatsapp_only', 'is_active')]
class PackageVariant extends Model
{
    use LogsActivity;

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

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Implement Activity Log Spatie
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('master-data')
            ->setDescriptionForEvent(fn(string $event) => $event);
    }
}
