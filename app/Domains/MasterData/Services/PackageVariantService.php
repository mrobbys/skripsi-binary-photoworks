<?php

namespace App\Domains\MasterData\Services;

use App\Domains\MasterData\DTOs\PackageVariantData;
use App\Domains\MasterData\Models\Package;
use App\Domains\MasterData\Models\PackageVariant;
use App\Domains\MasterData\Repositories\PackageRepository;
use App\Domains\MasterData\Repositories\PackageVariantRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PackageVariantService
{
  public function __construct(
    protected PackageRepository $packageRepository,
    protected PackageVariantRepository $variantRepository,
  ) {}

  /**
   * Tambah data variant baru
   * @param string $packageSlug
   * @param PackageVariantData $data
   */
  public function createVariant(string $packageSlug, PackageVariantData $data): PackageVariant
  {
    $package = $this->findPackageOrFail($packageSlug);
    $variant = $this->variantRepository->create($package, $data->except('features')->toArray());
    $this->syncFeatures($variant, $data->features);

    return $variant->load('features');
  }

  /**
   * Update data variant
   * @param PackageVariant $variant
   * @param PackageVariantData $data
   */
  public function updateVariant(PackageVariant $variant, PackageVariantData $data): PackageVariant
  {
    $this->variantRepository->update($variant, $data->except('features')->toArray());
    $this->syncFeatures($variant, $data->features);

    return $variant->load('features');
  }

  /**
   * Hapus data variant
   * @param PackageVariant $variant
   */
  public function deleteVariant(PackageVariant $variant): bool
  {
    return $this->variantRepository->delete($variant);
  }

  /**
   * Toggle variant active status
   * @param PackageVariant $variant
   */
  public function toggleVariantActiveStatus(PackageVariant $variant): PackageVariant
  {
    return $this->variantRepository->update($variant, [
      'is_active' => ! $variant->is_active,
    ]);
  }

  /**
   * Cari data paket berdasarkan slug
   * @param string $slug
   */
  private function findPackageOrFail(string $slug): Package
  {
    $package = $this->packageRepository->findBySlug($slug);

    if (!$package) {
      throw new ModelNotFoundException('Paket tidak ditemukan.');
    }

    return $package;
  }

  /**
   * @param string[] $featureDescriptions
   */
  private function syncFeatures(PackageVariant $variant, array $featureDescriptions): void
  {
    $variant->features()->get()->each->delete();

    $featureData = collect($featureDescriptions)
      ->filter(fn(string $desc) => trim($desc) !== '')
      ->map(fn(string $desc) => ['description' => trim($desc)])
      ->toArray();

    if (!empty($featureData)) {
      $variant->features()->createMany($featureData);
    }
  }
}
