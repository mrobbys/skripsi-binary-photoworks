<?php

namespace App\Domains\SystemSettings\Services;

use App\Domains\SystemSettings\DTOs\UserData;
use App\Domains\User\Models\User;
use App\Domains\User\Repositories\UserRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserManagementService
{
  public function __construct(
    private readonly UserRepository $userRepository,
  ) {}

  public function store(UserData $data): User
  {
    return DB::transaction(function () use ($data) {
      $user = $this->userRepository->create([
        'name' => $data->name,
        'email' => $data->email,
        'phone' => $data->phone,
        'password' => Hash::make('Password123'),
      ]);

      $user->syncRoles($data->role);

      return $user;
    });
  }

  public function update(User $user, UserData $data): void
  {
    DB::transaction(function () use ($user, $data) {
      $user->update([
        'name' => $data->name,
        'email' => $data->email,
        'phone' => $data->phone,
      ]);

      $user->syncRoles($data->role);
    });
  }

  public function destroy(User $user): void
  {
    $user->delete();
  }

  public function resetPassword(User $user): void
  {
    $user->update([
      'password' => Hash::make('Password123'),
    ]);
  }
}
