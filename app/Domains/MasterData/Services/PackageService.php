<?php

namespace App\Domains\MasterData\Services;

use App\Domains\MasterData\DTOs\PackageData;
use App\Domains\MasterData\Models\Package;
use App\Domains\MasterData\Repositories\PackageRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PackageService
{
  public function __construct(
    protected PackageRepository $packageRepository,
  ) {}

  public function createPackage(PackageData $data): Package
  {
    $package = $this->packageRepository->create([
      'category_id' => $data->category_id,
      'name' => $data->name,
      'is_active' => $data->is_active,
    ]);

    $this->syncFeatures($package, $data->features);

    return $package->load('features', 'category');
  }

  public function updatePackage(string $slug, PackageData $data): Package
  {
    $package = $this->findOrFail($slug);

    $this->packageRepository->update($package, [
      'category_id' => $data->category_id,
      'name' => $data->name,
      'is_active' => $data->is_active,
    ]);

    $this->syncFeatures($package, $data->features);

    return $package->load('features', 'category');
  }

  public function deletePackage(string $slug): bool
  {
    $package = $this->findOrFail($slug);
    return $this->packageRepository->delete($package);
  }

  public function toggleActiveStatus(string $slug): Package
  {
    $package = $this->findOrFail($slug);
    return $this->packageRepository->update($package, [
      'is_active' => ! $package->is_active,
    ]);
  }

  private function findOrFail(string $slug): Package
  {
    $package = $this->packageRepository->findBySlug($slug);

    if (! $package) {
      throw new ModelNotFoundException('Paket tidak ditemukan.');
    }

    return $package;
  }

  /**
   * Sinkronisasi features polimorfik: hapus semua, buat ulang dari array baru.
   *
   * @param string[] $featureDescriptions
   */
  private function syncFeatures(Package $package, array $featureDescriptions): void
  {
    $package->features()->delete();

    $featureData = collect($featureDescriptions)
      ->filter(fn(string $desc) => trim($desc) !== '')
      ->map(fn(string $desc) => ['description' => trim($desc)])
      ->toArray();

    if (!empty($featureData)) {
      $package->features()->createMany($featureData);
    }
  }
}
