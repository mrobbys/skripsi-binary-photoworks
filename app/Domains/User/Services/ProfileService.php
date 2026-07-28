<?php

namespace App\Domains\User\Services;

use App\Domains\User\DTOs\ChangePasswordData;
use App\Domains\User\DTOs\ProfileData;
use App\Domains\User\Models\User;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class ProfileService
{

  /**
   * Update data diri user (name, email, phone).
   * @param int $userId
   * @param ProfileData $data
   */
  public function updateProfile(int $userId, ProfileData $data): void
  {
    $user = User::findOrFail($userId);

    // isi data ke dalam model
    $user->fill($data->toArray());

    // cek apakah data yang di inputkan sama dengan data sebelumnya
    if ($user->isClean()) {
      throw new RuntimeException('Data profil tidak ada yang diubah.');
    }

    $user->save();
  }

  /**
   * Ganti password user.
   * Verifikasi password lama sebelum menyimpan yang baru.
   * @param int $userId
   * @param ChangePasswordData $data
   */
  public function changePassword(int $userId, ChangePasswordData $data): void
  {
    $user = User::findOrFail($userId);

    // cek apakah old_password sama dengan password sekarang di database
    if (! Hash::check($data->old_password, $user->password)) {
      throw new RuntimeException('Password lama tidak sesuai.');
    }

    $user->update(['password' => ($data->password)]);
  }
}
