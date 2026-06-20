<?php

namespace App\Domains\MasterData\Repositories;

use App\Domains\MasterData\Models\Package;
use App\Domains\MasterData\Models\PackageVariant;

class PackageVariantRepository
{
  public function create(Package $package, array $data): PackageVariant
  {
    return $package->variants()->create($data);
  }

  public function update(PackageVariant $variant, array $data): PackageVariant
  {
    $variant->update($data);
    return $variant;
  }

  public function delete(PackageVariant $variant): ?bool
  {
    return $variant->delete();
  }
}
