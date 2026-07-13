<?php

namespace App\Domains\MasterData\Services;

use App\Domains\MasterData\DTOs\BackgroundData;
use App\Domains\MasterData\Models\Background;
use App\Domains\MasterData\Repositories\BackgroundRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;

class BackgroundService
{
  public function __construct(
    protected BackgroundRepository $backgroundRepository,
  ) {}

  /**
   * Tambah background
   * @param BackgroundData $data
   * @param UploadedFile|null $image
   */
  public function createBackground(BackgroundData $data, ?UploadedFile $image = null): Background
  {
    $background = $this->backgroundRepository->create($data->toArray());

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
   * @param UploadedFile|null $image
   */
  public function updateBackground(int $id, BackgroundData $data, ?UploadedFile $image = null): Background
  {
    $background = $this->findOrFail($id);

    $this->backgroundRepository->update($background, $data->toArray());

    if ($image) {
      $background
        ->clearMediaCollection('background-image')
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
    $background = $this->findOrFail($id);
    return $this->backgroundRepository->delete($background);
  }

  /**
   * Toggle active status
   * @param int $id
   */
  public function toggleActiveStatus(int $id): Background
  {
    $background = $this->findOrFail($id);
    return $this->backgroundRepository->update($background, [
      'is_active' => ! $background->is_active,
    ]);
  }

  /**
   * Cari background
   * @param int $id
   */
  private function findOrFail(int $id): Background
  {
    $background = $this->backgroundRepository->findById($id);

    if (! $background) {
      throw new ModelNotFoundException('Background tidak ditemukan.');
    }

    return $background;
  }
}
