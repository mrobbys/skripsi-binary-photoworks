<?php

namespace App\Domains\MasterData\Models;

use App\Domains\MasterData\Models\Category;
use App\Domains\MasterData\Models\Feature;
use App\Domains\MasterData\Models\PackageVariant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

#[Fillable('category_id', 'name', 'description', 'slug', 'is_active')]
class Package extends Model implements HasMedia
{
    use HasSlug, InteractsWithMedia;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    /**
     * Registrasi koleksi media untuk gambar package
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('package-image')
            ->useDisk(env('MEDIA_DISK', 's3'))
            ->singleFile();
    }

    /**
     * Relasi many-to-one: Satu paket dimiliki oleh satu kategori
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relasi one-to-many: Satu paket memiliki banyak varian
     */
    public function variants(): HasMany
    {
        return $this->hasMany(PackageVariant::class);
    }

    /**
     * Fasilitas global yang berlaku untuk seluruh varian di bawah paket ini
     */
    public function features(): MorphMany
    {
        return $this->morphMany(Feature::class, 'featureable');
    }
}
