<?php

namespace App\Domains\MasterData\Repositories;

use App\Domains\MasterData\Models\Package;

class PackageRepository
{
  public function findBySlug(string $slug): ?Package
  {
    return Package::where('slug', $slug)->first();
  }

  public function create(array $data): Package
  {
    return Package::create($data);
  }

  public function update(Package $package, array $data): Package
  {
    $package->update($data);
    return $package;
  }

  public function delete(Package $package): ?bool
  {
    return $package->delete();
  }
}
