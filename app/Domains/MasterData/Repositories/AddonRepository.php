<?php

namespace App\Domains\MasterData\Repositories;

use App\Domains\MasterData\Models\Addon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AddonRepository
{
  public function getPaginated(?string $search = null, int $perPage = 10): LengthAwarePaginator
  {
    $query = Addon::query()->latest();

    if ($search) {
      $query->where(function ($q) use ($search) {
        $term = '%' . strtolower($search) . '%';
        $q->whereRaw('LOWER(name) LIKE ?', [$term])
          ->orWhereRaw('LOWER(description) LIKE ?', [$term]);
      });
    }

    return $query->paginate($perPage);
  }

  public function findById(int $id): ?Addon
  {
    return Addon::find($id);
  }

  public function create(array $data): Addon
  {
    return Addon::create($data);
  }

  public function update(Addon $addon, array $data): Addon
  {
    $addon->update($data);

    return $addon;
  }

  public function delete(Addon $addon): ?bool
  {
    return $addon->delete();
  }
}
