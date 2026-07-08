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

  /**
   * Tambah data add-on
   * @param AddonData $data
   */
  public function createAddon(AddonData $data): Addon
  {
    return $this->addonRepository->create($data->toArray());
  }

  /**
   * Update data add-on
   * @param int $id
   * @param AddonData $data
   */
  public function updateAddon(int $id, AddonData $data): Addon
  {
    $addon = $this->findOrFail($id);
    return $this->addonRepository->update($addon, $data->toArray());
  }

  /**
   * Hapus data add-on
   * @param int $id
   */
  public function deleteAddon(int $id): bool
  {
    $addon = $this->findOrFail($id);
    return $this->addonRepository->delete($addon);
  }

  /**
   * Ubah status aktif add-on
   * @param int $id
   */
  public function toggleActiveStatus(int $id): Addon
  {
    $addon = $this->findOrFail($id);

    return $this->addonRepository->update($addon, [
      'is_active' => ! $addon->is_active,
    ]);
  }

  /**
   * Mencari add-on berdasarkan id
   * @param int $id
   */
  private function findOrFail(int $id): Addon
  {
    $addon = $this->addonRepository->findById($id);

    if (! $addon) {
      throw new ModelNotFoundException('Add-on tidak ditemukan.');
    }

    return $addon;
  }
}
