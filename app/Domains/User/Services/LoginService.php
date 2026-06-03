<?php

namespace App\Domains\Auth\Services;

use App\Domains\Auth\DTOs\LoginData;
use Illuminate\Support\Facades\Auth;

class LoginService
{
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
   * Handle logout
   * 
   * @return void
   */
  public function logout(): void
  {
    Auth::guard('web')->logout();
  }
}
