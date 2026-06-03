<?php

namespace App\Domains\Auth\Services;

use App\Domains\Auth\DTOs\LoginData;
use App\Domains\Auth\DTOs\RegisterData;
use App\Domains\Auth\Models\User;
use App\Domains\Auth\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use App\Domains\Auth\Enums\RoleType;
use App\Domains\Auth\DTOs\ForgotPasswordData;
use App\Domains\Auth\DTOs\ResetPasswordData;
use App\Jobs\SendResetPasswordEmailJob;
use Illuminate\Support\Facades\Password;

class AuthService
{
  public function __construct(
    protected UserRepository $userRepository
  ) {}

  /**
   * Handle autentikasi logic login
   * 
   * @param LoginData $data
   * @return bool
   */
  public function login(LoginData $data): bool
  {
    return Auth::attempt(
      [
        'email' => $data->email,
        'password' => $data->password,
      ],
      $data->remember
    );
  }

  /**
   * Handle login dengan google
   * 
   * @param SocialiteUser $socialiteUser
   * @return array
   */
  public function loginWithGoogle(SocialiteUser $socialiteUser): array
  {
    // cari user berdasarkan google id
    $user = $this->userRepository->findByGoogleId($socialiteUser->getId());

    // jika ada update token google
    if ($user) {
      $user->update([
        'google_token' => $socialiteUser->token,
      ]);
      return [$user, false];
    }

    // jika google id tidak ada, cari berdasarkan email
    $user = $this->userRepository->findByEmail($socialiteUser->getEmail());

    // jika ada update google id dan google token
    if ($user) {
      $user->update([
        'google_id' => $socialiteUser->getId(),
        'google_token' => $socialiteUser->token,
      ]);
      return [$user, false];
    }

    // jika belum terdaftar sama sekali, create user
    $newUser = $this->userRepository->create([
      'name' => $socialiteUser->getName(),
      'email' => $socialiteUser->getEmail(),
      'phone' => null,
      'password' => Hash::make(Str::random(32)),
      'google_id' => $socialiteUser->getId(),
      'google_token' => $socialiteUser->token,
    ]);
    $newUser->assignRole(RoleType::USER->value);

    return [$newUser, true];
  }

  /**
   * Handle register dan menambahkan role user
   * 
   * @param RegisterData $registerData
   * @return User
   */
  public function register(RegisterData $registerData): User
  {
    $user = $this->userRepository->create([
      'name' => $registerData->name,
      'email' => $registerData->email,
      'phone' => $registerData->phone,
      'password' => Hash::make($registerData->password),
    ]);
    $user->assignRole(RoleType::USER->value);

    return $user;
  }

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

  /**
   * Handle logout
   * 
   * @return void
   */
  public function logout(): void
  {
    Auth::guard('web')->logout();
  }
}
