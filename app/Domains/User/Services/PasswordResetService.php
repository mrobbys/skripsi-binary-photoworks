<?php

namespace App\Domains\User\Services;

use App\Domains\User\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use App\Domains\User\DTOs\ForgotPasswordData;
use App\Domains\User\DTOs\ResetPasswordData;
use App\Jobs\SendResetPasswordEmailJob;
use Illuminate\Support\Facades\Password;

class PasswordResetService
{
  public function __construct(
    protected UserRepository $userRepository
  ) {}

  /**
   * Handle pengiriman link reset password
   * 
   * @param ForgotPasswordData $data
   * @return string
   */
  public function sendResetPasswordLink(ForgotPasswordData $data): string
  {
    // cari user berdasarkan email
    $user = $this->userRepository->findByEmail($data->email);

    /**
     * jika tidak ada, kembalikan pesan reset password link terkirim
     * 
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
   * 
   * @param ResetPasswordData $data
   * @return string
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
