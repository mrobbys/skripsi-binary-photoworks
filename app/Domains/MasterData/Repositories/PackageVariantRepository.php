<?php

namespace App\Domains\MasterData\Repositories;

use App\Domains\MasterData\Models\Package;
use App\Domains\MasterData\Models\PackageVariant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class PackageVariantRepository
{
  /**
   * Query total paket count()
   */
  public function countPackages(): int
  {
    return PackageVariant::count();
  }

  /**
   * Blueprint query paket yang aktif
   */
  public function queryActive(): Builder
  {
    return PackageVariant::where('is_active', true);
  }

  /**
   * Ambil semua paket yang aktif
   */
  public function getActive(): Collection
  {
    return $this->queryActive()->get();
  }

  /**
   * Hitung total paket yang aktif
   */
  public function countActive(): int
  {
    return $this->queryActive()->count();
  }
  
  /**
   * Create variant
   * @param array $data
   * @param Package $package
   */
  public function create(Package $package, array $data): PackageVariant
  {
    return $package->variants()->create($data);
  }

  /**
   * Update variant
   * @param PackageVariant $variant
   * @param array $data
   */
  public function update(PackageVariant $variant, array $data): PackageVariant
  {
    $variant->update($data);
    return $variant;
  }

  /**
   * Delete variant
   * @param PackageVariant $variant
   */
  public function delete(PackageVariant $variant): ?bool
  {
    return $variant->delete();
  }
}
