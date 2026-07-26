<?php

namespace App\Domains\User\Services;

use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Illuminate\Support\Str;
use App\Domains\User\Enums\RoleType;
use App\Domains\User\Models\User;

class GoogleAuthService
{
  /**
   * Handle login dengan google
   * 
   * @param SocialiteUser $socialiteUser
   * @return array
   */
  public function loginWithGoogle(SocialiteUser $socialiteUser): array
  {
    // cari user berdasarkan google id
    $user = User::where('google_id', $socialiteUser->getId())->first();

    // jika ada update token google
    if ($user) {
      $user->update([
        'google_token' => $socialiteUser->token,
      ]);
      return [$user, false];
    }

    // jika google id tidak ada, cari berdasarkan email
    $user = User::where('email', $socialiteUser->getEmail())->first();

    // jika ada update google id dan google token
    if ($user) {
      $user->update([
        'google_id' => $socialiteUser->getId(),
        'google_token' => $socialiteUser->token,
      ]);
      return [$user, false];
    }

    // jika belum terdaftar sama sekali, create user
    $newUser = User::create([
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
