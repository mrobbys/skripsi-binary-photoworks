<?php

namespace App\Domains\MasterData\Models;

use App\Domains\Booking\Models\Booking;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable('name', 'description', 'is_active')]
class Background extends Model implements HasMedia
{
    use InteractsWithMedia, LogsActivity;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Registrasi koleksi media untuk gambar latar belakang
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('background-image')
            ->useDisk(env('MEDIA_DISK', 's3'))
            ->singleFile();   // hanya 1 gambar per background
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Fit::Crop, 100, 100)
            ->nonQueued();
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
