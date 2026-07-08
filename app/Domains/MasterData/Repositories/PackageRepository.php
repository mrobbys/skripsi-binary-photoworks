<?php

namespace App\Domains\MasterData\Repositories;

use App\Domains\MasterData\Models\Package;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class PackageRepository
{
  /**
   * Query total paket count()
   */
  public function countPackages(): int
  {
    return Package::count();
  }

  /**
   * Blueprint query paket yang aktif
   */
  public function queryActive(): Builder
  {
    return Package::where('is_active', true);
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
   * Mencari paket berdasarkan slug
   * @param string $slug
   */
  public function findBySlug(string $slug): ?Package
  {
    return Package::where('slug', $slug)->first();
  }

  /**
   * Create paket
   * @param array $data
   */
  public function create(array $data): Package
  {
    return Package::create($data);
  }

  /**
   * Update paket
   * @param Package $package
   * @param array $data
   */
  public function update(Package $package, array $data): Package
  {
    $package->update($data);
    return $package;
  }

  /**
   * Hapus paket
   * @param Package $package
   */
  public function delete(Package $package): ?bool
  {
    return $package->delete();
  }
}
