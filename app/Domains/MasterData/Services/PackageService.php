<?php

namespace App\Domains\MasterData\Services;

use App\Domains\MasterData\DTOs\PackageData;
use App\Domains\MasterData\Models\Package;
use App\Domains\MasterData\Repositories\PackageRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;

class PackageService
{
  public function __construct(
    protected PackageRepository $packageRepository,
  ) {}

  /**
   * Tambah data paket
   * @param PackageData $data
   */
  public function createPackage(PackageData $data, ?UploadedFile $image = null): Package
  {
    $package = $this->packageRepository->create($data->except('features')->toArray());
    $this->syncFeatures($package, $data->features);

    // jika ada image
    if ($image) {
      $package
        ->addMedia($image)
        ->toMediaCollection('package-image');
    }

    return $package->load('features', 'category');
  }

  /**
   * Update data paket
   * @param string $slug
   * @param PackageData $data
   */
  public function updatePackage(string $slug, PackageData $data, ?UploadedFile $image = null): Package
  {
    $package = $this->findOrFail($slug);
    $this->packageRepository->update($package, $data->except('features')->toArray());
    $this->syncFeatures($package, $data->features);

    // jika ada image
    if ($image) {
      $package
        ->addMedia($image)
        ->toMediaCollection('package-image');
    }

    return $package->load('features', 'category');
  }

  /**
   * Hapus data paket
   * @param string $slug
   */
  public function deletePackage(string $slug): bool
  {
    $package = $this->findOrFail($slug);
    return $this->packageRepository->delete($package);
  }

  /**
   * Toggle status aktif paket
   * @param string $slug
   */
  public function toggleActiveStatus(string $slug): Package
  {
    $package = $this->findOrFail($slug);
    return $this->packageRepository->update($package, [
      'is_active' => ! $package->is_active,
    ]);
  }

  /**
   * Cari paket berdasarkan slug
   * @param string $slug
   */
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
