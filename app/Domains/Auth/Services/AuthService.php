<?php

namespace App\Domains\Auth\Services;

use App\Domains\Auth\DTOs\LoginData;
use App\Domains\Auth\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;

class AuthService
{
  public function __construct(
    protected UserRepository $userRepository
  ) {}

  /**
   * Handle the authentication login logic.
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
}
