<?php

namespace App\Domains\MasterData\Repositories;

use App\Domains\MasterData\Models\Addon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AddonRepository
{
  /**
   * Query search add-on
   * @param ?string $search
   */
  public function searchQuery(?string $search): Builder
  {
    $query = Addon::query()->latest();

    if ($search) {
      $query->where(function ($q) use ($search) {
        $term = '%' . strtolower($search) . '%';
        $q->whereRaw('LOWER(name) LIKE ?', [$term])
          ->orWhereRaw('LOWER(description) LIKE ?', [$term]);
      });
    }

    return $query;
  }

  /**
   * Blueprint query addon yang aktif.
   */
  public function queryActive(): Builder
  {
    return Addon::where('is_active', true);
  }

  /**
   * Query total addon count()
   */
  public function countAddon(): int
  {
    return Addon::count();
  }

  /**
   * Mengambil semua addon yang aktif.
   */
  public function getActive(): Collection
  {
    return $this->queryActive()->get();
  }

  /**
   * Menghitung total addon yang aktif.
   */
  public function countActive(): int
  {
    return $this->queryActive()->count();
  }

  /**
   * Mencari add-on berdasarkan id
   * @param int $id
   */
  public function findById(int $id): ?Addon
  {
    return Addon::find($id);
  }

  /**
   * Menambahkan data add-on
   * @param array $data
   */
  public function create(array $data): Addon
  {
    return Addon::create($data);
  }

  /**
   * Memperbarui data add-on
   * @param Addon $addon
   * @param array $data
   */
  public function update(Addon $addon, array $data): Addon
  {
    $addon->update($data);

    return $addon;
  }

  /**
   * Menghapus data add-on
   * @param Addon $addon
   */
  public function delete(Addon $addon): ?bool
  {
    return $addon->delete();
  }
}
