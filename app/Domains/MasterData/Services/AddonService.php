<?php

namespace App\Domains\MasterData\Services;

use App\Domains\MasterData\DTOs\AddonData;
use App\Domains\MasterData\Models\Addon;
use Illuminate\Database\Eloquent\Builder;

class AddonService
{
	/**
	 * Query pencarian add-on
	 * @param ?string $search
	 */
	public function searchQuery(?string $search): Builder
	{
		$query = Addon::query()->latest();

		if ($search) {
			$term = '%' . $search . '%';
			$query->where(function ($q) use ($term) {
				$q->where('name', 'ILIKE', $term)
					->orWhere('description', 'ILIKE', $term);
			});
		}

		return $query;
	}

	/**
	 * Tambah data add-on
	 * @param AddonData $data
	 */
	public function createAddon(AddonData $data): Addon
	{
		return Addon::create($data->toArray());
	}

	/**
	 * Update data add-on
	 * @param int $id
	 * @param AddonData $data
	 */
	public function updateAddon(int $id, AddonData $data): Addon
	{
		$addon = Addon::findOrFail($id);
		$addon->update($data->toArray());
		return $addon;
	}

	/**
	 * Hapus data add-on
	 * @param int $id
	 */
	public function deleteAddon(int $id): bool
	{
		$addon = Addon::findOrFail($id);

		if ($addon->bookings()->exists()) {
			throw new \RuntimeException(
				'Add-on tidak dapat dihapus karena masih terhubung dengan data pemesanan.'
			);
		}

		return $addon->delete();
	}

	/**
	 * Ubah status aktif add-on
	 * @param int $id
	 */
	public function toggleActiveStatus(int $id): Addon
	{
		$addon = Addon::findOrFail($id);
		$addon->update(['is_active' => !$addon->is_active]);
		return $addon;
	}
}
