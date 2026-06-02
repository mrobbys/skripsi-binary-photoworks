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
   * Handle logout
   * 
   * @return void
   */
  public function logout(): void
  {
    Auth::guard('web')->logout();
  }
}
