<?php

namespace App\Domains\User\Services;

use Illuminate\Support\Facades\Hash;
use App\Domains\User\DTOs\ResetPasswordData;
use App\Domains\User\Models\User;
use App\Jobs\SendResetPasswordEmailJob;
use Illuminate\Support\Facades\Password;

class PasswordResetService
{

  /**
   * Handle pengiriman link reset password
   * @return string
   */
  public function sendResetPasswordLink(string $email): string
  {
    // cari user berdasarkan email
    $user = User::where('email', $email)->first();

    /**
     * jika tidak ada, kembalikan pesan reset password link terkirim
     * tujuan: user tidak mengetahui email mana yang terdaftar
     */
    if (!$user) {
      return Password::RESET_LINK_SENT;
    }

    // hapus token sebelumnya, lalu buat token yang baru
    Password::deleteToken($user);
    $token = Password::createToken($user);

    // buat url untuk reset password
    $resetUrl = url(route('reset.password.index', [
      'token' => $token,
      'email' => $user->email,
    ], false));

    // dispatch job untuk mengirim email reset password
    SendResetPasswordEmailJob::dispatch(
      email: $user->email,
      userName: $user->name,
      resetUrl: $resetUrl
    );

    // kirim link reset password
    return Password::RESET_LINK_SENT;
  }

  /**
   * Handle reset password
   * @param ResetPasswordData $data
   */
  public function resetPassword(ResetPasswordData $data): string
  {
    return Password::reset(
      [
        'email' => $data->email,
        'password' => $data->password,
        'password_confirmation' => $data->password,
        'token' => $data->token,
      ],
      function ($user, string $password) {
        $user->forceFill([
          'password' => Hash::make($password),
        ])->save();
      }
    );
  }
}
