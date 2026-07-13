<?php

namespace App\Domains\User\Services;

use App\Domains\User\DTOs\ChangePasswordData;
use App\Domains\User\DTOs\ProfileData;
use App\Domains\User\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class ProfileService
{
  public function __construct(
    private readonly UserRepository $repository,
  ) {}

  /**
   * Update data diri user (name, email, phone).
   * @param int $userId
   * @param ProfileData $data
   */
  public function updateProfile(int $userId, ProfileData $data): void
  {
    $user = $this->repository->findById($userId);

    // isi data ke dalam model
    $user->fill($data->toArray());

    // cek apakah data yang di inputkan sama dengan data sebelumnya
    if ($user->isClean()) {
      throw new RuntimeException('Data profil tidak ada yang diubah.');
    }

    $this->repository->updateById($userId, $data->toArray());
  }

  /**
   * Ganti password user.
   * Verifikasi password lama sebelum menyimpan yang baru.
   * @param int $userId
   * @param ChangePasswordData $data
   */
  public function changePassword(int $userId, ChangePasswordData $data): void
  {
    $user = $this->repository->findById($userId);

    // cek apakah old_password sama dengan password sekarang di database
    if (! Hash::check($data->old_password, $user->password)) {
      throw new RuntimeException('Password lama tidak sesuai.');
    }

    $this->repository->updateById($userId, [
      'password' => Hash::make($data->password),
    ]);
  }
}
