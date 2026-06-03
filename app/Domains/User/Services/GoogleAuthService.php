<?php

namespace App\Domains\Auth\Services;

use App\Domains\Auth\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Illuminate\Support\Str;
use App\Domains\Auth\Enums\RoleType;

class GoogleAuthService
{

  public function __construct(protected UserRepository $userRepository) {}

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
}
