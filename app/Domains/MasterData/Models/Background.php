<?php

namespace App\Domains\MasterData\Models;

use App\Domains\MasterData\Models\PackageVariant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[Fillable('name', 'description', 'is_active')]
class Background extends Model implements HasMedia
{
    use InteractsWithMedia;

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

    /**
     * Relasi many-to-many: Satu background bisa dimiliki oleh banyak varian
     */
    public function packageVariants(): BelongsToMany
    {
        return $this->belongsToMany(PackageVariant::class);
    }
}
