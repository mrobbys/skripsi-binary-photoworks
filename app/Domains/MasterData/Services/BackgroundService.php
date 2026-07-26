<?php

namespace App\Domains\MasterData\Services;

use App\Domains\MasterData\DTOs\BackgroundData;
use App\Domains\MasterData\Models\Background;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;

class BackgroundService
{
	/**
	 * Query pencarian background
	 * @param ?string $search
	 */
	public function searchQuery(?string $search): Builder
	{
		$query = Background::with('media')->latest();

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
	 * Tambah background
	 * @param BackgroundData $data
	 * @param ?UploadedFile $image
	 */
	public function createBackground(BackgroundData $data, ?UploadedFile $image = null): Background
	{
		$background = Background::create($data->toArray());

		if ($image) {
			$background
				->addMedia($image)
				->toMediaCollection('background-image');
		}

		return $background->load('media');
	}

	/**
	 * Update background
	 * @param int $id
	 * @param BackgroundData $data
	 */
	public function updateBackground(int $id, BackgroundData $data, ?UploadedFile $image = null): Background
	{
		$background = Background::findOrFail($id);
		$background->update($data->toArray());

		if ($image) {
			$background
				->addMedia($image)
				->toMediaCollection('background-image');
		}

		return $background->load('media');
	}

	/**
	 * Hapus background
	 * @param int $id
	 */
	public function deleteBackground(int $id): bool
	{
		return Background::findOrFail($id)->delete();
	}

	/**
	 * Ubah status aktif background
	 * @param int $id
	 */
	public function toggleActiveStatus(int $id): Background
	{
		$background = Background::findOrFail($id);
		$background->update(['is_active' => !$background->is_active]);
		return $background;
	}
}
