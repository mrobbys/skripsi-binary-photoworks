<?php

namespace App\Domains\MasterData\Services;

use App\Domains\MasterData\DTOs\AddonData;
use App\Domains\MasterData\Models\Addon;
use App\Domains\MasterData\Repositories\AddonRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AddonService
{
  public function __construct(
    protected AddonRepository $addonRepository,
  ) {}

  public function createAddon(AddonData $data): Addon
  {
    return $this->addonRepository->create($data->toArray());
  }

  public function updateAddon(int $id, AddonData $data): Addon
  {
    $addon = $this->findOrFail($id);

    return $this->addonRepository->update($addon, $data->toArray());
  }

  public function deleteAddon(int $id): bool
  {
    $addon = $this->findOrFail($id);

    return $this->addonRepository->delete($addon);
  }

  public function toggleActiveStatus(int $id): Addon
  {
    $addon = $this->findOrFail($id);

    return $this->addonRepository->update($addon, [
      'is_active' => ! $addon->is_active,
    ]);
  }

  private function findOrFail(int $id): Addon
  {
    $addon = $this->addonRepository->findById($id);

    if (! $addon) {
      throw new ModelNotFoundException('Add-on tidak ditemukan.');
    }

    return $addon;
  }
}
