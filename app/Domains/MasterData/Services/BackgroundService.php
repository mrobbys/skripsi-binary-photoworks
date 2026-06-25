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

  public function createBackground(BackgroundData $data, ?UploadedFile $image = null): Background
  {
    $background = $this->backgroundRepository->create([
      'name' => $data->name,
      'description' => $data->description,
      'is_active' => $data->is_active,
    ]);

    if ($image) {
      $background
        ->addMedia($image)
        ->toMediaCollection('background-image');
    }

    return $background->load('media');
  }

  public function updateBackground(int $id, BackgroundData $data, ?UploadedFile $image = null): Background
  {
    $background = $this->findOrFail($id);

    $this->backgroundRepository->update($background, [
      'name' => $data->name,
      'description' => $data->description,
      'is_active' => $data->is_active,
    ]);

    if ($image) {
      $background
        ->addMedia($image)
        ->toMediaCollection('background-image');
    }

    return $background->load('media');
  }

  public function deleteBackground(int $id): bool
  {
    $background = $this->findOrFail($id);

    return $this->backgroundRepository->delete($background);
  }

  public function toggleActiveStatus(int $id): Background
  {
    $background = $this->findOrFail($id);

    return $this->backgroundRepository->update($background, [
      'is_active' => ! $background->is_active,
    ]);
  }

  private function findOrFail(int $id): Background
  {
    $background = $this->backgroundRepository->findById($id);

    if (! $background) {
      throw new ModelNotFoundException('Background tidak ditemukan.');
    }

    return $background;
  }
}
