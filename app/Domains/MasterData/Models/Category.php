<?php

namespace App\Domains\MasterData\Models;

use App\Domains\MasterData\Models\Package;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

#[Fillable('category_code', 'name', 'slug', 'is_active')]
class Category extends Model
{

    use HasSlug;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Konfigurasi spatie sluggable
     * - slug di dapat dari kolom = name
     * - slug tidak diubah otomatis ketika nama diupdate
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    /**
     * Relasi one-to-many: Satu kategori memiliki banyak paket
     */
    public function packages(): HasMany
    {
        return $this->hasMany(Package::class);
    }
}
