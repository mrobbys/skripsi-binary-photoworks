<?php

namespace App\Domains\User\Services;

use App\Domains\User\DTOs\RegisterData;
use App\Domains\User\Enums\RoleType;
use App\Domains\User\Models\User;
use App\Domains\User\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;

class RegisterService
{
  public function __construct(
    protected UserRepository $userRepository
  ) {}

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
}
