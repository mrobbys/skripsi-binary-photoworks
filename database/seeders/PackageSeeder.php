<?php

namespace Database\Seeders;

use App\Domains\MasterData\Models\Category;
use App\Domains\MasterData\Models\Package;
use App\Domains\MasterData\Models\PackageVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = require database_path('seeders/data/packages.php');

        foreach ($packages as $packageData) {
            $category = Category::where('category_code', $packageData['category_code'])->first();

            if (!$category) {
                $this->command->warn("Kategori '{$packageData['category_code']}' tidak ditemukan. Skip: {$packageData['name']}");
                continue;
            }

            /** @var Package $package */
            $package = Package::create([
                'category_id' => $category->id,
                'name' => $packageData['name'],
                'slug' => Str::slug($packageData['name']),
                'description' => $packageData['description'],
                'is_active' => true,
            ]);

            // Tambahkan gambar dummy dari Unsplash
            $package->addMediaFromUrl('https://images.unsplash.com/photo-1542038784456-1ea8e935640e?w=800')
                    ->toMediaCollection('package-image');

            if (!empty($packageData['features'])) {
                $package->features()->createMany(
                    collect($packageData['features'])
                        ->map(fn(string $desc) => ['description' => $desc])
                        ->toArray(),
                );
            }

            foreach ($packageData['variants'] as $variantData) {
                /** @var PackageVariant $variant */
                $variant = $package->variants()->create([
                    'name' => $variantData['name'],
                    'price' => $variantData['price'],
                    'duration' => $variantData['duration'],
                    'is_whatsapp_only' => $variantData['is_whatsapp_only'],
                    'is_active' => true,
                ]);

                if (!empty($variantData['features'])) {
                    $variant->features()->createMany(
                        collect($variantData['features'])
                            ->map(fn(string $desc) => ['description' => $desc])
                            ->toArray(),
                    );
                }
            }
        }
    }
}
